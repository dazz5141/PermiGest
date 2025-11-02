<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Solicitud;
use App\Models\Rol;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        // Según el rol, mostramos un dashboard diferente
        switch ($usuario->rol?->nombre ?? '') {

            // 🔹 ADMINISTRADOR
            case 'admin':
                $totalUsuarios     = User::count();
                $totalSolicitudes  = Solicitud::count();
                $aprobadas         = Solicitud::whereHas('estado', fn($q) => $q->where('nombre', 'Aprobada'))->count();
                $rechazadas        = Solicitud::whereHas('estado', fn($q) => $q->where('nombre', 'Rechazada'))->count();
                $pendientes        = Solicitud::whereHas('estado', fn($q) => $q->where('nombre', 'En revisión'))->count();

                return view('dashboard.admin', compact(
                    'usuario',
                    'totalUsuarios',
                    'totalSolicitudes',
                    'aprobadas',
                    'rechazadas',
                    'pendientes'
                ));

            // 🔹 SECRETARÍA
            case 'secretaria':
                // Secretaria ve resumen global y genera reportes mensuales
                $totalSolicitudes = Solicitud::count();
                $pendientes = Solicitud::whereHas('estado', fn($q) => $q->where('nombre', 'En revisión'))->count();
                $aprobadas = Solicitud::whereHas('estado', fn($q) => $q->where('nombre', 'Aprobada'))->count();

                return view('dashboard.secretaria', compact('usuario', 'totalSolicitudes', 'pendientes', 'aprobadas'));

            // 🔹 JEFATURA (Inspector General o similar)
            case 'jefe_directo':
                $pendientes = Solicitud::where('validador_id', $usuario->id)
                    ->whereHas('estado', fn($q) => $q->where('nombre', 'En revisión'))
                    ->with(['usuario', 'tipo', 'estado'])
                    ->get();

                return view('dashboard.jefatura', compact('usuario', 'pendientes'));

            // 🔹 FUNCIONARIO (Docente o Asistente)
            default:
                $total = $usuario->solicitudes()->count();
                $enRevision = $usuario->solicitudes()->whereHas('estado', fn($q) => $q->where('nombre', 'En revisión'))->count();
                $aprobadas = $usuario->solicitudes()->whereHas('estado', fn($q) => $q->where('nombre', 'Aprobada'))->count();
                $rechazadas = $usuario->solicitudes()->whereHas('estado', fn($q) => $q->where('nombre', 'Rechazada'))->count();

                return view('dashboard.funcionario', compact('usuario', 'total', 'enRevision', 'aprobadas', 'rechazadas'));
        }
    }
}
