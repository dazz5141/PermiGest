<?php

namespace App\Http\Controllers;

use App\Models\Resolucion;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Genera el reporte mensual de permisos en PDF
     */
    public function reporteMensual(Request $request)
    {
        $usuario = auth()->user();
        $rol = strtolower($usuario->rol?->nombre ?? '');

        // Solo roles permitidos
        if (!in_array($rol, ['administrador', 'secretaria', 'inspector_general', 'jefe_directo'])) {
            abort(403, 'Acceso no autorizado.');
        }

        // Obtener mes/año
        $mes = $request->input('mes', date('m'));
        $año = $request->input('año', date('Y'));

        // Cargar resoluciones del mes
        $resoluciones = Resolucion::with([
                'solicitud.usuario',
                'solicitud.tipo',
                'solicitud.estado',
            ])
            ->whereHas('solicitud', function ($q) use ($mes, $año) {
                $q->whereMonth('fecha_desde', $mes)
                ->whereYear('fecha_desde', $año);
            })
            ->orderByDesc('id')
            ->get();

        // Generar nombre de mes y PDF
        $nombreMes = ucfirst(\Carbon\Carbon::createFromDate($año, $mes)->locale('es')->monthName);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.reporte_mensual', [
            'resoluciones' => $resoluciones,
            'nombreMes' => $nombreMes,
            'año' => $año,
            'mes' => $mes,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("reporte_permisos_{$año}_{$mes}.pdf");
    }
}
