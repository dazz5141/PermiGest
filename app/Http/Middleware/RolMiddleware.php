<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        if (!$usuario->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tu usuario esta deshabilitado. Debes solicitar reactivacion al encargado del sistema.');
        }

        if (in_array(strtolower($usuario->rol?->nombre ?? ''), $roles, true)) {
            return $next($request);
        }

        abort(403, 'Acceso no autorizado.');
    }
}
