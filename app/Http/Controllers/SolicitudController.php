<?php

namespace App\Http\Controllers;

use App\Helpers\AuditoriaHelper;
use App\Models\Feriado;
use App\Models\Parentesco;
use App\Models\PeriodoAdministrativo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\TipoVario;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SolicitudController extends Controller
{
    private const TIPO_CON_GOCE = 1;
    private const TIPO_SIN_GOCE = 2;
    private const TIPO_DEFUNCION = 3;
    private const TIPO_VARIOS = 4;
    private const ESTADO_PENDIENTE = 1;
    private const ESTADO_APROBADO = 3;
    private const TOTAL_DIAS_CON_GOCE = 6.0;

    /**
     * Listar solicitudes del usuario autenticado
     */
    public function index()
    {
        $solicitudes = Solicitud::with(['tipo', 'estado', 'restauraciones'])
            ->where('user_id', Auth::user()->id)
            ->orderByDesc('created_at')
            ->get();

        return view('solicitudes.index', compact('solicitudes'));
    }

    /**
     * Mostrar formulario de creacion
     */
    public function create($tipo)
    {
        $usuario = Auth::user();
        $tipos = TipoSolicitud::all();
        $parentescos = Parentesco::all();

        $periodoActivo = PeriodoAdministrativo::activo();
        if (!$periodoActivo) {
            abort(500, 'No existe un periodo administrativo activo.');
        }

        $vistas = [
            'con_goce' => 'solicitudes.con_goce',
            'sin_goce' => 'solicitudes.sin_goce',
            'defuncion' => 'solicitudes.defuncion',
            'varios' => 'solicitudes.permisos_varios',
        ];

        if (!array_key_exists($tipo, $vistas)) {
            abort(404, 'Tipo de solicitud no valido.');
        }

        $feriados = Feriado::pluck('fecha')->toArray();
        $totalDias = self::TOTAL_DIAS_CON_GOCE;
        $diasDisponibles = $this->obtenerDiasDisponiblesConGoce($usuario->id, $periodoActivo->id);
        $diasTomados = $totalDias - $diasDisponibles;
        $tipos_varios = [];

        if ($tipo === 'varios') {
            $tipos_varios = TipoVario::orderBy('nombre')->get();
        }

        return view($vistas[$tipo], compact(
            'tipos',
            'parentescos',
            'tipos_varios',
            'usuario',
            'totalDias',
            'diasTomados',
            'diasDisponibles',
            'feriados'
        ));
    }

    /**
     * Guardar solicitud
     */
    public function store(Request $request)
    {
        $usuario = Auth::user();
        $periodoActivo = PeriodoAdministrativo::activo();

        if (!$periodoActivo) {
            return back()->withErrors([
                'periodo' => 'No existe un periodo administrativo activo.'
            ]);
        }

        $rules = [
            'tipo_solicitud_id' => 'required|exists:tipos_solicitud,id',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
            'hora_desde' => 'nullable|date_format:H:i',
            'hora_hasta' => 'nullable|date_format:H:i|after:hora_desde',
            'password' => ['required', 'current_password'],
        ];

        $tipoSolicitudId = (int) $request->tipo_solicitud_id;

        if ($tipoSolicitudId === self::TIPO_CON_GOCE) {
            $rules['motivo'] = 'required|string|max:1000';
            $rules['dias_solicitados'] = 'required|numeric|min:0.5|max:6';
            $rules['jornada'] = 'nullable|in:manana,tarde';
        } elseif ($tipoSolicitudId === self::TIPO_SIN_GOCE) {
            $rules['motivo'] = 'required|string|max:1000';
            $rules['dias_solicitados'] = 'required|integer|min:1';
        } elseif ($tipoSolicitudId === self::TIPO_DEFUNCION) {
            $rules['parentesco_id'] = 'required|exists:parentescos,id';
            $rules['dias_solicitados'] = 'required|integer|min:1|max:7';
            $rules['motivo'] = 'nullable|string|max:1000';
        } elseif ($tipoSolicitudId === self::TIPO_VARIOS) {
            $rules['tipo_vario_id'] = 'required|exists:tipos_varios,id';
            $rules['motivo'] = 'required|string|max:1000';
        }

        $request->validate($rules);

        if (!Hash::check($request->password, $usuario->password)) {
            return back()->withErrors([
                'password' => 'La contrasena ingresada no coincide con su cuenta.'
            ])->withInput();
        }

        $desde = Carbon::parse($request->fecha_desde)->startOfDay();
        $hasta = Carbon::parse($request->fecha_hasta)->startOfDay();

        if ($this->rangoTieneDiasNoHabiles($desde, $hasta)) {
            return back()->withErrors([
                'fecha_desde' => 'El rango seleccionado incluye fines de semana o feriados no permitidos.'
            ])->withInput();
        }

        $diasSolicitados = $request->filled('dias_solicitados')
            ? (float) $request->dias_solicitados
            : null;

        $jornada = strtolower(trim((string) $request->jornada));
        $esMedioDia = in_array($jornada, ['manana', 'tarde'], true) || $diasSolicitados === 0.5;
        $diasHabilesRango = $this->contarDiasHabiles($desde, $hasta);

        if ($esMedioDia) {
            if ($tipoSolicitudId !== self::TIPO_CON_GOCE) {
                return back()->withErrors([
                    'dias_solicitados' => 'Solo los permisos con goce pueden solicitarse por medio dia.'
                ])->withInput();
            }

            if (!$desde->isSameDay($hasta)) {
                return back()->withErrors([
                    'fecha_hasta' => 'Un permiso de medio dia debe corresponder a una sola fecha.'
                ])->withInput();
            }

            if (!in_array($jornada, ['manana', 'tarde'], true)) {
                return back()->withErrors([
                    'jornada' => 'Debe indicar si el medio dia corresponde a manana o tarde.'
                ])->withInput();
            }

            $diasSolicitados = 0.5;
        } elseif (in_array($tipoSolicitudId, [
            self::TIPO_CON_GOCE,
            self::TIPO_SIN_GOCE,
            self::TIPO_DEFUNCION,
        ], true) && $diasSolicitados !== (float) $diasHabilesRango) {
            return back()->withErrors([
                'dias_solicitados' => 'La cantidad de dias debe coincidir con los dias habiles del rango seleccionado.'
            ])->withInput();
        }

        if ($tipoSolicitudId === self::TIPO_CON_GOCE && !$esMedioDia && floor($diasSolicitados) !== $diasSolicitados) {
            return back()->withErrors([
                'dias_solicitados' => 'Los permisos con goce solo permiten dias completos o medio dia.'
            ])->withInput();
        }

        if ($tipoSolicitudId === self::TIPO_CON_GOCE) {
            $diasDisponibles = $this->obtenerDiasDisponiblesConGoce($usuario->id, $periodoActivo->id);

            if ($diasSolicitados > $diasDisponibles) {
                return back()->withErrors([
                    'dias_solicitados' => 'La solicitud excede los dias administrativos disponibles para este ano.'
                ])->withInput();
            }
        }

        $jefe = $usuario->jefeDirecto;

        if (!$jefe || strtolower($jefe->rol?->nombre ?? '') !== 'jefe_directo') {
            $jefe = \App\Models\User::whereHas('rol', function ($query) {
                $query->where('nombre', 'jefe_directo');
            })->first();
        }

        if (!$jefe) {
            return back()->withErrors([
                'jefe_directo' => 'No hay un director configurado para autorizar esta solicitud.'
            ])->withInput();
        }

        $solicitud = Solicitud::create([
            'user_id' => $usuario->id,
            'periodo_id' => $periodoActivo->id,
            'validador_id' => $jefe->id,
            'tipo_solicitud_id' => $tipoSolicitudId,
            'estado_solicitud_id' => self::ESTADO_PENDIENTE,
            'parentesco_id' => $request->parentesco_id,
            'motivo' => $request->motivo,
            'fecha_desde' => $desde->toDateString(),
            'fecha_hasta' => $hasta->toDateString(),
            'hora_desde' => $request->hora_desde,
            'hora_hasta' => $request->hora_hasta,
            'dias_solicitados' => $diasSolicitados,
            'jornada' => $request->jornada,
            'tipo_vario_id' => $request->tipo_vario_id,
            'fecha_envio' => now(),
            'token_validacion' => Str::uuid(),
        ]);

        AuditoriaHelper::registrar(
            'solicitudes',
            $solicitud->id,
            'solicitud_creada',
            Auth::user()->id,
            null,
            $solicitud->toArray()
        );

        return redirect()->route('solicitudes.index')
            ->with('success', 'Solicitud enviada correctamente.');
    }

    /**
     * Mostrar detalle de una solicitud
     */
    public function show($id)
    {
        $usuario = auth()->user();

        $solicitud = Solicitud::with(['usuario', 'tipo', 'estado', 'resoluciones', 'ultimaResolucion', 'restauraciones.usuario'])
            ->findOrFail($id);

        if (
            $solicitud->user_id === $usuario->id ||
            ($usuario->rol?->nombre === 'jefe_directo' && $solicitud->usuario->jefe_directo_id === $usuario->id) ||
            in_array($usuario->rol?->nombre, ['secretaria', 'encargado_sistema', 'admin'])
        ) {
            return view('solicitudes.show', compact('solicitud'));
        }

        abort(403, 'Acceso no autorizado.');
    }

    public function pdf(Solicitud $solicitud)
    {
        $user = auth()->user();
        $rol = strtolower($user->rol->nombre ?? '');

        if (!in_array($rol, ['admin', 'encargado_sistema', 'secretaria', 'jefe_directo'])) {
            abort(403, 'No tienes permiso para imprimir esta ficha.');
        }

        $solicitud->load(['usuario', 'validador', 'tipo', 'estado', 'ultimaResolucion', 'restauraciones.usuario']);

        $pdf = Pdf::loadView('solicitudes.pdf', [
            'solicitud' => $solicitud,
        ])->setPaper('letter');

        return $pdf->stream('permiso_' . $solicitud->id . '.pdf');
    }

    private function obtenerDiasDisponiblesConGoce(int $userId, int $periodoId): float
    {
        $diasTomados = Solicitud::withSum('restauraciones', 'dias_restaurados')
            ->where('user_id', $userId)
            ->where('tipo_solicitud_id', self::TIPO_CON_GOCE)
            ->where('estado_solicitud_id', self::ESTADO_APROBADO)
            ->where('periodo_id', $periodoId)
            ->get()
            ->sum(function ($solicitud) {
                $diasAprobados = (float) $solicitud->dias_solicitados;
                $diasRestaurados = (float) ($solicitud->restauraciones_sum_dias_restaurados ?? 0);

                return max($diasAprobados - $diasRestaurados, 0);
            });

        return max(self::TOTAL_DIAS_CON_GOCE - $diasTomados, 0);
    }

    private function contarDiasHabiles(Carbon $desde, Carbon $hasta): int
    {
        $feriados = Feriado::pluck('fecha')
            ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString())
            ->all();

        $contador = 0;
        $fecha = $desde->copy();

        while ($fecha->lte($hasta)) {
            if (!$fecha->isWeekend() && !in_array($fecha->toDateString(), $feriados, true)) {
                $contador++;
            }

            $fecha->addDay();
        }

        return $contador;
    }

    private function rangoTieneDiasNoHabiles(Carbon $desde, Carbon $hasta): bool
    {
        $diasHabiles = $this->contarDiasHabiles($desde, $hasta);
        $diasTotalesRango = (int) $desde->copy()->diffInDays($hasta->copy()) + 1;

        return $diasHabiles !== $diasTotalesRango;
    }
}
