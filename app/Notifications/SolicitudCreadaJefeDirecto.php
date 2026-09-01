<?php

namespace App\Notifications;

use App\Models\EstadoSolicitud;
use App\Models\Solicitud;
use Illuminate\Notifications\Messages\MailMessage;

class SolicitudCreadaJefeDirecto extends PermiGestNotification
{
    public function __construct(public Solicitud $solicitud) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return Solicitud::query()
            ->whereKey($this->solicitud->id)
            ->where('validador_id', $notifiable->id)
            ->whereHas('estado', fn ($query) => $query->where('codigo', EstadoSolicitud::CODIGO_PENDIENTE))
            ->whereHas('validador', fn ($query) => $query
                ->where('activo', true)
                ->whereHas('rol', fn ($roleQuery) => $roleQuery->where('nombre', 'jefe_directo')))
            ->exists();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->solicitud->loadMissing(['tipo', 'usuario']);

        return (new MailMessage)
            ->subject("PermiGest: nueva solicitud #{$this->solicitud->id} pendiente")
            ->greeting("Hola {$notifiable->nombre_completo}")
            ->line('Tienes una nueva solicitud de permiso pendiente de revision.')
            ->line('Folio: #'.$this->solicitud->id)
            ->line('Solicitante: '.($this->solicitud->usuario?->nombre_completo ?? 'Funcionario'))
            ->line('Tipo: '.($this->solicitud->tipo?->nombre ?? 'Permiso administrativo'))
            ->line('Desde: '.$this->solicitud->fecha_desde?->format('d/m/Y'))
            ->line('Hasta: '.$this->solicitud->fecha_hasta?->format('d/m/Y'))
            ->line('Dias solicitados: '.number_format((float) $this->solicitud->dias_solicitados, 1, ',', '.'))
            ->action('Revisar solicitudes', route('resoluciones.index'))
            ->line('Ingresa a PermiGest para aprobar o rechazar la solicitud.');
    }
}
