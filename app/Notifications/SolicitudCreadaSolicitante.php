<?php

namespace App\Notifications;

use App\Models\Solicitud;
use Illuminate\Notifications\Messages\MailMessage;

class SolicitudCreadaSolicitante extends PermiGestNotification
{
    public function __construct(public Solicitud $solicitud) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->solicitud->loadMissing('tipo');

        return (new MailMessage)
            ->subject("PermiGest: solicitud #{$this->solicitud->id} recibida")
            ->greeting("Hola {$notifiable->nombre_completo}")
            ->line('Tu solicitud de permiso fue recibida correctamente y quedo pendiente de revision.')
            ->line('Folio: #'.$this->solicitud->id)
            ->line('Tipo: '.($this->solicitud->tipo?->nombre ?? 'Permiso administrativo'))
            ->line('Desde: '.$this->solicitud->fecha_desde?->format('d/m/Y'))
            ->line('Hasta: '.$this->solicitud->fecha_hasta?->format('d/m/Y'))
            ->line('Dias solicitados: '.number_format((float) $this->solicitud->dias_solicitados, 1, ',', '.'))
            ->action('Ver solicitud', route('solicitudes.show', $this->solicitud->id))
            ->line('Recibiras una nueva notificacion cuando la solicitud sea resuelta.');
    }
}
