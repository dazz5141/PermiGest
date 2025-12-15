@extends('layouts.app')

@section('title', 'Resumen de Permisos Administrativos')

@section('content')
<div class="container-fluid py-4">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-clipboard-data text-primary me-3 fs-3 mt-1"></i>

            <div>
                <h4 class="fw-bold mb-1">Resumen de Permisos Administrativos</h4>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <small class="text-muted">
                        Información consolidada – Período administrativo
                        @isset($periodoActivo)
                            <strong>{{ $periodoActivo->anio }}</strong>
                        @endisset
                    </small>

                    {{-- SELECTOR DE PERÍODO --}}
                    @isset($periodos)
                    <form method="GET" class="d-print-none">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-calendar3"></i>
                            </span>
                            <select name="periodo_id"
                                    class="form-select"
                                    onchange="this.form.submit()">
                                @foreach($periodos as $periodo)
                                    <option value="{{ $periodo->id }}"
                                        @selected($periodoActivo && $periodo->id == $periodoActivo->id)>
                                        Año {{ $periodo->anio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    @endisset
                </div>
            </div>
        </div>

        {{-- ACCIONES --}}
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
                Total días de ausentismo (período administrativo):
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

