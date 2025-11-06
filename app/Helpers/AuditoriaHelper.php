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
}
