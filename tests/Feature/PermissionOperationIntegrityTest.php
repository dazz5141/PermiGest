<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\EstadoSolicitud;
use App\Models\PeriodoAdministrativo;
use App\Models\Resolucion;
use App\Models\Rol;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PermissionOperationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_authorized_jefe_resolves_pending_request_atomically(): void
    {
        $jefe = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_PENDIENTE);

        $response = $this->actingAs($jefe)->post(route('resoluciones.update', $solicitud), [
            'accion' => 'aprobado',
            'comentario' => 'Aprobada por jefatura.',
        ]);

        $response->assertSessionHas('success', 'Resolucion registrada correctamente.');
        $solicitud->refresh();
        $this->assertSame(EstadoSolicitud::CODIGO_APROBADO, $solicitud->estado->codigo);
        $this->assertSame($jefe->id, $solicitud->validador_id);
        $this->assertDatabaseHas('resoluciones', [
            'solicitud_id' => $solicitud->id,
            'user_id' => $jefe->id,
            'accion' => 'aprobado',
        ]);
        $this->assertDatabaseHas('auditorias', [
            'registro_id' => $solicitud->id,
            'accion' => 'solicitud_aprobada',
        ]);
    }

    public function test_resolved_request_cannot_receive_second_resolution(): void
    {
        $jefe = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_PENDIENTE);

        $this->actingAs($jefe)->post(route('resoluciones.update', $solicitud), [
            'accion' => 'aprobado',
        ])->assertSessionHas('success');

        $response = $this->actingAs($jefe)->post(route('resoluciones.update', $solicitud), [
            'accion' => 'rechazado',
        ]);

        $response->assertSessionHasErrors('solicitud');
        $this->assertSame(EstadoSolicitud::CODIGO_APROBADO, $solicitud->fresh()->estado->codigo);
        $this->assertDatabaseCount('resoluciones', 1);
    }

    public function test_unrelated_jefe_cannot_resolve_request(): void
    {
        $jefeRole = Rol::where('nombre', 'jefe_directo')->firstOrFail();
        $otroJefe = User::create([
            'nombres' => 'Otra',
            'apellidos' => 'Jefatura',
            'run' => '55.555.555-5',
            'correo_institucional' => 'otra.jefatura@colegio.cl',
            'cargo' => 'Jefatura',
            'rol_id' => $jefeRole->id,
            'password' => 'ClaveJefatura123',
            'activo' => true,
        ]);
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_PENDIENTE);

        $response = $this->actingAs($otroJefe)->post(route('resoluciones.update', $solicitud), [
            'accion' => 'aprobado',
        ]);

        $response->assertForbidden();
        $this->assertSame(EstadoSolicitud::CODIGO_PENDIENTE, $solicitud->fresh()->estado->codigo);
        $this->assertDatabaseCount('resoluciones', 0);
    }

    public function test_resolution_failure_rolls_back_request_update(): void
    {
        $this->withoutExceptionHandling();

        $jefe = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_PENDIENTE);
        Resolucion::creating(fn () => throw new RuntimeException('Fallo forzado de resolucion.'));

        try {
            $this->actingAs($jefe)->post(route('resoluciones.update', $solicitud), [
                'accion' => 'aprobado',
            ]);
            $this->fail('La operacion debio fallar antes de confirmar la transaccion.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo forzado de resolucion.', $exception->getMessage());
        }

        $solicitud->refresh();
        $this->assertSame(EstadoSolicitud::CODIGO_PENDIENTE, $solicitud->estado->codigo);
        $this->assertNull($solicitud->fecha_revision);
        $this->assertDatabaseCount('resoluciones', 0);
        $this->assertDatabaseMissing('auditorias', [
            'registro_id' => $solicitud->id,
            'accion' => 'solicitud_aprobada',
        ]);
    }

    public function test_partial_and_total_restorations_recalculate_available_balance(): void
    {
        $admin = User::where('correo_institucional', 'admin@colegio.cl')->firstOrFail();
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_APROBADO, 1.0);

        $this->actingAs($admin)->post(route('admin.restauraciones.store'), $this->restorationData(
            $solicitud,
            'parcial',
            0.5
        ))->assertRedirect(route('admin.restauraciones.index'));

        $this->actingAs($admin)->post(route('admin.restauraciones.store'), $this->restorationData(
            $solicitud,
            'total'
        ))->assertRedirect(route('admin.restauraciones.index'));

        $this->assertDatabaseCount('restauraciones_permiso', 2);
        $this->assertSame(1.0, (float) $solicitud->restauraciones()->sum('dias_restaurados'));
        $this->assertDatabaseCount('auditorias', 2);
    }

    public function test_restoration_cannot_exceed_recalculated_balance(): void
    {
        $admin = User::where('correo_institucional', 'admin@colegio.cl')->firstOrFail();
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_APROBADO, 1.0);

        $this->actingAs($admin)->post(route('admin.restauraciones.store'), $this->restorationData(
            $solicitud,
            'parcial',
            0.5
        ))->assertRedirect(route('admin.restauraciones.index'));

        $response = $this->actingAs($admin)->post(route('admin.restauraciones.store'), $this->restorationData(
            $solicitud,
            'parcial',
            1.0
        ));

        $response->assertSessionHasErrors('dias_restaurados');
        $this->assertDatabaseCount('restauraciones_permiso', 1);
        $this->assertSame(0.5, (float) $solicitud->restauraciones()->sum('dias_restaurados'));
    }

    public function test_partial_restoration_must_use_half_day_increments(): void
    {
        $admin = User::where('correo_institucional', 'admin@colegio.cl')->firstOrFail();
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_APROBADO, 1.0);

        $response = $this->actingAs($admin)->post(route('admin.restauraciones.store'), $this->restorationData(
            $solicitud,
            'parcial',
            0.6
        ));

        $response->assertSessionHasErrors('dias_restaurados');
        $this->assertDatabaseCount('restauraciones_permiso', 0);
    }

    public function test_restoration_audit_failure_rolls_back_restoration(): void
    {
        $this->withoutExceptionHandling();

        $admin = User::where('correo_institucional', 'admin@colegio.cl')->firstOrFail();
        $solicitud = $this->createSolicitud(EstadoSolicitud::CODIGO_APROBADO, 1.0);
        Auditoria::creating(fn () => throw new RuntimeException('Fallo forzado de auditoria.'));

        try {
            $this->actingAs($admin)->post(route('admin.restauraciones.store'), $this->restorationData(
                $solicitud,
                'total'
            ));
            $this->fail('La operacion debio fallar antes de confirmar la transaccion.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo forzado de auditoria.', $exception->getMessage());
        }

        $this->assertDatabaseCount('restauraciones_permiso', 0);
        $this->assertDatabaseMissing('auditorias', [
            'tabla' => 'restauraciones_permiso',
            'accion' => 'solicitud_restaurada',
        ]);
    }

    private function createSolicitud(string $estadoCodigo, float $dias = 1.0): Solicitud
    {
        $docente = User::where('correo_institucional', 'docente@colegio.cl')->firstOrFail();
        $tipo = TipoSolicitud::where('codigo', TipoSolicitud::CODIGO_CON_GOCE)->firstOrFail();
        $estado = EstadoSolicitud::where('codigo', $estadoCodigo)->firstOrFail();
        $periodo = PeriodoAdministrativo::where('activo', true)->firstOrFail();

        return Solicitud::create([
            'user_id' => $docente->id,
            'periodo_id' => $periodo->id,
            'tipo_solicitud_id' => $tipo->id,
            'estado_solicitud_id' => $estado->id,
            'motivo' => 'Prueba de integridad',
            'fecha_desde' => now()->addDays(10)->toDateString(),
            'fecha_hasta' => now()->addDays(10)->toDateString(),
            'dias_solicitados' => $dias,
            'fecha_envio' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function restorationData(Solicitud $solicitud, string $tipo, ?float $dias = null): array
    {
        return [
            'solicitud_id' => $solicitud->id,
            'tipo' => $tipo,
            'dias_restaurados' => $dias,
            'motivo' => 'Licencia medica',
            'observacion' => 'Se restituyen los dias conforme al antecedente presentado.',
            'documento_referencia' => 'LM-001',
        ];
    }
}
