<?php

namespace App\Http\Controllers;

use App\Models\Resolucion;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function reporteMensual(Request $request)
    {
        $usuario = auth()->user();
        $rol = strtolower($usuario->rol?->nombre ?? '');

        if (!in_array($rol, ['admin', 'encargado_sistema', 'secretaria', 'jefe_directo'])) {
            abort(403, 'Acceso no autorizado.');
        }

        $mes = $request->input('mes', date('m'));
        $anio = $request->input('año', $request->input('anio', date('Y')));

        $resoluciones = Resolucion::with([
                'solicitud.usuario',
                'solicitud.tipo',
                'solicitud.estado',
            ])
            ->whereHas('solicitud', function ($q) use ($mes, $anio) {
                $q->whereMonth('fecha_desde', $mes)
                    ->whereYear('fecha_desde', $anio);
            })
            ->orderByDesc('id')
            ->get();

        $nombreMes = ucfirst(\Carbon\Carbon::createFromDate($anio, $mes)->locale('es')->monthName);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.reporte_mensual', [
            'resoluciones' => $resoluciones,
            'nombreMes' => $nombreMes,
            'año' => $anio,
            'mes' => $mes,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("reporte_permisos_{$anio}_{$mes}.pdf");
    }
}
