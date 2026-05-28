<?php

namespace App\Http\Controllers;

use App\Models\EstadoSolicitud;
use App\Models\PeriodoAdministrativo;
use App\Models\Solicitud;
use Illuminate\Support\Facades\Auth;

class ResumenPermisosController extends Controller
{
    private const ESTADO_APROBADO = EstadoSolicitud::CODIGO_APROBADO;
    private const TIPO_CON_GOCE = 1;
    private const TIPO_SIN_GOCE = 2;
    private const TIPO_DEFUNCION = 3;
    private const TIPO_VARIOS = 4;
    private const TOTAL_DIAS_ANUALES_CON_GOCE = 6.0;

    public function index()
    {
        $usuario = Auth::user();
        $userId = (int) $usuario->id;
        $periodoIdSeleccionado = request('periodo_id');

        $periodoActivo = $periodoIdSeleccionado
            ? PeriodoAdministrativo::find($periodoIdSeleccionado)
            : PeriodoAdministrativo::activo();

        if (!$periodoActivo) {
            abort(500, 'No existe un periodo administrativo activo.');
        }

        $solicitudes = Solicitud::with(['tipo', 'parentesco', 'restauraciones'])
            ->where('user_id', $userId)
            ->whereHas('estado', fn ($q) => $q->where('codigo', self::ESTADO_APROBADO))
            ->where('periodo_id', $periodoActivo->id)
            ->orderBy('fecha_desde')
            ->get();

        $conGoce = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === self::TIPO_CON_GOCE);
        $sinGoce = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === self::TIPO_SIN_GOCE);
        $defuncion = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === self::TIPO_DEFUNCION);
        $varios = $solicitudes->filter(fn ($s) => (int) $s->tipo_solicitud_id === self::TIPO_VARIOS);

        $totalConGoce = $conGoce->sum(fn ($solicitud) => $solicitud->dias_netos_descontados);
        $totalSinGoce = $sinGoce->sum(fn ($solicitud) => $solicitud->dias_registrados);
        $totalDefuncion = $defuncion->sum(fn ($solicitud) => $solicitud->dias_registrados);
        $totalVarios = $varios->sum(fn ($solicitud) => $solicitud->dias_registrados);
        $saldoConGoceDisponible = max(self::TOTAL_DIAS_ANUALES_CON_GOCE - $totalConGoce, 0);

        $periodos = PeriodoAdministrativo::orderBy('anio', 'desc')->get();

        return view('permisos.resumen', [
            'usuario' => $usuario,
            'conGoce' => $conGoce,
            'sinGoce' => $sinGoce,
            'defuncion' => $defuncion,
            'varios' => $varios,
            'totalConGoce' => $totalConGoce,
            'totalSinGoce' => $totalSinGoce,
            'totalDefuncion' => $totalDefuncion,
            'totalVarios' => $totalVarios,
            'totalAusentismo' => $totalConGoce + $totalSinGoce + $totalDefuncion + $totalVarios,
            'saldoConGoceDisponible' => $saldoConGoceDisponible,
            'totalDiasAnualesConGoce' => self::TOTAL_DIAS_ANUALES_CON_GOCE,
            'periodoActivo' => $periodoActivo,
            'periodos' => $periodos,
        ]);
    }
}
