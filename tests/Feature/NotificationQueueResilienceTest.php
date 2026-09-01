<?php

namespace Tests\Feature;

use App\Models\EstadoSolicitud;
use App\Models\PeriodoAdministrativo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Queue\Events\JobQueueing;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class NotificationQueueResilienceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('queue.default', 'database');
        $this->seed(DatabaseSeeder::class);
    }

    public function test_request_creation_remains_successful_when_enqueue_fails_for_both_recipients(): void
    {
        $attempts = 0;
        $this->forceQueueFailure($attempts);

        $docente = $this->user('docente@colegio.cl');
        $response = $this->actingAs($docente)->post(route('solicitudes.store'), $this->validRequestData());

        $response->assertRedirect(route('solicitudes.index'));
        $response->assertSessionHas('success', 'Solicitud enviada correctamente.');
        $solicitud = Solicitud::latest('id')->firstOrFail();
        $this->assertSame(2, $attempts);
        $this->assertDatabaseHas('auditorias', [
            'registro_id' => $solicitud->id,
            'accion' => 'solicitud_creada',
        ]);
    }

    public function test_resolution_remains_successful_when_enqueue_fails(): void
    {
        $attempts = 0;
        $this->forceQueueFailure($attempts);

        $jefe = $this->user('jefe@colegio.cl');
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_PENDIENTE);
        $response = $this->actingAs($jefe)->post(route('resoluciones.update', $solicitud), [
            'accion' => 'aprobado',
        ]);

        $response->assertSessionHas('success', 'Resolucion registrada correctamente.');
        $this->assertSame(1, $attempts);
        $this->assertSame(EstadoSolicitud::CODIGO_APROBADO, $solicitud->fresh()->estado->codigo);
        $this->assertDatabaseHas('resoluciones', ['solicitud_id' => $solicitud->id]);
    }

    public function test_restoration_remains_successful_when_enqueue_fails(): void
    {
        $attempts = 0;
        $this->forceQueueFailure($attempts);

        $admin = $this->user('admin@colegio.cl');
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_APROBADO);
        $response = $this->actingAs($admin)->post(route('admin.restauraciones.store'), [
            'solicitud_id' => $solicitud->id,
            'tipo' => 'parcial',
            'dias_restaurados' => 0.5,
            'motivo' => 'Antecedente administrativo',
            'observacion' => 'Restauracion valida para probar resiliencia.',
            'documento_referencia' => 'DOC-RES-001',
        ]);

        $response->assertRedirect(route('admin.restauraciones.index'));
        $this->assertSame(1, $attempts);
        $this->assertDatabaseHas('restauraciones_permiso', [
            'solicitud_id' => $solicitud->id,
            'dias_restaurados' => 0.5,
        ]);
    }

    public function test_password_reset_remains_successful_when_enqueue_fails(): void
    {
        $attempts = 0;
        $this->forceQueueFailure($attempts);

        $admin = $this->user('admin@colegio.cl');
        $docente = $this->user('docente@colegio.cl');
        $response = $this->actingAs($admin)->post(route('admin.usuarios.reset', $docente), [
            'current_password' => 'admin123',
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertSame(1, $attempts);
        $this->assertTrue(Hash::check('NuevaClave123', $docente->fresh()->password));
        $this->assertDatabaseHas('auditorias', [
            'registro_id' => $docente->id,
            'accion' => 'usuario_password_restablecida',
        ]);
    }

    public function test_failed_reminder_enqueue_removes_daily_mark_and_allows_retry(): void
    {
        $shouldFail = true;
        Event::listen(JobQueueing::class, function () use (&$shouldFail): void {
            if ($shouldFail) {
                $shouldFail = false;
                throw new RuntimeException('Fallo forzado al encolar el recordatorio.');
            }
        });
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_PENDIENTE, now()->subHours(48));

        $this->assertSame(1, Artisan::call('solicitudes:recordar-pendientes'));
        $this->assertDatabaseMissing('auditorias', [
            'registro_id' => $solicitud->id,
            'accion' => 'recordatorio_solicitud_pendiente',
        ]);
        $this->assertDatabaseCount('jobs', 0);

        $this->assertSame(0, Artisan::call('solicitudes:recordar-pendientes'));
        $this->assertDatabaseHas('auditorias', [
            'registro_id' => $solicitud->id,
            'accion' => 'recordatorio_solicitud_pendiente',
        ]);
        $this->assertDatabaseCount('jobs', 1);
    }

    private function user(string $email): User
    {
        return User::where('correo_institucional', $email)->firstOrFail();
    }

    private function forceQueueFailure(int &$attempts): void
    {
        Event::listen(JobQueueing::class, function () use (&$attempts): void {
            $attempts++;

            throw new RuntimeException('Fallo forzado al encolar la notificacion.');
        });
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
            'tipo_codigo' => TipoSolicitud::CODIGO_CON_GOCE,
            'motivo' => 'Solicitud valida para probar resiliencia.',
            'dias_solicitados' => 0.5,
            'jornada' => 'manana',
            'fecha_desde' => $fecha->toDateString(),
            'fecha_hasta' => $fecha->toDateString(),
            'password' => 'docente123',
        ];
    }

    private function createSolicitud(string $estadoCodigo, ?Carbon $createdAt = null): Solicitud
    {
        $docente = $this->user('docente@colegio.cl');
        $jefe = $this->user('jefe@colegio.cl');
        $tipo = TipoSolicitud::where('codigo', TipoSolicitud::CODIGO_CON_GOCE)->firstOrFail();
        $estado = EstadoSolicitud::where('codigo', $estadoCodigo)->firstOrFail();
        $periodo = PeriodoAdministrativo::where('activo', true)->firstOrFail();
        $createdAt ??= now();

        $solicitud = Solicitud::create([
            'user_id' => $docente->id,
            'periodo_id' => $periodo->id,
            'tipo_solicitud_id' => $tipo->id,
            'estado_solicitud_id' => $estado->id,
            'validador_id' => $jefe->id,
            'motivo' => 'Prueba de resiliencia de cola.',
            'fecha_desde' => now()->addDays(10)->toDateString(),
            'fecha_hasta' => now()->addDays(10)->toDateString(),
            'dias_solicitados' => 1.0,
            'fecha_envio' => $createdAt,
        ]);

        $solicitud->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        return $solicitud;
    }
}
