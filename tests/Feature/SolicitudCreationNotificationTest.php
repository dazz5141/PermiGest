<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Solicitud;
use App\Models\User;
use App\Notifications\SolicitudCreadaJefeDirecto;
use App\Notifications\SolicitudCreadaSolicitante;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class SolicitudCreationNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_valid_request_notifies_applicant_and_assigned_jefe_once(): void
    {
        Notification::fake()->serializeAndRestore();

        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        $jefe = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $admin = User::where('correo_institucional', 'admin@colegio.cl')->firstOrFail();

        $response = $this->actingAs($docente)->post(route('solicitudes.store'), $this->validRequestData());

        $response->assertRedirect(route('solicitudes.index'));
        $solicitud = Solicitud::latest('id')->firstOrFail();

        $this->assertSame($jefe->id, $solicitud->validador_id);
        Notification::assertSentToTimes($docente, SolicitudCreadaSolicitante::class, 1);
        Notification::assertSentToTimes($jefe, SolicitudCreadaJefeDirecto::class, 1);
        Notification::assertNothingSentTo($admin);

        Notification::assertSentTo(
            $docente,
            SolicitudCreadaSolicitante::class,
            fn (SolicitudCreadaSolicitante $notification, array $channels): bool => $channels === ['mail']
                && $this->hasSafeApplicantContent($notification, $docente, $solicitud)
        );

        Notification::assertSentTo(
            $jefe,
            SolicitudCreadaJefeDirecto::class,
            fn (SolicitudCreadaJefeDirecto $notification, array $channels): bool => $channels === ['mail']
                && $this->hasSafeJefeContent($notification, $jefe, $solicitud)
        );
    }

    public function test_invalid_request_does_not_create_or_notify(): void
    {
        Notification::fake();

        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        $data = $this->validRequestData();
        $data['fecha_desde'] = now()->subDay()->toDateString();
        $data['fecha_hasta'] = $data['fecha_desde'];

        $response = $this->actingAs($docente)->post(route('solicitudes.store'), $data);

        $response->assertSessionHasErrors('fecha_desde');
        $this->assertDatabaseCount('solicitudes', 0);
        Notification::assertNothingSent();
    }

    public function test_inactive_assigned_jefe_is_replaced_by_active_jefe(): void
    {
        Notification::fake();

        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        $jefeInactivo = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $jefeInactivo->update(['activo' => false]);
        $jefeActivo = User::create([
            'nombres' => 'Jefatura',
            'apellidos' => 'Alternativa',
            'run' => '55.555.555-5',
            'correo_institucional' => 'jefatura.alternativa@colegio.cl',
            'cargo' => 'Director',
            'rol_id' => $jefeInactivo->rol_id,
            'password' => 'ClaveJefatura123',
            'activo' => true,
        ]);

        $response = $this->actingAs($docente)->post(route('solicitudes.store'), $this->validRequestData());

        $response->assertRedirect(route('solicitudes.index'));
        $solicitud = Solicitud::latest('id')->firstOrFail();
        $this->assertSame($jefeActivo->id, $solicitud->validador_id);
        Notification::assertSentToTimes($jefeActivo, SolicitudCreadaJefeDirecto::class, 1);
        Notification::assertNothingSentTo($jefeInactivo);
    }

    public function test_request_is_not_created_when_there_is_no_active_jefe(): void
    {
        Notification::fake();

        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        User::where('correo_institucional', 'jefe@colegio.cl')->update(['activo' => false]);

        $response = $this->actingAs($docente)->post(route('solicitudes.store'), $this->validRequestData());

        $response->assertSessionHasErrors('jefe_directo');
        $this->assertDatabaseCount('solicitudes', 0);
        Notification::assertNothingSent();
    }

    public function test_audit_failure_rolls_back_request_without_notifying(): void
    {
        Notification::fake();
        $this->withoutExceptionHandling();

        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        Auditoria::creating(fn () => throw new RuntimeException('Fallo forzado de auditoria.'));

        try {
            $this->actingAs($docente)->post(route('solicitudes.store'), $this->validRequestData());
            $this->fail('La operacion debio revertirse al fallar la auditoria.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo forzado de auditoria.', $exception->getMessage());
        }

        $this->assertDatabaseCount('solicitudes', 0);
        Notification::assertNothingSent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validRequestData(): array
    {
        $fecha = Carbon::today()->addDay();

        while ($fecha->isWeekend()) {
            $fecha->addDay();
        }

        return [
            'tipo_codigo' => 'con_goce',
            'motivo' => 'Antecedente privado que no debe incluirse en el correo.',
            'dias_solicitados' => 0.5,
            'jornada' => 'manana',
            'fecha_desde' => $fecha->toDateString(),
            'fecha_hasta' => $fecha->toDateString(),
            'password' => 'docente123',
        ];
    }

    private function hasSafeApplicantContent(
        SolicitudCreadaSolicitante $notification,
        User $docente,
        Solicitud $solicitud
    ): bool {
        $message = $notification->toMail($docente);
        $content = implode(' ', $message->introLines);

        return $notification->solicitud->is($solicitud)
            && $message->actionUrl === route('solicitudes.show', $solicitud->id)
            && str_contains($content, "Folio: #{$solicitud->id}")
            && ! str_contains($content, $solicitud->motivo)
            && ! str_contains($content, (string) $solicitud->token_validacion);
    }

    private function hasSafeJefeContent(
        SolicitudCreadaJefeDirecto $notification,
        User $jefe,
        Solicitud $solicitud
    ): bool {
        $message = $notification->toMail($jefe);
        $content = implode(' ', $message->introLines);

        return $notification->solicitud->is($solicitud)
            && $message->actionUrl === route('resoluciones.index')
            && str_contains($content, $solicitud->usuario->nombre_completo)
            && ! str_contains($content, $solicitud->motivo)
            && ! str_contains($content, (string) $solicitud->token_validacion);
    }
}
