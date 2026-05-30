<?php

namespace App\Http\Controllers;

use App\Models\Resolucion;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function reporteMensual(Request $request)
    {
        $usuario = auth()->user();
        $rol = strtolower($usuario->rol?->nombre ?? '');

        if (!in_array($rol, ['admin', 'encargado_sistema', 'secretaria', 'jefe_directo'], true)) {
            abort(403, 'Acceso no autorizado.');
        }

        $validated = $request->validate([
            'mes' => 'nullable|integer|min:1|max:12',
            'anio' => 'nullable|integer|min:2000|max:2100',
        ]);

        $mes = (int) ($validated['mes'] ?? date('m'));
        $anio = (int) ($validated['anio'] ?? date('Y'));

        $resoluciones = Resolucion::with([
                'solicitud.usuario',
                'solicitud.tipo',
                'solicitud.estado',
            ])
            ->whereHas('solicitud', function ($q) use ($mes, $anio, $rol, $usuario) {
                $q->whereMonth('fecha_desde', $mes)
                    ->whereYear('fecha_desde', $anio);

                if ($rol === 'jefe_directo') {
                    $subordinadosIds = $usuario->subordinados()->pluck('id')->all();

                    $q->where(function ($scope) use ($usuario, $subordinadosIds) {
                        $scope->where('validador_id', $usuario->id);

                        if (!empty($subordinadosIds)) {
                            $scope->orWhereIn('user_id', $subordinadosIds);
                        }
                    });
                }
            })
            ->orderByDesc('id')
            ->get();

        $nombreMes = ucfirst(Carbon::createFromDate($anio, $mes)->locale('es')->monthName);

        $pdf = Pdf::loadView('reportes.reporte_mensual', [
            'resoluciones' => $resoluciones,
            'nombreMes' => $nombreMes,
            'anio' => $anio,
            'mes' => $mes,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("reporte_permisos_{$anio}_{$mes}.pdf");
    }
}
