<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Solicitud;
use App\Models\Resolucion;

class ResolucionController extends Controller
{
    /**
     * Listar solicitudes pendientes para revisar
     */
    public function index()
    {
        $solicitudes = Solicitud::where('estado_solicitud_id', 1) // Pendiente
            ->with(['usuario', 'tipo'])
            ->orderBy('fecha_envio', 'desc')
            ->get();

        return view('resoluciones.index', compact('solicitudes'));
    }

    /**
     * Actualizar estado (aprobar/rechazar)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'accion' => 'required|in:aprobado,rechazado',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $solicitud = Solicitud::findOrFail($id);

        $estado = $request->accion === 'aprobado' ? 3 : 4; // 3 = Aprobado, 4 = Rechazado
        $solicitud->update([
            'estado_solicitud_id' => $estado,
            'validador_id' => Auth::id(),
            'fecha_revision' => now(),
            'observaciones_validador' => $request->comentario,
            'firma_validador' => true,
        ]);

        Resolucion::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => Auth::id(),
            'accion' => $request->accion,
            'comentario' => $request->comentario,
        ]);

        return back()->with('success', 'Resolución registrada correctamente.');
    }
}
