<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\EstadoSolicitud;
use App\Models\PeriodoAdministrativo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Notifications\SolicitudPendienteRecordatorio;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class PendingRequestReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-31 12:00:00', 'UTC'));
        $this->seed(DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_eligible_request_notifies_assigned_validator_once_per_local_day(): void
    {
        Notification::fake()->serializeAndRestore();

        $docente = $this->user('docente@colegio.cl');
        $jefe = $this->user('jefe@colegio.cl');
        $admin = $this->user('admin@colegio.cl');
        $solicitud = $this->createSolicitud(
            $docente,
            $jefe,
            EstadoSolicitud::CODIGO_PENDIENTE,
            now()->subHours(48)
        );

        $this->assertSame(0, Artisan::call('solicitudes:recordar-pendientes'));
        $this->assertStringContainsString('Recordatorios enviados: 1. Omitidos: 0. Fallidos: 0.', Artisan::output());
        $this->assertSame(0, Artisan::call('solicitudes:recordar-pendientes'));
        $this->assertStringContainsString('Recordatorios enviados: 0. Omitidos: 1. Fallidos: 0.', Artisan::output());

        Notification::assertSentToTimes($jefe, SolicitudPendienteRecordatorio::class, 1);
        Notification::assertNothingSentTo($docente);
        Notification::assertNothingSentTo($admin);
        Notification::assertSentTo(
            $jefe,
            SolicitudPendienteRecordatorio::class,
            fn (SolicitudPendienteRecordatorio $notification, array $channels): bool => $channels === ['mail']
                && $this->hasSafeReminderContent($notification, $jefe, $solicitud)
        );
        $this->assertDatabaseCount('auditorias', 1);
        $this->assertDatabaseHas('auditorias', [
            'tabla' => 'solicitudes',
            'registro_id' => $solicitud->id,
            'accion' => 'recordatorio_solicitud_pendiente',
        ]);
    }

    public function test_recent_resolved_and_inactive_validator_requests_do_not_notify(): void
    {
        Notification::fake();

        $docente = $this->user('docente@colegio.cl');
        $jefe = $this->user('jefe@colegio.cl');

        $this->createSolicitud(
            $docente,
            $jefe,
            EstadoSolicitud::CODIGO_PENDIENTE,
            now()->subHours(12)
        );
        $this->createSolicitud(
            $docente,
            $jefe,
            EstadoSolicitud::CODIGO_APROBADO,
            now()->subHours(48)
        );
        $this->createSolicitud(
            $docente,
            $jefe,
            EstadoSolicitud::CODIGO_PENDIENTE,
            now()->subHours(48)
        );
        $jefe->update(['activo' => false]);

        $this->assertSame(0, Artisan::call('solicitudes:recordar-pendientes'));

        Notification::assertNothingSent();
        $this->assertDatabaseCount('auditorias', 0);
    }

    public function test_individual_failure_does_not_stop_other_reminders(): void
    {
        Notification::fake();

        $docente = $this->user('docente@colegio.cl');
        $jefe = $this->user('jefe@colegio.cl');
        $fallida = $this->createSolicitud(
            $docente,
            $jefe,
            EstadoSolicitud::CODIGO_PENDIENTE,
            now()->subHours(48)
        );
        $exitosa = $this->createSolicitud(
            $docente,
            $jefe,
            EstadoSolicitud::CODIGO_PENDIENTE,
            now()->subHours(48)
        );
        Auditoria::creating(function (Auditoria $auditoria) use ($fallida): void {
            if ((int) $auditoria->registro_id === $fallida->id) {
                throw new RuntimeException('Fallo forzado de auditoria de recordatorio.');
            }
        });

        $this->assertSame(1, Artisan::call('solicitudes:recordar-pendientes'));
        $this->assertStringContainsString('Recordatorios enviados: 1. Omitidos: 0. Fallidos: 1.', Artisan::output());

        Notification::assertSentToTimes($jefe, SolicitudPendienteRecordatorio::class, 1);
        $this->assertDatabaseMissing('auditorias', [
            'registro_id' => $fallida->id,
            'accion' => 'recordatorio_solicitud_pendiente',
        ]);
        $this->assertDatabaseHas('auditorias', [
            'registro_id' => $exitosa->id,
            'accion' => 'recordatorio_solicitud_pendiente',
        ]);
    }

    public function test_scheduler_runs_on_weekdays_at_eight_in_santiago(): void
    {
        $event = collect($this->app->make(Schedule::class)->events())
            ->first(fn ($scheduledEvent) => str_contains(
                (string) $scheduledEvent->command,
                'solicitudes:recordar-pendientes'
            ));

        $this->assertNotNull($event);
        $this->assertSame('0 8 * * 1-5', $event->expression);
        $this->assertSame('America/Santiago', $event->timezone);
        $this->assertTrue($event->withoutOverlapping);
    }

    private function user(string $email): User
    {
        return User::where('correo_institucional', $email)->firstOrFail();
    }

    private function createSolicitud(
        User $docente,
        User $jefe,
        string $estadoCodigo,
        Carbon $createdAt
    ): Solicitud {
        $tipo = TipoSolicitud::where('codigo', TipoSolicitud::CODIGO_CON_GOCE)->firstOrFail();
        $estado = EstadoSolicitud::where('codigo', $estadoCodigo)->firstOrFail();
        $periodo = PeriodoAdministrativo::where('activo', true)->firstOrFail();

        $solicitud = Solicitud::create([
            'user_id' => $docente->id,
            'periodo_id' => $periodo->id,
            'tipo_solicitud_id' => $tipo->id,
            'estado_solicitud_id' => $estado->id,
            'validador_id' => $jefe->id,
            'motivo' => 'Antecedente privado que no debe incluirse en el recordatorio.',
            'observaciones' => 'Observacion privada de la solicitud.',
            'fecha_desde' => now()->addDays(10)->toDateString(),
            'fecha_hasta' => now()->addDays(10)->toDateString(),
            'dias_solicitados' => 0.5,
            'fecha_envio' => $createdAt,
            'token_validacion' => 'token-privado-'.Str::uuid(),
        ]);

        $solicitud->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        return $solicitud;
    }

    private function hasSafeReminderContent(
        SolicitudPendienteRecordatorio $notification,
        User $jefe,
        Solicitud $solicitud
    ): bool {
        $message = $notification->toMail($jefe);
        $content = implode(' ', $message->introLines);

        return $notification->solicitud->is($solicitud)
            && $message->actionUrl === route('resoluciones.index')
            && str_contains($content, "Folio: #{$solicitud->id}")
            && str_contains($content, $solicitud->usuario->nombre_completo)
            && ! str_contains($content, $solicitud->motivo)
            && ! str_contains($content, (string) $solicitud->observaciones)
            && ! str_contains($content, (string) $solicitud->token_validacion);
    }
}
