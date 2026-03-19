<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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

        $usuario = User::where('correo_institucional', $request->correo_institucional)->first();

        if ($usuario && Hash::check($request->password, $usuario->password) && !$usuario->activo) {
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
            $request->session()->regenerate();

            return redirect()
                ->intended(route('dashboard'))
                ->with('success', 'Has iniciado sesion correctamente.');
        }

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
}
