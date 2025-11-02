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
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('solicitudes.index', compact('solicitudes'));
    }

    /**
     * Mostrar formulario de creación (usa las vistas Blade existentes)
     */
    public function create($tipo)
    {
        $tipos = TipoSolicitud::all();
        $parentescos = Parentesco::all();

        // Mapeo de tipos válidos a sus vistas
        $vistas = [
            'con_goce' => 'solicitudes.con_goce',
            'sin_goce' => 'solicitudes.sin_goce',
            'defuncion' => 'solicitudes.defuncion',
            'varios' => 'solicitudes.permisos_varios',
        ];

        // Si el tipo no existe, mostramos error 404
        if (!array_key_exists($tipo, $vistas)) {
            abort(404, 'Tipo de solicitud no válido.');
        }

        return view($vistas[$tipo], compact('tipos', 'parentescos'));
    }

    /**
     * Guardar solicitud
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_solicitud_id' => 'required|exists:tipos_solicitud,id',
            'motivo' => 'required|string|max:1000',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
        ]);

        Solicitud::create([
            'user_id' => Auth::id(),
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
            'token_validacion' => Str::uuid(),
        ]);

        return redirect()->route('solicitudes.index')
            ->with('success', 'Solicitud enviada correctamente.');
    }

    /**
     * Mostrar detalle de una solicitud
     */
    public function show($id)
    {
        $solicitud = Solicitud::with(['usuario', 'tipo', 'estado', 'resoluciones'])
            ->findOrFail($id);

        return view('solicitudes.show', compact('solicitud'));
    }
}
