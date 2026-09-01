<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class ContrasenaRestablecidaUsuario extends PermiGestNotification
{
    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('PermiGest: contrasena restablecida')
            ->greeting("Hola {$notifiable->nombre_completo}")
            ->line('La contrasena de tu cuenta fue restablecida por un usuario autorizado.')
            ->line('Si no reconoces esta accion, contacta de inmediato al administrador del sistema.');
    }
}
