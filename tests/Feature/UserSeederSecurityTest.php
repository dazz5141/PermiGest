<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class UserSeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_testing_environment_creates_five_demo_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $jefe = User::where('correo_institucional', 'jefe@colegio.cl')->firstOrFail();
        $this->assertTrue(Hash::check('jefe1234', $jefe->password));
    }

    public function test_non_demo_environment_requires_initial_admin_password(): void
    {
        $this->seed(RolesSeeder::class);
        $this->app->instance('env', 'production');
        config()->set('permigest.initial_admin_password');

        try {
            $this->runUserSeeder();
            $this->fail('El seeder debio exigir la clave inicial del administrador.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('PERMIGEST_INITIAL_ADMIN_PASSWORD', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_non_demo_environment_creates_only_initial_admin(): void
    {
        $this->seed(RolesSeeder::class);
        $this->app->instance('env', 'production');
        config()->set('permigest.initial_admin_password', 'AdminProduccion123');
        $this->runUserSeeder();

        $this->assertDatabaseCount('users', 1);
        $admin = User::firstOrFail();
        $this->assertSame('admin', $admin->rol->nombre);
        $this->assertTrue(Hash::check('AdminProduccion123', $admin->password));
    }

    public function test_non_demo_environment_rejects_weak_initial_admin_password(): void
    {
        $this->seed(RolesSeeder::class);
        $this->app->instance('env', 'production');
        config()->set('permigest.initial_admin_password', 'corta1');

        try {
            $this->runUserSeeder();
            $this->fail('El seeder debio rechazar la clave inicial debil.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('al menos 12 caracteres', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_existing_initial_admin_does_not_require_seed_password_again(): void
    {
        $this->seed(RolesSeeder::class);
        $this->app->instance('env', 'production');
        config()->set('permigest.initial_admin_password', 'AdminProduccion123');
        $this->runUserSeeder();

        config()->set('permigest.initial_admin_password');
        $this->runUserSeeder();

        $this->assertDatabaseCount('users', 1);
    }

    private function runUserSeeder(): void
    {
        $this->app->make(UserSeeder::class)->run();
    }
}
