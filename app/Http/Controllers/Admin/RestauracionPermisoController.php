<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditoriaHelper;
use App\Http\Controllers\Controller;
use App\Models\EstadoSolicitud;
use App\Models\RestauracionPermiso;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestauracionPermisoController extends Controller
{
    private const TIPO_CON_GOCE = TipoSolicitud::CODIGO_CON_GOCE;
    private const ESTADO_APROBADO = EstadoSolicitud::CODIGO_APROBADO;

    public function index()
    {
        $solicitudes = Solicitud::with(['usuario', 'tipo', 'validador'])
            ->withSum('restauraciones', 'dias_restaurados')
            ->whereHas('tipo', fn ($q) => $q->where('codigo', self::TIPO_CON_GOCE))
            ->whereHas('estado', fn ($q) => $q->where('codigo', self::ESTADO_APROBADO))
            ->orderByDesc('fecha_revision')
            ->orderByDesc('created_at')
            ->get();

        $restauraciones = RestauracionPermiso::with(['solicitud.usuario', 'usuario'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.restauraciones.index', compact('solicitudes', 'restauraciones'));
    }

    public function store(Request $request)
    {
        $usuarioActualId = (int) Auth::user()->id;

        $validated = $request->validate([
            'solicitud_id' => 'required|exists:solicitudes,id',
            'tipo' => 'required|in:total,parcial',
            'dias_restaurados' => 'nullable|numeric|min:0.5',
            'motivo' => 'required|string|max:255',
            'observacion' => 'required|string|max:1000',
            'documento_referencia' => 'nullable|string|max:255',
        ]);

        $solicitud = Solicitud::withSum('restauraciones', 'dias_restaurados')
            ->where('id', $validated['solicitud_id'])
            ->whereHas('tipo', fn ($q) => $q->where('codigo', self::TIPO_CON_GOCE))
            ->whereHas('estado', fn ($q) => $q->where('codigo', self::ESTADO_APROBADO))
            ->firstOrFail();

        $diasYaRestaurados = (float) ($solicitud->restauraciones_sum_dias_restaurados ?? 0);
        $saldoRestaurable = max((float) $solicitud->dias_solicitados - $diasYaRestaurados, 0);

        if ($saldoRestaurable <= 0) {
            return back()->withErrors([
                'solicitud_id' => 'La solicitud seleccionada ya no tiene dias disponibles para restaurar.',
            ]);
        }

        $diasRestaurados = $validated['tipo'] === 'total'
            ? $saldoRestaurable
            : (float) ($validated['dias_restaurados'] ?? 0);

        if ($validated['tipo'] === 'parcial' && $diasRestaurados <= 0) {
            return back()->withErrors([
                'dias_restaurados' => 'Debes indicar la cantidad de dias a restaurar.',
            ])->withInput();
        }

        if ($diasRestaurados > $saldoRestaurable) {
            return back()->withErrors([
                'dias_restaurados' => 'No puedes restaurar mas dias que los efectivamente descontados.',
            ])->withInput();
        }

        $restauracion = RestauracionPermiso::create([
            'solicitud_id' => $solicitud->id,
            'user_id' => $usuarioActualId,
            'tipo' => $validated['tipo'],
            'dias_restaurados' => $diasRestaurados,
            'motivo' => $validated['motivo'],
            'observacion' => $validated['observacion'],
            'documento_referencia' => $validated['documento_referencia'] ?? null,
        ]);

        AuditoriaHelper::registrar(
            'restauraciones_permiso',
            $restauracion->id,
            'solicitud_restaurada',
            $usuarioActualId,
            null,
            $restauracion->toArray()
        );

        return redirect()->route('admin.restauraciones.index')
            ->with('success', 'Restauracion registrada correctamente.');
    }
}
