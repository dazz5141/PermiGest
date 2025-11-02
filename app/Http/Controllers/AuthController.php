<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        // Si ya está autenticado, redirige al dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Procesar inicio de sesión
     */
    public function login(Request $request)
    {
        $request->validate([
            'correo_institucional' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = [
            'correo_institucional' => $request->correo_institucional,
            'password' => $request->password,
        ];

        // Autenticación con "remember me"
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()
                ->intended(route('dashboard'))
                ->with('success', 'Has iniciado sesión correctamente.');
        }

        // Si falla la autenticación
        return back()->with('error', 'Las credenciales no coinciden con nuestros registros.')
                     ->withInput(['correo_institucional']);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Sesión cerrada correctamente.');
    }
}
