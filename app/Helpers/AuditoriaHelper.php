<?php

namespace App\Helpers;

use App\Models\Auditoria;

class AuditoriaHelper
{
    public static function registrar($tabla, $registroId, $accion, $usuarioId, $oldData = null, $newData = null)
    {
        Auditoria::create([
            'user_id' => $usuarioId ?? optional(auth()->user())->id,
            'tabla' => $tabla,
            'registro_id' => $registroId,
            'accion' => $accion,
            'datos_anteriores' => $oldData,
            'datos_nuevos' => $newData,
            'ip' => request()->ip(),
            'navegador' => request()->header('User-Agent'),
        ]);
    }

    public static function accionLegible($accion)
    {
        $map = [
            'crear' => 'Registro creado',
            'actualizar' => 'Registro actualizado',
            'eliminar' => 'Registro eliminado',
            'solicitud_creada' => 'Solicitud creada',
            'solicitud_aprobada' => 'Solicitud aprobada',
            'solicitud_rechazada' => 'Solicitud rechazada',
            'solicitud_restaurada' => 'Solicitud restaurada',
            'usuario_creado' => 'Usuario creado',
            'usuario_actualizado' => 'Usuario actualizado',
            'usuario_eliminado' => 'Usuario eliminado',
            'usuario_activado' => 'Usuario activado',
            'usuario_desactivado' => 'Usuario desactivado',
            'usuario_password_restablecida' => 'Contrasena restablecida',
            'feriado_creado' => 'Feriado creado',
            'feriado_actualizado' => 'Feriado actualizado',
            'feriado_eliminado' => 'Feriado eliminado',
        ];

        return $map[$accion] ?? ucfirst(str_replace('_', ' ', $accion));
    }
}
