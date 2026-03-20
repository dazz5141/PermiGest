<?php

namespace App\Http\Controllers;

use App\Models\Feriado;
use App\Models\RestauracionPermiso;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private const ESTADO_PENDIENTE = 'Pendiente';
    private const ESTADO_APROBADO = 'Aprobado';
    private const ESTADO_RECHAZADO = 'Rechazado';

    public function index()
    {
        $usuario = Auth::user();

        switch ($usuario->rol?->nombre ?? '') {
            case 'admin':
                $totalUsuarios = User::count();
                $totalSolicitudes = Solicitud::count();
                $aprobadas = Solicitud::whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_APROBADO))->count();
                $rechazadas = Solicitud::whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_RECHAZADO))->count();
                $pendientes = Solicitud::whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_PENDIENTE))->count();

                return view('dashboard.admin', compact(
                    'usuario',
                    'totalUsuarios',
                    'totalSolicitudes',
                    'aprobadas',
                    'rechazadas',
                    'pendientes'
                ));

            case 'encargado_sistema':
                $totalUsuarios = User::count();
                $totalSolicitudes = Solicitud::count();
                $aprobadas = Solicitud::whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_APROBADO))->count();
                $rechazadas = Solicitud::whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_RECHAZADO))->count();
                $pendientes = Solicitud::whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_PENDIENTE))->count();
                $usuariosActivos = User::where('activo', true)->count();
                $feriadosRegistrados = Feriado::count();
                $restauracionesRegistradas = RestauracionPermiso::count();
                $restauracionesRecientes = RestauracionPermiso::with(['solicitud.usuario', 'usuario'])
                    ->latest()
                    ->take(6)
                    ->get();

                return view('dashboard.encargado_sistema', compact(
                    'usuario',
                    'totalUsuarios',
                    'totalSolicitudes',
                    'aprobadas',
                    'rechazadas',
                    'pendientes',
                    'usuariosActivos',
                    'feriadosRegistrados',
                    'restauracionesRegistradas',
                    'restauracionesRecientes'
                ));

            case 'secretaria':
                $totalSolicitudes = Solicitud::count();
                $pendientes = Solicitud::whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_PENDIENTE))->count();
                $aprobadas = Solicitud::whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_APROBADO))->count();
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

            case 'jefe_directo':
                $subordinadosIds = $usuario->subordinados()->pluck('id')->toArray();
                $pendientes = collect();

                if (!empty($subordinadosIds)) {
                    $pendientes = Solicitud::whereIn('user_id', $subordinadosIds)
                        ->whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_PENDIENTE))
                        ->with(['usuario', 'tipo', 'estado'])
                        ->orderByDesc('created_at')
                        ->get();
                }

                return view('dashboard.jefatura', compact('usuario', 'pendientes'));

            default:
                $total = $usuario->solicitudes()->count();
                $enRevision = $usuario->solicitudes()
                    ->whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_PENDIENTE))
                    ->count();
                $aprobadas = $usuario->solicitudes()
                    ->whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_APROBADO))
                    ->count();
                $rechazadas = $usuario->solicitudes()
                    ->whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_RECHAZADO))
                    ->count();

                return view('dashboard.funcionario', compact('usuario', 'total', 'enRevision', 'aprobadas', 'rechazadas'));
        }
    }
}
