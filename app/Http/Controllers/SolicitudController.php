<?php

namespace App\Http\Controllers;

use App\Helpers\AuditoriaHelper;
use App\Models\EstadoSolicitud;
use App\Models\Feriado;
use App\Models\Parentesco;
use App\Models\PeriodoAdministrativo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\TipoVario;
use App\Models\User;
use App\Notifications\SolicitudCreadaJefeDirecto;
use App\Notifications\SolicitudCreadaSolicitante;
use App\Support\NotificacionSegura;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SolicitudController extends Controller
{
    private const TIPO_CON_GOCE = TipoSolicitud::CODIGO_CON_GOCE;

    private const TIPO_SIN_GOCE = TipoSolicitud::CODIGO_SIN_GOCE;

    private const TIPO_DEFUNCION = TipoSolicitud::CODIGO_DEFUNCION;

    private const TIPO_VARIOS = TipoSolicitud::CODIGO_VARIOS;

    private const ESTADO_PENDIENTE = EstadoSolicitud::CODIGO_PENDIENTE;

    private const ESTADO_APROBADO = EstadoSolicitud::CODIGO_APROBADO;

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
        if (! $periodoActivo) {
            abort(500, 'No existe un periodo administrativo activo.');
        }

        $vistas = [
            'con_goce' => 'solicitudes.con_goce',
            'sin_goce' => 'solicitudes.sin_goce',
            'defuncion' => 'solicitudes.defuncion',
            'varios' => 'solicitudes.permisos_varios',
        ];

        if (! array_key_exists($tipo, $vistas)) {
            abort(404, 'Tipo de solicitud no valido.');
        }

        $tipoSolicitud = TipoSolicitud::where('codigo', $tipo)->first();
        if (! $tipoSolicitud) {
            abort(500, 'No existe el tipo base de solicitud configurado.');
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
        ) + ['tipoSolicitud' => $tipoSolicitud]);
    }

    /**
     * Guardar solicitud
     */
    public function store(Request $request)
    {
        $usuario = Auth::user();
        $periodoActivo = PeriodoAdministrativo::activo();

        if (! $periodoActivo) {
            return back()->withErrors([
                'periodo' => 'No existe un periodo administrativo activo.',
            ]);
        }

        $rules = [
            'tipo_codigo' => 'required|in:con_goce,sin_goce,defuncion,varios',
            'fecha_desde' => 'required|date|after_or_equal:today',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
            'hora_desde' => 'nullable|date_format:H:i',
            'hora_hasta' => 'nullable|date_format:H:i|after:hora_desde',
            'password' => ['required', 'current_password'],
        ];

        $tipoCodigo = $request->tipo_codigo;
        $tipoSolicitud = TipoSolicitud::where('codigo', $tipoCodigo)->first();

        if (! $tipoSolicitud) {
            return back()->withErrors([
                'tipo_codigo' => 'No existe el tipo base de solicitud configurado.',
            ])->withInput();
        }

        $tipoSolicitudId = (int) $tipoSolicitud->id;

        if ($tipoCodigo === self::TIPO_CON_GOCE) {
            $rules['motivo'] = 'required|string|max:1000';
            $rules['dias_solicitados'] = 'required|numeric|min:0.5|max:6';
            $rules['jornada'] = 'nullable|in:manana,tarde';
        } elseif ($tipoCodigo === self::TIPO_SIN_GOCE) {
            $rules['motivo'] = 'required|string|max:1000';
            $rules['dias_solicitados'] = 'required|integer|min:1';
        } elseif ($tipoCodigo === self::TIPO_DEFUNCION) {
            $rules['parentesco_id'] = 'required|exists:parentescos,id';
            $rules['dias_solicitados'] = 'required|integer|min:1|max:7';
            $rules['motivo'] = 'nullable|string|max:1000';
        } elseif ($tipoCodigo === self::TIPO_VARIOS) {
            $rules['tipo_vario_id'] = 'required|exists:tipos_varios,id';
            $rules['motivo'] = 'required|string|max:1000';
        }

        $request->validate($rules, [
            'fecha_desde.after_or_equal' => 'No se pueden solicitar permisos para fechas pasadas.',
            'fecha_hasta.after_or_equal' => 'La fecha de termino debe ser igual o posterior a la fecha de inicio.',
        ]);

        if (! Hash::check($request->password, $usuario->password)) {
            return back()->withErrors([
                'password' => 'La contrasena ingresada no coincide con su cuenta.',
            ])->withInput();
        }

        $desde = Carbon::parse($request->fecha_desde)->startOfDay();
        $hasta = Carbon::parse($request->fecha_hasta)->startOfDay();

        if ($this->rangoTieneDiasNoHabiles($desde, $hasta)) {
            return back()->withErrors([
                'fecha_desde' => 'El rango seleccionado incluye fines de semana o feriados no permitidos.',
            ])->withInput();
        }

        $diasSolicitados = $request->filled('dias_solicitados')
            ? (float) $request->dias_solicitados
            : null;

        $jornada = strtolower(trim((string) $request->jornada));
        $esMedioDia = in_array($jornada, ['manana', 'tarde'], true) || $diasSolicitados === 0.5;
        $diasHabilesRango = $this->contarDiasHabiles($desde, $hasta);

        if ($tipoCodigo === self::TIPO_VARIOS) {
            $diasSolicitados = (float) $diasHabilesRango;
        }

        if ($esMedioDia) {
            if ($tipoCodigo !== self::TIPO_CON_GOCE) {
                return back()->withErrors([
                    'dias_solicitados' => 'Solo los permisos con goce pueden solicitarse por medio dia.',
                ])->withInput();
            }

            if (! $desde->isSameDay($hasta)) {
                return back()->withErrors([
                    'fecha_hasta' => 'Un permiso de medio dia debe corresponder a una sola fecha.',
                ])->withInput();
            }

            if (! in_array($jornada, ['manana', 'tarde'], true)) {
                return back()->withErrors([
                    'jornada' => 'Debe indicar si el medio dia corresponde a manana o tarde.',
                ])->withInput();
            }

            $diasSolicitados = 0.5;
        } elseif (in_array($tipoCodigo, [
            self::TIPO_CON_GOCE,
            self::TIPO_SIN_GOCE,
            self::TIPO_DEFUNCION,
        ], true) && $diasSolicitados !== (float) $diasHabilesRango) {
            return back()->withErrors([
                'dias_solicitados' => 'La cantidad de dias debe coincidir con los dias habiles del rango seleccionado.',
            ])->withInput();
        }

        if ($tipoCodigo === self::TIPO_CON_GOCE && ! $esMedioDia && floor($diasSolicitados) !== $diasSolicitados) {
            return back()->withErrors([
                'dias_solicitados' => 'Los permisos con goce solo permiten dias completos o medio dia.',
            ])->withInput();
        }

        if ($tipoCodigo === self::TIPO_CON_GOCE) {
            $diasDisponibles = $this->obtenerDiasDisponiblesConGoce($usuario->id, $periodoActivo->id);

            if ($diasSolicitados > $diasDisponibles) {
                return back()->withErrors([
                    'dias_solicitados' => 'La solicitud excede los dias administrativos disponibles para este ano.',
                ])->withInput();
            }
        }

        $jefe = $usuario->jefeDirecto;

        if (! $this->esJefaturaActiva($jefe)) {
            $jefe = User::where('activo', true)->whereHas('rol', function ($query) {
                $query->where('nombre', 'jefe_directo');
            })->first();
        }

        if (! $jefe) {
            return back()->withErrors([
                'jefe_directo' => 'No hay un director configurado para autorizar esta solicitud.',
            ])->withInput();
        }

        $estadoPendienteId = EstadoSolicitud::where('codigo', self::ESTADO_PENDIENTE)->value('id');

        if (! $estadoPendienteId) {
            return back()->withErrors([
                'estado_solicitud_id' => 'No existe el estado base pendiente configurado.',
            ])->withInput();
        }

        $datosSolicitud = [
            'user_id' => $usuario->id,
            'periodo_id' => $periodoActivo->id,
            'validador_id' => $jefe->id,
            'tipo_solicitud_id' => $tipoSolicitudId,
            'estado_solicitud_id' => $estadoPendienteId,
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
        ];

        $solicitud = DB::transaction(function () use ($datosSolicitud, $usuario): Solicitud {
            $solicitud = Solicitud::create($datosSolicitud);

            AuditoriaHelper::registrar(
                'solicitudes',
                $solicitud->id,
                'solicitud_creada',
                $usuario->id,
                null,
                $solicitud->toArray()
            );

            return $solicitud;
        }, 3);

        NotificacionSegura::enviar($usuario, new SolicitudCreadaSolicitante($solicitud));
        NotificacionSegura::enviar($jefe, new SolicitudCreadaJefeDirecto($solicitud));

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

        if ($this->puedeVerSolicitud($usuario, $solicitud)) {
            return view('solicitudes.show', compact('solicitud'));
        }

        abort(403, 'Acceso no autorizado.');
    }

    public function pdf(Solicitud $solicitud)
    {
        $user = auth()->user();

        $solicitud->load(['usuario', 'validador', 'tipo', 'estado', 'ultimaResolucion', 'restauraciones.usuario']);

        if (! $this->puedeVerSolicitud($user, $solicitud)) {
            abort(403, 'No tienes permiso para imprimir esta ficha.');
        }

        $pdf = Pdf::loadView('solicitudes.pdf', [
            'solicitud' => $solicitud,
        ])->setPaper('letter');

        return $pdf->stream('permiso_'.$solicitud->id.'.pdf');
    }

    private function puedeVerSolicitud($usuario, Solicitud $solicitud): bool
    {
        $rol = strtolower($usuario->rol?->nombre ?? '');

        if ($solicitud->user_id === $usuario->id) {
            return true;
        }

        if (in_array($rol, ['secretaria', 'encargado_sistema', 'admin'], true)) {
            return true;
        }

        if ($rol !== 'jefe_directo') {
            return false;
        }

        return (int) ($solicitud->usuario?->jefe_directo_id ?? 0) === (int) $usuario->id
            || (int) ($solicitud->validador_id ?? 0) === (int) $usuario->id;
    }

    private function esJefaturaActiva(?User $usuario): bool
    {
        return $usuario !== null
            && $usuario->activo
            && strtolower($usuario->rol?->nombre ?? '') === 'jefe_directo';
    }

    private function obtenerDiasDisponiblesConGoce(int $userId, int $periodoId): float
    {
        $tipoConGoceId = TipoSolicitud::where('codigo', self::TIPO_CON_GOCE)->value('id');

        if (! $tipoConGoceId) {
            return self::TOTAL_DIAS_CON_GOCE;
        }

        $diasTomados = Solicitud::withSum('restauraciones', 'dias_restaurados')
            ->where('user_id', $userId)
            ->where('tipo_solicitud_id', $tipoConGoceId)
            ->whereHas('estado', fn ($q) => $q->where('codigo', self::ESTADO_APROBADO))
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
            if (! $fecha->isWeekend() && ! in_array($fecha->toDateString(), $feriados, true)) {
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
