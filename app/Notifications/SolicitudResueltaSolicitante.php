<?php

namespace App\Notifications;

use App\Models\Solicitud;
use Illuminate\Notifications\Messages\MailMessage;

class SolicitudResueltaSolicitante extends PermiGestNotification
{
    public function __construct(
        public Solicitud $solicitud,
        public string $accion,
        public ?string $comentario = null
    ) {}

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

        $aprobada = $this->accion === 'aprobado';
        $resultado = $aprobada ? 'aprobada' : 'rechazada';

        $message = (new MailMessage)
            ->subject("PermiGest: solicitud #{$this->solicitud->id} {$resultado}")
            ->greeting("Hola {$notifiable->nombre_completo}")
            ->line("Tu solicitud de permiso fue {$resultado}.")
            ->line('Folio: #'.$this->solicitud->id)
            ->line('Tipo: '.($this->solicitud->tipo?->nombre ?? 'Permiso administrativo'))
            ->line('Desde: '.$this->solicitud->fecha_desde?->format('d/m/Y'))
            ->line('Hasta: '.$this->solicitud->fecha_hasta?->format('d/m/Y'))
            ->line('Dias solicitados: '.number_format((float) $this->solicitud->dias_solicitados, 1, ',', '.'));

        if ($this->comentario) {
            $label = $aprobada ? 'Comentario' : 'Motivo del rechazo';
            $message->line($label.': '.$this->comentario);
        }

        return $message->action('Ver solicitud', route('solicitudes.show', $this->solicitud->id));
    }
}
