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
        $usuario = Auth::user();

        // 🧩 Obtener los IDs de los subordinados directos del jefe actual
        $subordinadosIds = $usuario->subordinados()
            ->pluck('id')
            ->filter()
            ->toArray();

        // Si no tiene subordinados, devolver vista vacía
        if (empty($subordinadosIds)) {
            return view('resoluciones.index', [
                'pendientes' => collect(),
                'usuario' => $usuario
            ]);
        }

        // 📨 Obtener solicitudes pendientes de revisión de esos subordinados
        $pendientes = Solicitud::whereIn('user_id', $subordinadosIds)
            ->where('estado_solicitud_id', 1) // 1 = Pendiente
            ->with(['usuario', 'tipo', 'estado'])
            ->orderByDesc('created_at')
            ->get();

        return view('resoluciones.index', compact('pendientes', 'usuario'));
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

        // ⚠️ Seguridad: solo el validador asignado o el admin pueden resolver
        if ($solicitud->validador_id !== Auth::user()->id && Auth::user()->rol_id !== 1) {
            abort(403, 'No tienes permiso para resolver esta solicitud.');
        }

        $estado = $request->accion === 'aprobado' ? 3 : 4; // 3 = Aprobado, 4 = Rechazado
        $solicitud->update([
            'estado_solicitud_id' => $estado,
            'validador_id' => Auth::user()->id,
            'fecha_revision' => now(),
            'observaciones_validador' => $request->comentario,
            'firma_validador' => true,
        ]);

        Resolucion::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => Auth::user()->id,
            'accion' => $request->accion,
            'comentario' => $request->comentario,
        ]);

        return back()->with('success', 'Resolución registrada correctamente.');
    }
}
