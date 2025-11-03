<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\EstadoSolicitud;
use App\Models\Parentesco;

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

        // Si es permiso con goce, calculamos los días
        $totalDias = 6; // cambiar el valor dependiendo de los días que se quieran asignar

        $diasTomados = Solicitud::where('user_id', $usuario->id)
            ->where('tipo_solicitud_id', 1) // tipo con goce
            ->where('estado_solicitud_id', 3) // aprobadas
            ->sum('dias_solicitados');

        $diasDisponibles = max($totalDias - $diasTomados, 0);

        return view($vistas[$tipo], compact(
            'tipos',
            'parentescos',
            'usuario',
            'totalDias',
            'diasTomados',
            'diasDisponibles'
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
    ];

    // Si es tipo Defunción (ej: ID 3)
    if ($request->tipo_solicitud_id == 3) {
        $rules['parentesco_id']    = 'required|exists:parentescos,id';
        $rules['dias_solicitados'] = 'required|numeric|min:1|max:7';
        // motivo es opcional
    } else {
        $rules['motivo'] = 'required|string|max:1000';
    }

    // Ejecución de validación con las reglas completas
    $request->validate($rules);

        // Buscar jefe directo dinámicamente (rol_id = 3)
        $jefe = \App\Models\User::where('rol_id', 3)->first();

        // Si no existe jefe directo, forzamos el ID 3 (Inspector)
        if (!$jefe) {
            $jefe = \App\Models\User::find(3);
        }

        // Creamos la solicitud con validador_id asignado
        Solicitud::create([
            'user_id' => $usuario->id,
            'validador_id' => $jefe?->id, // 👈 aseguramos que se envíe
            'tipo_solicitud_id' => $request->tipo_solicitud_id,
            'estado_solicitud_id' => 1, // Pendiente
            'parentesco_id' => $request->parentesco_id,
            'motivo' => $request->motivo,
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
            'hora_desde' => $request->hora_desde,
            'hora_hasta' => $request->hora_hasta,
            'dias_solicitados' => $request->dias_solicitados,
            'jornada' => $request->jornada,
            'tipo_varios' => $request->tipo_varios,
            'fecha_envio' => now(),
            'token_validacion' => \Illuminate\Support\Str::uuid(),
        ]);

        return redirect()->route('solicitudes.index')
            ->with('success', 'Solicitud enviada correctamente.');
    }

    /**
     * Mostrar detalle de una solicitud
     */
    public function show($id)
    {
        $usuario = auth()->user();

        $solicitud = Solicitud::with(['usuario', 'tipo', 'estado', 'resoluciones'])
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
}
