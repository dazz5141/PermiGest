<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PermiGestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationInfrastructureTest extends TestCase
{
    public function test_user_routes_mail_notifications_to_institutional_email(): void
    {
        $usuario = new User([
            'correo_institucional' => 'docente@colegio.cl',
        ]);

        $this->assertSame(
            'docente@colegio.cl',
            $usuario->routeNotificationFor('mail', new InfrastructureNotification)
        );
    }

    public function test_base_notification_is_queued_after_commit(): void
    {
        $notification = new InfrastructureNotification;

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertInstanceOf(ShouldQueueAfterCommit::class, $notification);
    }

    public function test_queued_notification_job_is_marked_for_after_commit_dispatch(): void
    {
        Queue::fake();

        $usuario = new User([
            'correo_institucional' => 'docente@colegio.cl',
        ]);

        $usuario->notify(new InfrastructureNotification);

        Queue::assertPushed(
            SendQueuedNotifications::class,
            fn (SendQueuedNotifications $job): bool => $job->afterCommit === true
                && $job->notification instanceof InfrastructureNotification
        );
    }

    public function test_notifications_define_progressive_retry_policy(): void
    {
        Queue::fake();

        $usuario = new User([
            'correo_institucional' => 'docente@colegio.cl',
        ]);
        $notification = new InfrastructureNotification;
        $usuario->notify($notification);

        $this->assertSame(4, $notification->tries);
        $this->assertSame([60, 300, 900], $notification->backoff());
        Queue::assertPushed(
            SendQueuedNotifications::class,
            fn (SendQueuedNotifications $job): bool => $job->tries === 4
                && $job->backoff() === [60, 300, 900]
        );
    }
}

class InfrastructureNotification extends PermiGestNotification
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
            ->subject('Prueba de infraestructura')
            ->line('Notificacion de prueba.');
    }
}
