<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\EstadoSolicitud;
use App\Models\Parentesco;
use App\Models\TipoVario;
use App\Models\Feriado;
use App\Helpers\AuditoriaHelper;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SolicitudController extends Controller
{
    /**
     * Listar solicitudes del usuario autenticado
     */
    public function index()
    {
        $solicitudes = Solicitud::with(['tipo', 'estado'])
            ->where('user_id', Auth::user()->id)
            ->orderByDesc('created_at')
            ->get();

        return view('solicitudes.index', compact('solicitudes'));
    }

    /**
     * Mostrar formulario de creación (usa las vistas Blade existentes)
     */
    public function create($tipo)
    {
        $usuario = Auth::user();
        $tipos = TipoSolicitud::all();
        $parentescos = Parentesco::all();

        // Mapeo de vistas disponibles
        $vistas = [
            'con_goce' => 'solicitudes.con_goce',
            'sin_goce' => 'solicitudes.sin_goce',
            'defuncion' => 'solicitudes.defuncion',
            'varios' => 'solicitudes.permisos_varios',
        ];

        if (!array_key_exists($tipo, $vistas)) {
            abort(404, 'Tipo de solicitud no válido.');
        }

        // OBTENER FERIADOS PARA VALIDAR FECHAS
        $feriados = Feriado::pluck('fecha')->toArray();

        // Si es permiso con goce, calculamos los días
        $totalDias = 6; // cambiar el valor dependiendo de los días que se quieran asignar

        $diasTomados = Solicitud::where('user_id', $usuario->id)
            ->where('tipo_solicitud_id', 1) // tipo con goce
            ->where('estado_solicitud_id', 3) // aprobadas
            ->sum('dias_solicitados');

        $diasDisponibles = max($totalDias - $diasTomados, 0);

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

        // Validación dinámica según tipo de solicitud
        $rules = [
            'tipo_solicitud_id' => 'required|exists:tipos_solicitud,id',
            'fecha_desde'       => 'required|date',
            'fecha_hasta'       => 'required|date|after_or_equal:fecha_desde',
            'hora_desde'        => 'nullable|date_format:H:i',
            'hora_hasta'        => 'nullable|date_format:H:i|after:hora_desde',
        ];

        // Si es tipo Defunción (ej: ID 3)
        if ($request->tipo_solicitud_id == 3) {
            $rules['parentesco_id']    = 'required|exists:parentescos,id';
            $rules['dias_solicitados'] = 'nullable|numeric|min:1|max:7';
            $rules['motivo']           = 'nullable|string|max:1000';
        }
        // Si es tipo Permisos Varios (ej: ID 4)
        elseif ($request->tipo_solicitud_id == 4) {
            $rules['tipo_vario_id'] = 'required|exists:tipos_varios,id';
            $rules['motivo'] = 'required|string|max:1000';
        }

        // Ejecución de validación con las reglas completas
        $request->validate($rules);

        /* VALIDACIÓN DE FECHAS NO PERMITIDAS */

        $desde = Carbon::parse($request->fecha_desde);
        $hasta = Carbon::parse($request->fecha_hasta);

        // No permitir fines de semana
        if ($desde->isWeekend() || $hasta->isWeekend()) {
            return back()->withErrors([
                'fecha_desde' => 'Los permisos no se pueden tomar sábados ni domingos.'
            ])->withInput();
        }

        // No permitir feriados

        if (Feriado::whereIn('fecha', [$desde->toDateString(), $hasta->toDateString()])->exists()) {
            return back()->withErrors([
                'fecha_desde' => 'Los permisos no se pueden tomar en feriados.'
            ])->withInput();
        }

        // Cálculo de días solicitados
        $diasSolicitados = null;

        // Si el usuario escribió manualmente los días (por ejemplo 0.5), se respeta
        if ($request->filled('dias_solicitados')) {
            $diasSolicitados = floatval($request->dias_solicitados);
        } 
        // Si no los ingresó, se calcula automáticamente
        elseif ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $desde = Carbon::parse($request->fecha_desde)->startOfDay();
            $hasta = Carbon::parse($request->fecha_hasta)->endOfDay();

            // Si son el mismo día, cuenta como 1 día exacto (no 1.1)
            if ($desde->isSameDay($hasta)) {
                $diasSolicitados = 1;
            } else {
                $diasSolicitados = $desde->diffInDays($hasta) + 1;
            }
        }

        // Ajuste por jornada media
        $jornada = strtolower(trim($request->jornada));
        if (preg_match('/medio|media|mañana|tarde|mediod/i', $jornada)) {
            $diasSolicitados = 0.5;
        }

        // Buscar jefe directo dinámicamente (rol_id = 3)
        $jefe = \App\Models\User::where('rol_id', 3)->first();

        // Si no existe jefe directo, forzamos el ID 3 (Inspector)
        if (!$jefe) {
            $jefe = \App\Models\User::find(3);
        }

        // Crear la solicitud
        $solicitud = Solicitud::create([
            'user_id' => $usuario->id,
            'validador_id' => $jefe?->id, 
            'tipo_solicitud_id' => $request->tipo_solicitud_id,
            'estado_solicitud_id' => 1, // Pendiente
            'parentesco_id' => $request->parentesco_id,
            'motivo' => $request->motivo,
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
            'hora_desde' => $request->hora_desde,
            'hora_hasta' => $request->hora_hasta,
            'dias_solicitados' => $diasSolicitados, 
            'jornada' => $request->jornada,
            'tipo_vario_id' => $request->tipo_vario_id,
            'fecha_envio' => now(),
            'token_validacion' => Str::uuid(),
        ]);

        /**
         * AUDITORÍA — SOLO AGREGAMOS ESTO
         */
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

        $solicitud = Solicitud::with(['usuario', 'tipo', 'estado', 'resoluciones','ultimaResolucion'])
            ->findOrFail($id);

        // Control de acceso
        if (
            // Puede verla si es su propia solicitud
            $solicitud->user_id === $usuario->id ||

            // O si es jefe directo y el solicitante es su subordinado
            ($usuario->rol?->nombre === 'jefe_directo' &&
            $solicitud->usuario->jefe_directo_id === $usuario->id) ||

            // O si tiene rol secretaria o admin
            in_array($usuario->rol?->nombre, ['secretaria', 'admin'])
        ) {
            return view('solicitudes.show', compact('solicitud'));
        }

        // Si no cumple ninguna condición
        abort(403, 'Acceso no autorizado.');
    }

    public function pdf(Solicitud $solicitud)
    {
        // Regla mínima: admin/secretaria/inspector_general pueden imprimir
        $user = auth()->user();
        $rol  = strtolower($user->rol->nombre ?? '');

        if (!in_array($rol, ['admin','secretaria','inspector_general','jefe_directo'])) {
            abort(403, 'No tienes permiso para imprimir esta ficha.');
        }

        // Cargamos relaciones útiles para la ficha
        $solicitud->load(['usuario', 'validador','tipo','estado','ultimaResolucion',]);

        $pdf = Pdf::loadView('solicitudes.pdf', [
            'solicitud' => $solicitud,
        ])->setPaper('letter'); // A4 o letter según prefieras

        // stream = abre en el navegador; download() si quieres descarga directa
        return $pdf->stream('permiso_'.$solicitud->id.'.pdf');
    }
}
