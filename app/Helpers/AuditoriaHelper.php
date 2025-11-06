<?php

namespace App\Helpers;

use App\Models\Auditoria;

class AuditoriaHelper
{
    public static function registrar($tabla, $registroId, $accion, $usuarioId, $oldData = null, $newData = null)
    {
        Auditoria::create([
            'user_id'          => $usuarioId ?? optional(auth()->user())->id,
            'tabla'            => $tabla,
            'registro_id'      => $registroId,
            'accion'           => $accion,
            'datos_anteriores' => $oldData,
            'datos_nuevos'     => $newData,
            'ip'               => request()->ip(),
            'navegador'        => request()->header('User-Agent'),
        ]);
    }

    /**
     * Convierte acciones técnicas a un texto más entendible.
     */
    public static function accionLegible($accion)
    {
        $map = [
            // Acciones genéricas
            'crear'      => 'Registro creado',
            'actualizar' => 'Registro actualizado',
            'eliminar'   => 'Registro eliminado',

            // Acciones específicas de solicitudes
            'solicitud_creada'   => 'Solicitud creada',
            'solicitud_aprobada' => 'Solicitud aprobada',
            'solicitud_rechazada'=> 'Solicitud rechazada',

            // Acciones específicas de usuarios
            'usuario_creado'     => 'Usuario creado',
            'usuario_actualizado'=> 'Usuario actualizado',
            'usuario_eliminado'  => 'Usuario eliminado',
        ];

        return $map[$accion] ?? ucfirst(str_replace('_', ' ', $accion));
    }
}
