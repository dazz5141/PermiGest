<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserPasswordSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_with_password_that_meets_policy(): void
    {
        $admin = $this->createUser('admin', 'admin@colegio.cl', '11.111.111-1', 'AdminClave123');
        $funcionarioRole = $this->role('funcionario');

        $response = $this->actingAs($admin)->post(route('admin.usuarios.store'), [
            'nombres' => 'Nuevo',
            'apellidos' => 'Funcionario',
            'run' => '12.345.678-5',
            'correo_institucional' => 'nuevo@colegio.cl',
            'cargo' => 'Docente',
            'departamento' => 'Matematica',
            'rol_id' => $funcionarioRole->id,
            'jefe_directo_id' => null,
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $usuario = User::where('correo_institucional', 'nuevo@colegio.cl')->firstOrFail();
        $this->assertTrue(Hash::check('NuevaClave123', $usuario->password));
    }

    public function test_weak_passwords_are_rejected_when_creating_user(): void
    {
        $admin = $this->createUser('admin', 'admin@colegio.cl', '11.111.111-1', 'AdminClave123');
        $funcionarioRole = $this->role('funcionario');

        foreach (['corta1', 'SoloLetras', '12345678'] as $password) {
            $response = $this->actingAs($admin)->post(route('admin.usuarios.store'), [
                'nombres' => 'Nuevo',
                'apellidos' => 'Funcionario',
                'run' => '12.345.678-5',
                'correo_institucional' => 'nuevo@colegio.cl',
                'rol_id' => $funcionarioRole->id,
                'password' => $password,
                'password_confirmation' => $password,
            ]);

            $response->assertSessionHasErrors('password');
        }

        $this->assertDatabaseMissing('users', ['correo_institucional' => 'nuevo@colegio.cl']);
    }

    public function test_wrong_operator_password_does_not_reset_user_password(): void
    {
        $admin = $this->createUser('admin', 'admin@colegio.cl', '11.111.111-1', 'AdminClave123');
        $usuario = $this->createUser('funcionario', 'docente@colegio.cl', '44.444.444-4', 'ClaveOriginal123');

        $response = $this->actingAs($admin)->post(route('admin.usuarios.reset', $usuario), [
            'current_password' => 'ClaveIncorrecta123',
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('ClaveOriginal123', $usuario->fresh()->password));
    }

    public function test_authorized_reset_changes_password_and_invalidates_database_sessions(): void
    {
        config()->set('session.driver', 'database');

        $admin = $this->createUser('admin', 'admin@colegio.cl', '11.111.111-1', 'AdminClave123');
        $usuario = $this->createUser('funcionario', 'docente@colegio.cl', '44.444.444-4', 'ClaveOriginal123');
        $usuario->setRememberToken('token-anterior');
        $usuario->save();

        DB::table('sessions')->insert([
            'id' => 'sesion-docente',
            'user_id' => $usuario->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.usuarios.reset', $usuario), [
            'current_password' => 'AdminClave123',
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertRedirect(route('admin.usuarios.index'));
        $usuario->refresh();
        $this->assertTrue(Hash::check('NuevaClave123', $usuario->password));
        $this->assertNotSame('token-anterior', $usuario->getRememberToken());
        $this->assertDatabaseMissing('sessions', ['id' => 'sesion-docente']);
        $this->assertDatabaseHas('auditorias', [
            'user_id' => $admin->id,
            'registro_id' => $usuario->id,
            'accion' => 'usuario_password_restablecida',
        ]);
    }

    public function test_funcionario_cannot_reset_another_user_password(): void
    {
        $funcionario = $this->createUser('funcionario', 'uno@colegio.cl', '44.444.444-4', 'ClaveUsuario123');
        $otroUsuario = $this->createUser('funcionario', 'dos@colegio.cl', '12.345.678-5', 'ClaveOriginal123');

        $response = $this->actingAs($funcionario)->post(route('admin.usuarios.reset', $otroUsuario), [
            'current_password' => 'ClaveUsuario123',
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertForbidden();
        $this->assertTrue(Hash::check('ClaveOriginal123', $otroUsuario->fresh()->password));
    }

    public function test_encargado_cannot_reset_admin_password(): void
    {
        $encargado = $this->createUser('encargado_sistema', 'encargado@colegio.cl', '12.345.678-5', 'ClaveEncargado123');
        $admin = $this->createUser('admin', 'admin@colegio.cl', '11.111.111-1', 'ClaveAdmin123');

        $response = $this->actingAs($encargado)->post(route('admin.usuarios.reset', $admin), [
            'current_password' => 'ClaveEncargado123',
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertForbidden();
        $this->assertTrue(Hash::check('ClaveAdmin123', $admin->fresh()->password));
    }

    public function test_password_reset_attempts_are_rate_limited(): void
    {
        $admin = $this->createUser('admin', 'admin@colegio.cl', '11.111.111-1', 'AdminClave123');
        $usuario = $this->createUser('funcionario', 'docente@colegio.cl', '44.444.444-4', 'ClaveOriginal123');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->actingAs($admin)->post(route('admin.usuarios.reset', $usuario), [
                'current_password' => 'ClaveIncorrecta123',
                'password' => 'NuevaClave123',
                'password_confirmation' => 'NuevaClave123',
            ])->assertSessionHasErrors('current_password');
        }

        $response = $this->actingAs($admin)->post(route('admin.usuarios.reset', $usuario), [
            'current_password' => 'ClaveIncorrecta123',
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ]);

        $response->assertSessionHas('error', 'Demasiados intentos de restablecimiento. Intenta nuevamente en un minuto.');
        $this->assertTrue(Hash::check('ClaveOriginal123', $usuario->fresh()->password));
    }

    private function role(string $name): Rol
    {
        return Rol::firstOrCreate(
            ['nombre' => $name],
            ['descripcion' => ucfirst(str_replace('_', ' ', $name))]
        );
    }

    private function createUser(string $role, string $email, string $run, string $password): User
    {
        return User::create([
            'nombres' => 'Usuario',
            'apellidos' => ucfirst($role),
            'run' => $run,
            'correo_institucional' => $email,
            'cargo' => 'Docente',
            'rol_id' => $this->role($role)->id,
            'password' => $password,
            'activo' => true,
        ]);
    }
}
