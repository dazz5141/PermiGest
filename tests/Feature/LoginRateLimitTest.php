<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'docente@colegio.cl';

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearRateLimiters(self::EMAIL, '127.0.0.1');
    }

    public function test_active_user_can_log_in(): void
    {
        $usuario = $this->createUser();

        $response = $this->post(route('login.post'), $this->credentials('ClaveSegura123'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_sixth_failed_login_attempt_is_temporarily_blocked(): void
    {
        $this->createUser();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login.post'), $this->credentials('clave-incorrecta'))
                ->assertSessionHas('error', 'Las credenciales no coinciden con nuestros registros.');
        }

        $response = $this->post(route('login.post'), $this->credentials('clave-incorrecta'));

        $response->assertSessionHas(
            'error',
            fn (string $message): bool => str_starts_with($message, 'Demasiados intentos de inicio de sesion.')
        );
        $this->assertGuest();
    }

    public function test_successful_login_clears_previous_failed_attempts(): void
    {
        $this->createUser();

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $this->post(route('login.post'), $this->credentials('clave-incorrecta'));
        }

        $this->post(route('login.post'), $this->credentials('ClaveSegura123'))
            ->assertRedirect(route('dashboard'));

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login.post'), $this->credentials('clave-incorrecta'))
                ->assertSessionHas('error', 'Las credenciales no coinciden con nuestros registros.');
        }
    }

    public function test_account_is_temporarily_blocked_when_failed_attempts_use_different_ips(): void
    {
        $this->createUser();

        for ($attempt = 1; $attempt <= 20; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => "192.0.2.{$attempt}"])
                ->post(route('login.post'), $this->credentials('clave-incorrecta'))
                ->assertSessionHas('error', 'Las credenciales no coinciden con nuestros registros.');
        }

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.21'])
            ->post(route('login.post'), $this->credentials('clave-incorrecta'));

        $response->assertSessionHas(
            'error',
            fn (string $message): bool => str_starts_with($message, 'Demasiados intentos de inicio de sesion.')
        );
    }

    public function test_ip_is_temporarily_blocked_when_it_attempts_many_accounts(): void
    {
        for ($attempt = 1; $attempt <= 50; $attempt++) {
            $this->post(
                route('login.post'),
                $this->credentials('clave-incorrecta', "usuario{$attempt}@colegio.cl")
            )->assertSessionHas('error', 'Las credenciales no coinciden con nuestros registros.');
        }

        $response = $this->post(
            route('login.post'),
            $this->credentials('clave-incorrecta', 'usuario51@colegio.cl')
        );

        $response->assertSessionHas(
            'error',
            fn (string $message): bool => str_starts_with($message, 'Demasiados intentos de inicio de sesion.')
        );
    }

    public function test_successful_login_does_not_clear_ip_wide_failed_attempts(): void
    {
        $this->createUser();
        $ipLimiterKey = 'login:ip:'.hash('sha256', '127.0.0.1');

        for ($attempt = 1; $attempt <= 49; $attempt++) {
            RateLimiter::hit($ipLimiterKey, 60);
        }

        $this->post(route('login.post'), $this->credentials('ClaveSegura123'))
            ->assertRedirect(route('dashboard'));

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->post(
            route('login.post'),
            $this->credentials('clave-incorrecta', 'otra-cuenta@colegio.cl')
        )->assertSessionHas('error', 'Las credenciales no coinciden con nuestros registros.');

        $response = $this->post(
            route('login.post'),
            $this->credentials('clave-incorrecta', 'cuenta-final@colegio.cl')
        );

        $response->assertSessionHas(
            'error',
            fn (string $message): bool => str_starts_with($message, 'Demasiados intentos de inicio de sesion.')
        );
    }

    private function createUser(): User
    {
        $rol = Rol::create([
            'nombre' => 'funcionario',
            'descripcion' => 'Funcionario',
        ]);

        return User::create([
            'nombres' => 'Ana',
            'apellidos' => 'Perez',
            'run' => '44.444.444-4',
            'correo_institucional' => self::EMAIL,
            'cargo' => 'Docente',
            'rol_id' => $rol->id,
            'password' => 'ClaveSegura123',
            'activo' => true,
        ]);
    }

    /**
     * @return array{correo_institucional: string, password: string}
     */
    private function credentials(string $password, string $email = self::EMAIL): array
    {
        return [
            'correo_institucional' => $email,
            'password' => $password,
        ];
    }

    private function clearRateLimiters(string $email, string $ip): void
    {
        RateLimiter::clear('login:email-ip:'.hash('sha256', $email.'|'.$ip));
        RateLimiter::clear('login:email:'.hash('sha256', $email));
        RateLimiter::clear('login:ip:'.hash('sha256', $ip));
    }
}
