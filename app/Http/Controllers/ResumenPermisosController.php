<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Solicitud;
use App\Models\PeriodoAdministrativo;
use Carbon\Carbon;

class ResumenPermisosController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id;

        // Leer período desde selector (GET)
        $periodoIdSeleccionado = request('periodo_id');
        
        // Obtener período administrativo activo
        $periodoActivo = $periodoIdSeleccionado
            ? PeriodoAdministrativo::find($periodoIdSeleccionado)
            : PeriodoAdministrativo::activo();

        if (!$periodoActivo) {
            abort(500, 'No existe un período administrativo activo.');
        }

        // Solo solicitudes APROBADAS del usuario en el período activo
        $solicitudes = Solicitud::with(['tipo', 'parentesco'])
            ->where('user_id', $userId)
            ->where('estado_solicitud_id', 3) // Aprobadas
            ->where('periodo_id', $periodoActivo->id)
            ->orderBy('fecha_desde')
            ->get();

        // IDs de tipos (Ajustar en caso de cambios en la DB)
        $ID_CON_GOCE     = 1;
        $ID_SIN_GOCE     = 2;
        $ID_DEFUNCION    = 3;
        $ID_VARIOS       = 4;

        // Agrupaciones
        $conGoce   = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === $ID_CON_GOCE);
        $sinGoce   = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === $ID_SIN_GOCE);
        $defuncion = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === $ID_DEFUNCION);
        $varios    = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === $ID_VARIOS);

        // Totales
        $totalConGoce   = $conGoce->sum('dias_solicitados');
        $totalSinGoce   = $sinGoce->sum('dias_solicitados');
        $totalDefuncion = $defuncion->sum('dias_solicitados');
        $totalVarios    = $varios->sum('dias_solicitados');

        $periodos = PeriodoAdministrativo::orderBy('anio', 'desc')->get();

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

            // Período 
            'periodoActivo' => $periodoActivo,
            'periodos'      => $periodos,
        ]);
    }
}
