<?php

namespace Tests\Feature;

use App\Models\EstadoSolicitud;
use App\Models\PeriodoAdministrativo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Notifications\SolicitudCreadaJefeDirecto;
use App\Notifications\SolicitudPendienteRecordatorio;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PendingNotificationDeliveryValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_queued_pending_notifications_are_delivered_when_they_remain_valid(): void
    {
        Queue::fake();
        Event::fake([NotificationSent::class]);

        $jefe = $this->user('jefe@colegio.cl');
        $solicitud = $this->createPendingSolicitud($jefe);
        $jefe->notify(new SolicitudCreadaJefeDirecto($solicitud));
        $jefe->notify(new SolicitudPendienteRecordatorio($solicitud));
        $jobs = Queue::pushed(SendQueuedNotifications::class);

        $this->assertCount(2, $jobs);
        $jobs->each(fn (SendQueuedNotifications $job) => $job->handle($this->app->make(ChannelManager::class)));

        Event::assertDispatchedTimes(NotificationSent::class, 2);
    }

    public function test_queued_pending_notifications_are_suppressed_after_resolution(): void
    {
        Queue::fake();
        Event::fake([NotificationSent::class]);

        $jefe = $this->user('jefe@colegio.cl');
        $solicitud = $this->createPendingSolicitud($jefe);
        $jefe->notify(new SolicitudCreadaJefeDirecto($solicitud));
        $jefe->notify(new SolicitudPendienteRecordatorio($solicitud));
        $jobs = Queue::pushed(SendQueuedNotifications::class);
        $estadoAprobado = EstadoSolicitud::where('codigo', EstadoSolicitud::CODIGO_APROBADO)->firstOrFail();
        $solicitud->update(['estado_solicitud_id' => $estadoAprobado->id]);

        $this->assertCount(2, $jobs);
        $jobs->each(fn (SendQueuedNotifications $job) => $job->handle($this->app->make(ChannelManager::class)));

        Event::assertNotDispatched(NotificationSent::class);
    }

    public function test_queued_pending_notifications_are_suppressed_for_ineligible_validator(): void
    {
        Queue::fake();
        Event::fake([NotificationSent::class]);

        $jefe = $this->user('jefe@colegio.cl');
        $solicitud = $this->createPendingSolicitud($jefe);
        $jefe->notify(new SolicitudCreadaJefeDirecto($solicitud));
        $jefe->notify(new SolicitudPendienteRecordatorio($solicitud));
        $jobs = Queue::pushed(SendQueuedNotifications::class);
        $jefe->update(['activo' => false]);

        $this->assertCount(2, $jobs);
        $jobs->each(fn (SendQueuedNotifications $job) => $job->handle($this->app->make(ChannelManager::class)));

        Event::assertNotDispatched(NotificationSent::class);
    }

    public function test_queued_pending_notifications_are_suppressed_after_validator_role_change(): void
    {
        Queue::fake();
        Event::fake([NotificationSent::class]);

        $jefe = $this->user('jefe@colegio.cl');
        $solicitud = $this->createPendingSolicitud($jefe);
        $jefe->notify(new SolicitudCreadaJefeDirecto($solicitud));
        $jefe->notify(new SolicitudPendienteRecordatorio($solicitud));
        $jobs = Queue::pushed(SendQueuedNotifications::class);
        $rolFuncionarioId = $this->user('docente@colegio.cl')->rol_id;
        $jefe->update(['rol_id' => $rolFuncionarioId]);

        $this->assertCount(2, $jobs);
        $jobs->each(fn (SendQueuedNotifications $job) => $job->handle($this->app->make(ChannelManager::class)));

        Event::assertNotDispatched(NotificationSent::class);
    }

    public function test_queued_pending_notifications_are_suppressed_after_reassignment(): void
    {
        Queue::fake();
        Event::fake([NotificationSent::class]);

        $jefeOriginal = $this->user('jefe@colegio.cl');
        $jefeNuevo = User::create([
            'nombres' => 'Nueva',
            'apellidos' => 'Jefatura',
            'run' => '55.555.555-5',
            'correo_institucional' => 'nueva.jefatura@colegio.cl',
            'cargo' => 'Director',
            'rol_id' => $jefeOriginal->rol_id,
            'password' => 'ClaveJefatura123',
            'activo' => true,
        ]);
        $solicitud = $this->createPendingSolicitud($jefeOriginal);
        $jefeOriginal->notify(new SolicitudCreadaJefeDirecto($solicitud));
        $jefeOriginal->notify(new SolicitudPendienteRecordatorio($solicitud));
        $jobs = Queue::pushed(SendQueuedNotifications::class);
        $solicitud->update(['validador_id' => $jefeNuevo->id]);

        $this->assertCount(2, $jobs);
        $jobs->each(fn (SendQueuedNotifications $job) => $job->handle($this->app->make(ChannelManager::class)));

        Event::assertNotDispatched(NotificationSent::class);
    }

    private function user(string $email): User
    {
        return User::where('correo_institucional', $email)->firstOrFail();
    }

    private function createPendingSolicitud(User $jefe): Solicitud
    {
        $docente = $this->user('docente@colegio.cl');
        $tipo = TipoSolicitud::where('codigo', TipoSolicitud::CODIGO_CON_GOCE)->firstOrFail();
        $estado = EstadoSolicitud::where('codigo', EstadoSolicitud::CODIGO_PENDIENTE)->firstOrFail();
        $periodo = PeriodoAdministrativo::where('activo', true)->firstOrFail();

        return Solicitud::create([
            'user_id' => $docente->id,
            'periodo_id' => $periodo->id,
            'tipo_solicitud_id' => $tipo->id,
            'estado_solicitud_id' => $estado->id,
            'validador_id' => $jefe->id,
            'motivo' => 'Prueba de vigencia de la notificacion.',
            'fecha_desde' => now()->addDays(10)->toDateString(),
            'fecha_hasta' => now()->addDays(10)->toDateString(),
            'dias_solicitados' => 0.5,
            'fecha_envio' => now(),
        ]);
    }
}
