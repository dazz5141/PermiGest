<?php

namespace App\Notifications;

use App\Models\RestauracionPermiso;
use Illuminate\Notifications\Messages\MailMessage;

class DiasRestauradosSolicitante extends PermiGestNotification
{
    public function __construct(public RestauracionPermiso $restauracion) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->restauracion->loadMissing('solicitud');

        $solicitud = $this->restauracion->solicitud;
        $tipo = $this->restauracion->tipo === 'total' ? 'Total' : 'Parcial';

        return (new MailMessage)
            ->subject("PermiGest: dias restaurados en solicitud #{$solicitud->id}")
            ->greeting("Hola {$notifiable->nombre_completo}")
            ->line('Se registro una restauracion de dias en tu permiso administrativo.')
            ->line('Folio: #'.$solicitud->id)
            ->line('Tipo de restauracion: '.$tipo)
            ->line('Dias restaurados: '.number_format((float) $this->restauracion->dias_restaurados, 1, ',', '.'))
            ->action('Ver solicitud', route('solicitudes.show', $solicitud->id));
    }
}
