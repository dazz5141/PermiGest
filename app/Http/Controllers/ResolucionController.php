<?php

namespace App\Http\Controllers;

use App\Helpers\AuditoriaHelper;
use App\Models\EstadoSolicitud;
use App\Models\Resolucion;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResolucionController extends Controller
{
    private const ESTADO_PENDIENTE = EstadoSolicitud::CODIGO_PENDIENTE;
    private const ESTADO_APROBADO = EstadoSolicitud::CODIGO_APROBADO;
    private const ESTADO_RECHAZADO = EstadoSolicitud::CODIGO_RECHAZADO;

    /**
     * Listar solicitudes pendientes para resolver.
     */
    public function index()
    {
        $usuario = Auth::user();

        $subordinadosIds = $usuario->subordinados()
            ->pluck('id')
            ->filter()
            ->toArray();

        if (empty($subordinadosIds)) {
            return view('resoluciones.index', [
                'pendientes' => collect(),
                'usuario' => $usuario,
            ]);
        }

        $pendientes = Solicitud::whereIn('user_id', $subordinadosIds)
            ->whereHas('estado', fn ($q) => $q->where('codigo', self::ESTADO_PENDIENTE))
            ->with(['usuario', 'tipo', 'estado'])
            ->orderByDesc('created_at')
            ->get();

        return view('resoluciones.index', compact('pendientes', 'usuario'));
    }

    /**
     * Actualizar estado de la solicitud.
     */
    public function update(Request $request, $id)
    {
        $usuarioActualId = (int) Auth::user()->id;

        $request->validate([
            'accion' => 'required|in:aprobado,rechazado',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $solicitud = Solicitud::findOrFail($id);

        if (!$this->puedeResolver(Auth::user(), $solicitud)) {
            abort(403, 'No tienes permiso para resolver esta solicitud.');
        }

        $oldData = $solicitud->toArray();
        $estadoNombre = $request->accion === 'aprobado'
            ? self::ESTADO_APROBADO
            : self::ESTADO_RECHAZADO;

        $estadoId = EstadoSolicitud::where('codigo', $estadoNombre)->value('id');

        if (!$estadoId) {
            abort(500, 'No se encontro el estado de solicitud requerido.');
        }

        $solicitud->update([
            'estado_solicitud_id' => $estadoId,
            'validador_id' => $usuarioActualId,
            'fecha_revision' => now(),
            'observaciones_validador' => $request->comentario,
            'firma_validador' => true,
        ]);

        Resolucion::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => $usuarioActualId,
            'accion' => $request->accion,
            'comentario' => $request->comentario,
        ]);

        AuditoriaHelper::registrar(
            'solicitudes',
            $solicitud->id,
            $request->accion === 'aprobado' ? 'solicitud_aprobada' : 'solicitud_rechazada',
            $usuarioActualId,
            $oldData,
            $solicitud->fresh()->toArray()
        );

        return back()->with('success', 'Resolucion registrada correctamente.');
    }

    private function puedeResolver($usuario, Solicitud $solicitud): bool
    {
        if (($solicitud->validador_id ?? null) === $usuario->id) {
            return true;
        }

        return (int) ($solicitud->usuario?->jefe_directo_id ?? 0) === (int) $usuario->id;
    }
}
