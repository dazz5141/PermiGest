<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolMiddleware
{
    /**
     * Verifica si el usuario autenticado tiene el rol especificado.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        // Si el rol del usuario está dentro de los roles permitidos
        if (in_array(strtolower($usuario->rol?->nombre ?? ''), $roles)) {
            return $next($request);
        }

        // Si no tiene permiso
        abort(403, 'Acceso no autorizado.');
    }
}
