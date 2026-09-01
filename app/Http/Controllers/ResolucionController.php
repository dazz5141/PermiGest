<?php

namespace App\Http\Controllers;

use App\Helpers\AuditoriaHelper;
use App\Models\EstadoSolicitud;
use App\Models\Resolucion;
use App\Models\Solicitud;
use App\Notifications\SolicitudResueltaSolicitante;
use App\Support\NotificacionSegura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        $validated = $request->validate([
            'accion' => 'required|in:aprobado,rechazado',
            'comentario' => [
                Rule::requiredIf($request->input('accion') === 'rechazado'),
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'comentario.required' => 'Debe indicar el motivo del rechazo.',
        ]);

        $estadoNombre = $validated['accion'] === 'aprobado'
            ? self::ESTADO_APROBADO
            : self::ESTADO_RECHAZADO;

        $estadoId = EstadoSolicitud::where('codigo', $estadoNombre)->value('id');

        if (! $estadoId) {
            abort(500, 'No se encontro el estado de solicitud requerido.');
        }

        $solicitud = DB::transaction(function () use ($id, $estadoId, $usuarioActualId, $validated): Solicitud {
            $solicitud = Solicitud::with(['estado', 'usuario'])
                ->lockForUpdate()
                ->findOrFail($id);

            if (! $this->puedeResolver(Auth::user(), $solicitud)) {
                abort(403, 'No tienes permiso para resolver esta solicitud.');
            }

            if ($solicitud->estado?->codigo !== self::ESTADO_PENDIENTE) {
                throw ValidationException::withMessages([
                    'solicitud' => 'Esta solicitud ya fue resuelta y no puede modificarse nuevamente.',
                ]);
            }

            $oldData = $solicitud->toArray();

            $solicitud->update([
                'estado_solicitud_id' => $estadoId,
                'validador_id' => $usuarioActualId,
                'fecha_revision' => now(),
                'observaciones_validador' => $validated['comentario'] ?? null,
                'firma_validador' => true,
            ]);

            Resolucion::create([
                'solicitud_id' => $solicitud->id,
                'user_id' => $usuarioActualId,
                'accion' => $validated['accion'],
                'comentario' => $validated['comentario'] ?? null,
            ]);

            AuditoriaHelper::registrar(
                'solicitudes',
                $solicitud->id,
                $validated['accion'] === 'aprobado' ? 'solicitud_aprobada' : 'solicitud_rechazada',
                $usuarioActualId,
                $oldData,
                $solicitud->fresh()->toArray()
            );

            return $solicitud;
        }, 3);

        NotificacionSegura::enviar($solicitud->usuario, new SolicitudResueltaSolicitante(
            $solicitud,
            $validated['accion'],
            $validated['comentario'] ?? null
        ));

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
