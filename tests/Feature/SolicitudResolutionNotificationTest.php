<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\EstadoSolicitud;
use App\Models\PeriodoAdministrativo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Notifications\SolicitudResueltaSolicitante;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class SolicitudResolutionNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_approval_notifies_only_applicant_with_safe_content(): void
    {
        Notification::fake()->serializeAndRestore();

        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        $jefe = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $admin = User::where('correo_institucional', 'admin@colegio.cl')->firstOrFail();
        $solicitud = $this->createPendingSolicitud($docente, $jefe);

        $response = $this->actingAs($jefe)->post(route('resoluciones.update', $solicitud), [
            'accion' => 'aprobado',
            'comentario' => 'Aprobada por jefatura.',
        ]);

        $response->assertSessionHas('success', 'Resolucion registrada correctamente.');
        $this->assertSame(EstadoSolicitud::CODIGO_APROBADO, $solicitud->fresh()->estado->codigo);
        Notification::assertSentToTimes($docente, SolicitudResueltaSolicitante::class, 1);
        Notification::assertNothingSentTo($jefe);
        Notification::assertNothingSentTo($admin);
        Notification::assertSentTo(
            $docente,
            SolicitudResueltaSolicitante::class,
            fn (SolicitudResueltaSolicitante $notification, array $channels): bool => $channels === ['mail']
                && $this->hasSafeResolutionContent(
                    $notification,
                    $docente,
                    $solicitud,
                    'aprobada',
                    'Aprobada por jefatura.'
                )
        );
    }

    public function test_rejection_without_reason_keeps_request_pending_and_does_not_notify(): void
    {
        Notification::fake();

        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        $jefe = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $solicitud = $this->createPendingSolicitud($docente, $jefe);

        $response = $this->actingAs($jefe)->post(route('resoluciones.update', $solicitud), [
            'accion' => 'rechazado',
        ]);

        $response->assertSessionHasErrors('comentario');
        $this->assertSame(EstadoSolicitud::CODIGO_PENDIENTE, $solicitud->fresh()->estado->codigo);
        $this->assertDatabaseCount('resoluciones', 0);
        Notification::assertNothingSent();
    }

    public function test_rejection_notifies_applicant_with_reason(): void
    {
        Notification::fake()->serializeAndRestore();

        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        $jefe = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $solicitud = $this->createPendingSolicitud($docente, $jefe);
        $motivoRechazo = 'Falta adjuntar el antecedente requerido.';

        $response = $this->actingAs($jefe)->post(route('resoluciones.update', $solicitud), [
            'accion' => 'rechazado',
            'comentario' => $motivoRechazo,
        ]);

        $response->assertSessionHas('success');
        $this->assertSame(EstadoSolicitud::CODIGO_RECHAZADO, $solicitud->fresh()->estado->codigo);
        Notification::assertSentToTimes($docente, SolicitudResueltaSolicitante::class, 1);
        Notification::assertSentTo(
            $docente,
            SolicitudResueltaSolicitante::class,
            fn (SolicitudResueltaSolicitante $notification, array $channels): bool => $channels === ['mail']
                && $this->hasSafeResolutionContent(
                    $notification,
                    $docente,
                    $solicitud,
                    'rechazada',
                    $motivoRechazo
                )
        );
    }

    public function test_audit_failure_rolls_back_resolution_without_notifying(): void
    {
        Notification::fake();
        $this->withoutExceptionHandling();

        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        $jefe = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $solicitud = $this->createPendingSolicitud($docente, $jefe);
        Auditoria::creating(fn () => throw new RuntimeException('Fallo forzado de auditoria.'));

        try {
            $this->actingAs($jefe)->post(route('resoluciones.update', $solicitud), [
                'accion' => 'aprobado',
            ]);
            $this->fail('La operacion debio revertirse al fallar la auditoria.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo forzado de auditoria.', $exception->getMessage());
        }

        $this->assertSame(EstadoSolicitud::CODIGO_PENDIENTE, $solicitud->fresh()->estado->codigo);
        $this->assertDatabaseCount('resoluciones', 0);
        Notification::assertNothingSent();
    }

    private function createPendingSolicitud(User $docente, User $jefe): Solicitud
    {
        $tipo = TipoSolicitud::where('codigo', TipoSolicitud::CODIGO_CON_GOCE)->firstOrFail();
        $estado = EstadoSolicitud::where('codigo', EstadoSolicitud::CODIGO_PENDIENTE)->firstOrFail();
        $periodo = PeriodoAdministrativo::where('activo', true)->firstOrFail();

        return Solicitud::create([
            'user_id' => $docente->id,
            'periodo_id' => $periodo->id,
            'tipo_solicitud_id' => $tipo->id,
            'estado_solicitud_id' => $estado->id,
            'validador_id' => $jefe->id,
            'motivo' => 'Antecedente privado que no debe incluirse en el correo.',
            'fecha_desde' => now()->addDays(10)->toDateString(),
            'fecha_hasta' => now()->addDays(10)->toDateString(),
            'dias_solicitados' => 0.5,
            'fecha_envio' => now(),
            'token_validacion' => 'token-privado-de-prueba',
        ]);
    }

    private function hasSafeResolutionContent(
        SolicitudResueltaSolicitante $notification,
        User $docente,
        Solicitud $solicitud,
        string $resultado,
        string $comentario
    ): bool {
        $message = $notification->toMail($docente);
        $content = implode(' ', $message->introLines);

        return $notification->solicitud->is($solicitud)
            && $message->actionUrl === route('solicitudes.show', $solicitud->id)
            && str_contains($content, "Folio: #{$solicitud->id}")
            && str_contains($content, $resultado)
            && str_contains($content, $comentario)
            && ! str_contains($content, $solicitud->motivo)
            && ! str_contains($content, (string) $solicitud->token_validacion);
    }
}
