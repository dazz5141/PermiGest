<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const MAX_ACCOUNT_LOGIN_ATTEMPTS = 20;

    private const MAX_IP_LOGIN_ATTEMPTS = 50;

    private const LOGIN_DECAY_SECONDS = 60;

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'correo_institucional' => 'required|email',
            'password' => 'required',
        ]);

        $loginLimiters = $this->loginLimiters($request);
        $seconds = $this->retryAfter($loginLimiters);

        if ($seconds > 0) {
            return back()
                ->with('error', "Demasiados intentos de inicio de sesion. Intenta nuevamente en {$seconds} segundos.")
                ->withInput(['correo_institucional']);
        }

        $usuario = User::where('correo_institucional', $request->correo_institucional)->first();

        if ($usuario && Hash::check($request->password, $usuario->password) && ! $usuario->activo) {
            $this->hitLoginLimiters($loginLimiters);

            return back()
                ->with('error', 'Tu usuario esta deshabilitado. Debes solicitar reactivacion al encargado del sistema.')
                ->withInput(['correo_institucional']);
        }

        $credentials = [
            'correo_institucional' => $request->correo_institucional,
            'password' => $request->password,
            'activo' => true,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->clearLoginLimiters($loginLimiters);
            $request->session()->regenerate();

            return redirect()
                ->intended(route('dashboard'))
                ->with('success', 'Has iniciado sesion correctamente.');
        }

        $this->hitLoginLimiters($loginLimiters);

        return back()
            ->with('error', 'Las credenciales no coinciden con nuestros registros.')
            ->withInput(['correo_institucional']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Sesion cerrada correctamente.');
    }

    /**
     * @return array<string, int>
     */
    private function loginLimiters(Request $request): array
    {
        $email = Str::lower(trim((string) $request->input('correo_institucional')));
        $ip = (string) $request->ip();

        return [
            'login:email-ip:'.hash('sha256', $email.'|'.$ip) => self::MAX_LOGIN_ATTEMPTS,
            'login:email:'.hash('sha256', $email) => self::MAX_ACCOUNT_LOGIN_ATTEMPTS,
            'login:ip:'.hash('sha256', $ip) => self::MAX_IP_LOGIN_ATTEMPTS,
        ];
    }

    /**
     * @param  array<string, int>  $loginLimiters
     */
    private function retryAfter(array $loginLimiters): int
    {
        $seconds = 0;

        foreach ($loginLimiters as $key => $maxAttempts) {
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $seconds = max($seconds, RateLimiter::availableIn($key));
            }
        }

        return $seconds;
    }

    /**
     * @param  array<string, int>  $loginLimiters
     */
    private function hitLoginLimiters(array $loginLimiters): void
    {
        foreach (array_keys($loginLimiters) as $key) {
            RateLimiter::hit($key, self::LOGIN_DECAY_SECONDS);
        }
    }

    /**
     * @param  array<string, int>  $loginLimiters
     */
    private function clearLoginLimiters(array $loginLimiters): void
    {
        foreach (array_keys($loginLimiters) as $key) {
            if (str_starts_with($key, 'login:ip:')) {
                continue;
            }

            RateLimiter::clear($key);
        }
    }
}
