<?php

namespace App\Http\Controllers;

use App\Models\PeriodoAdministrativo;
use App\Models\Solicitud;
use Illuminate\Support\Facades\Auth;

class ResumenPermisosController extends Controller
{
    private const ESTADO_APROBADO = 'Aprobado';
    private const TIPO_CON_GOCE = 1;
    private const TIPO_SIN_GOCE = 2;
    private const TIPO_DEFUNCION = 3;
    private const TIPO_VARIOS = 4;

    public function index()
    {
        $userId = Auth::id();
        $periodoIdSeleccionado = request('periodo_id');

        $periodoActivo = $periodoIdSeleccionado
            ? PeriodoAdministrativo::find($periodoIdSeleccionado)
            : PeriodoAdministrativo::activo();

        if (!$periodoActivo) {
            abort(500, 'No existe un periodo administrativo activo.');
        }

        $solicitudes = Solicitud::with(['tipo', 'parentesco'])
            ->where('user_id', $userId)
            ->whereHas('estado', fn ($q) => $q->where('nombre', self::ESTADO_APROBADO))
            ->where('periodo_id', $periodoActivo->id)
            ->orderBy('fecha_desde')
            ->get();

        $conGoce = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === self::TIPO_CON_GOCE);
        $sinGoce = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === self::TIPO_SIN_GOCE);
        $defuncion = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === self::TIPO_DEFUNCION);
        $varios = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === self::TIPO_VARIOS);

        $totalConGoce = $conGoce->sum('dias_solicitados');
        $totalSinGoce = $sinGoce->sum('dias_solicitados');
        $totalDefuncion = $defuncion->sum('dias_solicitados');
        $totalVarios = $varios->sum('dias_solicitados');

        $periodos = PeriodoAdministrativo::orderBy('anio', 'desc')->get();

        return view('permisos.resumen', [
            'conGoce' => $conGoce,
            'sinGoce' => $sinGoce,
            'defuncion' => $defuncion,
            'varios' => $varios,
            'totalConGoce' => $totalConGoce,
            'totalSinGoce' => $totalSinGoce,
            'totalDefuncion' => $totalDefuncion,
            'totalVarios' => $totalVarios,
            'totalAusentismo' => $totalConGoce + $totalSinGoce + $totalDefuncion + $totalVarios,
            'periodoActivo' => $periodoActivo,
            'periodos' => $periodos,
        ]);
    }
}
