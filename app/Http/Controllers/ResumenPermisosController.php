<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Solicitud;
use Carbon\Carbon;

class ResumenPermisosController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;

        // Últimos 2 años
        $desde = Carbon::now()->subYears(2)->startOfDay();
        $hasta = Carbon::now()->endOfDay();

        // Solo solicitudes APROBADAS del usuario
        $solicitudes = Solicitud::with(['tipo', 'parentesco'])
            ->where('user_id', $userId)
            ->where('estado_solicitud_id', 3) // Aprobadas
            ->whereDate('fecha_desde', '>=', $desde->toDateString())
            ->orderBy('fecha_desde')
            ->get();

        // IDs de tipos (Ajustar en caso de cambios en la DB)
        $ID_CON_GOCE     = 1;
        $ID_SIN_GOCE     = 2;
        $ID_DEFUNCION    = 3;
        $ID_VARIOS       = 4;

        // Agrupaciones
        $conGoce = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === $ID_CON_GOCE);
        $sinGoce = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === $ID_SIN_GOCE);
        $defuncion = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === $ID_DEFUNCION);
        $varios = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === $ID_VARIOS);

        // Totales
        $totalConGoce   = $conGoce->sum('dias_solicitados');
        $totalSinGoce   = $sinGoce->sum('dias_solicitados');
        $totalDefuncion = $defuncion->sum('dias_solicitados');
        $totalVarios    = $varios->sum('dias_solicitados');

        return view('permisos.resumen', [
            // Listados
            'conGoce'    => $conGoce,
            'sinGoce'    => $sinGoce,
            'defuncion'  => $defuncion,
            'varios'     => $varios,

            // Totales
            'totalConGoce'   => $totalConGoce,
            'totalSinGoce'   => $totalSinGoce,
            'totalDefuncion' => $totalDefuncion,
            'totalVarios'    => $totalVarios,

            // Total general
            'totalAusentismo' =>
                $totalConGoce +
                $totalSinGoce +
                $totalDefuncion +
                $totalVarios,
        ]);
    }
}
