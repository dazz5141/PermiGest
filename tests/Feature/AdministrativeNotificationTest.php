<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\EstadoSolicitud;
use App\Models\PeriodoAdministrativo;
use App\Models\RestauracionPermiso;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Notifications\ContrasenaRestablecidaUsuario;
use App\Notifications\DiasRestauradosSolicitante;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class AdministrativeNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_restoration_notifies_only_applicant_with_safe_content(): void
    {
        Notification::fake()->serializeAndRestore();

        $admin = $this->user('admin@colegio.cl');
        $docente = $this->user('docente@colegio.cl');
        $solicitud = $this->createApprovedSolicitud($docente);

        $response = $this->actingAs($admin)->post(
            route('admin.restauraciones.store'),
            $this->restorationData($solicitud, 0.5)
        );

        $response->assertRedirect(route('admin.restauraciones.index'));
        $restauracion = RestauracionPermiso::latest('id')->firstOrFail();
        Notification::assertSentToTimes($docente, DiasRestauradosSolicitante::class, 1);
        Notification::assertNothingSentTo($admin);
        Notification::assertSentTo(
            $docente,
            DiasRestauradosSolicitante::class,
            fn (DiasRestauradosSolicitante $notification, array $channels): bool => $channels === ['mail']
                && $this->hasSafeRestorationContent($notification, $docente, $restauracion)
        );
    }

    public function test_invalid_restoration_does_not_notify(): void
    {
        Notification::fake();

        $admin = $this->user('admin@colegio.cl');
        $docente = $this->user('docente@colegio.cl');
        $solicitud = $this->createApprovedSolicitud($docente);

        $response = $this->actingAs($admin)->post(
            route('admin.restauraciones.store'),
            $this->restorationData($solicitud, 1.5)
        );

        $response->assertSessionHasErrors('dias_restaurados');
        $this->assertDatabaseCount('restauraciones_permiso', 0);
        Notification::assertNothingSent();
    }

    public function test_restoration_audit_failure_rolls_back_without_notifying(): void
    {
        Notification::fake();
        $this->withoutExceptionHandling();

        $admin = $this->user('admin@colegio.cl');
        $docente = $this->user('docente@colegio.cl');
        $solicitud = $this->createApprovedSolicitud($docente);
        Auditoria::creating(function (Auditoria $auditoria): void {
            if ($auditoria->accion === 'solicitud_restaurada') {
                throw new RuntimeException('Fallo forzado de auditoria de restauracion.');
            }
        });

        try {
            $this->actingAs($admin)->post(
                route('admin.restauraciones.store'),
                $this->restorationData($solicitud, 0.5)
            );
            $this->fail('La restauracion debio revertirse al fallar la auditoria.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo forzado de auditoria de restauracion.', $exception->getMessage());
        }

        $this->assertDatabaseCount('restauraciones_permiso', 0);
        Notification::assertNothingSent();
    }

    public function test_password_reset_notifies_only_affected_user_without_credentials(): void
    {
        Notification::fake()->serializeAndRestore();

        $admin = $this->user('admin@colegio.cl');
        $docente = $this->user('docente@colegio.cl');
        $docente->setRememberToken('token-anterior-privado');
        $docente->save();

        $response = $this->actingAs($admin)->post(route('admin.usuarios.reset', $docente), [
            'current_password' => 'admin123',
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $this->assertTrue(Hash::check('NuevaClave123', $docente->fresh()->password));
        Notification::assertSentToTimes($docente, ContrasenaRestablecidaUsuario::class, 1);
        Notification::assertNothingSentTo($admin);
        Notification::assertSentTo(
            $docente,
            ContrasenaRestablecidaUsuario::class,
            fn (ContrasenaRestablecidaUsuario $notification, array $channels): bool => $channels === ['mail']
                && $this->hasSafePasswordContent($notification, $docente)
        );
    }

    public function test_invalid_password_reset_does_not_notify(): void
    {
        Notification::fake();

        $admin = $this->user('admin@colegio.cl');
        $docente = $this->user('docente@colegio.cl');

        $response = $this->actingAs($admin)->post(route('admin.usuarios.reset', $docente), [
            'current_password' => 'ClaveIncorrecta123',
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('docente123', $docente->fresh()->password));
        Notification::assertNothingSent();
    }

    public function test_password_reset_audit_failure_rolls_back_without_notifying(): void
    {
        Notification::fake();
        $this->withoutExceptionHandling();
        config()->set('session.driver', 'database');

        $admin = $this->user('admin@colegio.cl');
        $docente = $this->user('docente@colegio.cl');
        $docente->setRememberToken('token-anterior');
        $docente->save();
        DB::table('sessions')->insert([
            'id' => 'sesion-docente-fase-cuatro',
            'user_id' => $docente->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);
        Auditoria::creating(function (Auditoria $auditoria): void {
            if ($auditoria->accion === 'usuario_password_restablecida') {
                throw new RuntimeException('Fallo forzado de auditoria de contrasena.');
            }
        });

        try {
            $this->actingAs($admin)->post(route('admin.usuarios.reset', $docente), [
                'current_password' => 'admin123',
                'password' => 'NuevaClave123',
                'password_confirmation' => 'NuevaClave123',
            ]);
            $this->fail('El restablecimiento debio revertirse al fallar la auditoria.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo forzado de auditoria de contrasena.', $exception->getMessage());
        }

        $docente->refresh();
        $this->assertTrue(Hash::check('docente123', $docente->password));
        $this->assertSame('token-anterior', $docente->getRememberToken());
        $this->assertDatabaseHas('sessions', ['id' => 'sesion-docente-fase-cuatro']);
        Notification::assertNothingSent();
    }

    private function user(string $email): User
    {
        return User::where('correo_institucional', $email)->firstOrFail();
    }

    private function createApprovedSolicitud(User $docente): Solicitud
    {
        $tipo = TipoSolicitud::where('codigo', TipoSolicitud::CODIGO_CON_GOCE)->firstOrFail();
        $estado = EstadoSolicitud::where('codigo', EstadoSolicitud::CODIGO_APROBADO)->firstOrFail();
        $periodo = PeriodoAdministrativo::where('activo', true)->firstOrFail();

        return Solicitud::create([
            'user_id' => $docente->id,
            'periodo_id' => $periodo->id,
            'tipo_solicitud_id' => $tipo->id,
            'estado_solicitud_id' => $estado->id,
            'motivo' => 'Antecedente privado de la solicitud.',
            'fecha_desde' => now()->addDays(10)->toDateString(),
            'fecha_hasta' => now()->addDays(10)->toDateString(),
            'dias_solicitados' => 1.0,
            'fecha_envio' => now(),
            'fecha_revision' => now(),
            'token_validacion' => 'token-privado-de-restauracion',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function restorationData(Solicitud $solicitud, float $dias): array
    {
        return [
            'solicitud_id' => $solicitud->id,
            'tipo' => 'parcial',
            'dias_restaurados' => $dias,
            'motivo' => 'Motivo medico privado.',
            'observacion' => 'Observacion administrativa privada.',
            'documento_referencia' => 'DOCUMENTO-PRIVADO-001',
        ];
    }

    private function hasSafeRestorationContent(
        DiasRestauradosSolicitante $notification,
        User $docente,
        RestauracionPermiso $restauracion
    ): bool {
        $message = $notification->toMail($docente);
        $content = implode(' ', $message->introLines);

        return $notification->restauracion->is($restauracion)
            && $message->actionUrl === route('solicitudes.show', $restauracion->solicitud_id)
            && str_contains($content, "Folio: #{$restauracion->solicitud_id}")
            && str_contains($content, 'Dias restaurados: 0,5')
            && ! str_contains($content, $restauracion->motivo)
            && ! str_contains($content, $restauracion->observacion)
            && ! str_contains($content, (string) $restauracion->documento_referencia)
            && ! str_contains($content, $restauracion->solicitud->motivo)
            && ! str_contains($content, (string) $restauracion->solicitud->token_validacion);
    }

    private function hasSafePasswordContent(
        ContrasenaRestablecidaUsuario $notification,
        User $docente
    ): bool {
        $message = $notification->toMail($docente);
        $content = implode(' ', $message->introLines);

        return str_contains($content, 'contrasena de tu cuenta fue restablecida')
            && str_contains($content, 'contacta de inmediato al administrador')
            && ! str_contains($content, 'NuevaClave123')
            && ! str_contains($content, 'admin123')
            && ! str_contains($content, (string) $docente->password)
            && ! str_contains($content, 'token-anterior-privado');
    }
}
