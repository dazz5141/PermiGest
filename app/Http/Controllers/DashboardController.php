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

            // ADMINISTRADOR
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

            // SECRETARÍA
            case 'secretaria':
                // Secretaría: resumen general + listado de solicitudes
                $totalSolicitudes = Solicitud::count();
                $pendientes = Solicitud::whereHas('estado', fn($q) => $q->where('nombre', 'En revisión'))->count();
                $aprobadas = Solicitud::whereHas('estado', fn($q) => $q->where('nombre', 'Aprobada'))->count();

                // Agregamos el listado de todas las solicitudes con relaciones
                $solicitudes = Solicitud::with(['usuario', 'tipo', 'estado'])
                    ->orderByDesc('created_at')
                    ->get();

                return view('dashboard.secretaria', compact(
                    'usuario', 
                    'totalSolicitudes', 
                    'pendientes', 
                    'aprobadas',
                    'solicitudes' 
                ));

            // JEFATURA (Inspector General o similar)
            case 'jefe_directo':
                // 🔹 Obtener los IDs de los subordinados directos del jefe actual
                $subordinadosIds = $usuario->subordinados()->pluck('id')->toArray();

                // Si tiene subordinados, obtener sus solicitudes pendientes
                $pendientes = collect();
                if (!empty($subordinadosIds)) {
                    $pendientes = Solicitud::whereIn('user_id', $subordinadosIds)
                        ->where('estado_solicitud_id', 1) // 1 = Pendiente o En revisión
                        ->with(['usuario', 'tipo', 'estado'])
                        ->orderByDesc('created_at')
                        ->get();
                }

                return view('dashboard.jefatura', compact('usuario', 'pendientes'));

            // FUNCIONARIO (Docente o Asistente)
            default:
                $total = $usuario->solicitudes()->count();
                $enRevision = $usuario->solicitudes()->whereHas('estado', fn($q) => $q->where('nombre', 'En revisión'))->count();
                $aprobadas = $usuario->solicitudes()->whereHas('estado', fn($q) => $q->where('nombre', 'Aprobada'))->count();
                $rechazadas = $usuario->solicitudes()->whereHas('estado', fn($q) => $q->where('nombre', 'Rechazada'))->count();

                return view('dashboard.funcionario', compact('usuario', 'total', 'enRevision', 'aprobadas', 'rechazadas'));
        }
    }
}
