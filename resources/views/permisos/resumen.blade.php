@extends('layouts.app')

@section('title', 'Resumen de Permisos Administrativos')

@section('content')
<div class="container-fluid py-4">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-clipboard-data text-primary me-3 fs-3"></i>
            <div>
                <h4 class="fw-bold mb-0">Resumen de Permisos Administrativos</h4>
                <small class="text-muted">Información consolidada – últimos 2 años</small>
            </div>
        </div>

        <div class="d-print-none">
            <a href="{{ route('solicitudes.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>

    {{-- ================= CON GOCE ================= --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white fw-semibold">
            Permisos administrativos con goce de remuneraciones
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="15%">F. Inicio</th>
                        <th width="15%">F. Término</th>
                        <th width="10%">Días</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conGoce as $p)
                        <tr>
                            <td>{{ $p->fecha_desde->format('d/m/Y') }}</td>
                            <td>{{ $p->fecha_hasta->format('d/m/Y') }}</td>
                            <td>{{ number_format($p->dias_solicitados, 1) }}</td>
                            <td>{{ $p->motivo ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                No registra permisos con goce en el período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-end fw-semibold">
            Total días: {{ number_format($totalConGoce, 1) }}
        </div>
    </div>

    {{-- ================= SIN GOCE ================= --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-secondary text-white fw-semibold">
            Permisos administrativos sin goce de remuneraciones
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="15%">F. Inicio</th>
                        <th width="15%">F. Término</th>
                        <th width="10%">Días</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sinGoce as $p)
                        <tr>
                            <td>{{ $p->fecha_desde->format('d/m/Y') }}</td>
                            <td>{{ $p->fecha_hasta->format('d/m/Y') }}</td>
                            <td>{{ number_format($p->dias_solicitados, 1) }}</td>
                            <td>{{ $p->motivo ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                No registra permisos sin goce en el período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-end fw-semibold">
            Total días: {{ number_format($totalSinGoce, 1) }}
        </div>
    </div>

    {{-- ================= DEFUNCIÓN ================= --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white fw-semibold">
            Permisos por defunción
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="15%">F. Inicio</th>
                        <th width="15%">F. Término</th>
                        <th width="10%">Días</th>
                        <th>Parentesco</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($defuncion as $p)
                        <tr>
                            <td>{{ $p->fecha_desde->format('d/m/Y') }}</td>
                            <td>{{ $p->fecha_hasta->format('d/m/Y') }}</td>
                            <td>{{ number_format($p->dias_solicitados, 1) }}</td>
                            <td>{{ $p->parentesco?->nombre ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                No registra permisos por defunción.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-end fw-semibold">
            Total días: {{ number_format($totalDefuncion, 1) }}
        </div>
    </div>

    {{-- ================= VARIOS ================= --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-white fw-semibold">
            Permisos administrativos varios
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="15%">F. Inicio</th>
                        <th width="15%">F. Término</th>
                        <th width="10%">Días</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($varios as $p)
                        <tr>
                            <td>{{ $p->fecha_desde->format('d/m/Y') }}</td>
                            <td>{{ $p->fecha_hasta->format('d/m/Y') }}</td>
                            <td>{{ number_format($p->dias_solicitados, 1) }}</td>
                            <td>{{ $p->observaciones ?? $p->motivo ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                No registra permisos varios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-end fw-semibold">
            Total días: {{ number_format($totalVarios, 1) }}
        </div>
    </div>

    {{-- ================= TOTAL GENERAL ================= --}}
    <div class="card shadow-sm border-0">
        <div class="card-body text-end">
            <h5 class="mb-0">
                Total días de ausentismo (últimos 2 años):
                <strong>{{ number_format($totalAusentismo, 1) }}</strong>
            </h5>
        </div>
    </div>

</div>
@endsection

{{-- IMPRESIÓN LIMPIA --}}
@section('styles')
<style>
@media print {

    /* Ocultar layout */
    nav,
    aside,
    footer,
    .sidebar,
    .navbar,
    .wrapper > *:not(.main-container),
    .main-container > aside {
        display: none !important;
    }

    /* Ajustar área principal */
    .main-container {
        margin: 0 !important;
        padding: 0 !important;
    }

    main.content-area {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    /* Quitar botones */
    .d-print-none {
        display: none !important;
    }

    body {
        background: #fff !important;
    }
}
</style>
@endsection

