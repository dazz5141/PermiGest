<?php

namespace App\Support;

use Illuminate\Notifications\Notification;
use Throwable;

final class NotificacionSegura
{
    public static function enviar(object $notificable, Notification $notificacion): bool
    {
        try {
            $notificable->notify($notificacion);

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
