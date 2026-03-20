@extends('layouts.app')

@section('title', 'Resumen de Permisos Administrativos')

@section('content')
<div class="container-fluid py-4 resumen-permisos-page">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-start">
            <div class="summary-icon-wrap me-3">
                <i class="bi bi-clipboard-data"></i>
            </div>

            <div>
                <div class="summary-kicker">{{ config('app.name', 'PermiGest Escolar') }}</div>
                <h4 class="fw-bold mb-1">Cartola Anual de Permisos Administrativos</h4>
                <p class="text-muted mb-2">
                    Información consolidada del período administrativo
                    @isset($periodoActivo)
                        <strong>{{ $periodoActivo->anio }}</strong>
                    @endisset
                </p>

                @isset($periodos)
                    <form method="GET" class="d-print-none">
                        <div class="input-group input-group-sm summary-period-selector">
                            <span class="input-group-text">
                                <i class="bi bi-calendar3"></i>
                            </span>
                            <select name="periodo_id" class="form-select" onchange="this.form.submit()">
                                @foreach($periodos as $periodo)
                                    <option value="{{ $periodo->id }}" @selected($periodoActivo && $periodo->id == $periodoActivo->id)>
                                        Año {{ $periodo->anio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                @endisset
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

    <div class="card shadow-sm border-0 rounded-4 mb-4 summary-employee-card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <small class="text-muted d-block">Funcionario</small>
                    <h5 class="fw-bold mb-1">{{ $usuario->nombre_completo }}</h5>
                    <p class="text-muted mb-0">{{ $usuario->cargo ?? 'Sin cargo registrado' }}</p>
                </div>
                <div class="summary-period-pill">
                    Período {{ $periodoActivo->anio }}
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="summary-employee-field">
                        <small>RUN</small>
                        <strong>{{ $usuario->run ?? '-' }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-employee-field">
                        <small>Correo institucional</small>
                        <strong>{{ $usuario->correo_institucional ?? '-' }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-employee-field">
                        <small>Dirección</small>
                        <strong>{{ $usuario->departamento ?? 'No informado' }}</strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-employee-field">
                        <small>Director asignado</small>
                        <strong>{{ $usuario->jefeDirecto?->nombre_completo ?? 'Sin director asignado' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 summary-executive-row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100 summary-highlight-card">
                <div class="card-body">
                    <small class="text-muted d-block mb-2">Con goce de remuneraciones</small>
                    <div class="fs-2 fw-bold text-primary">{{ number_format($totalConGoce, 1) }}</div>
                    <p class="text-muted mb-0">Descuento neto acumulado en el año administrativo.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-2">Saldo anual disponible</small>
                    <div class="fs-2 fw-bold text-dark">{{ number_format($saldoConGoceDisponible, 1) }}</div>
                    <p class="text-muted mb-0">De un máximo anual de {{ number_format($totalDiasAnualesConGoce, 1) }} días con goce.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-2">Ausentismo total</small>
                    <div class="fs-2 fw-bold text-danger">{{ number_format($totalAusentismo, 1) }}</div>
                    <p class="text-muted mb-0">Suma total de días aprobados en el período administrativo.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4 summary-section-card">
        <div class="card-header summary-card-header bg-primary-subtle border-0">
            <div>
                <h5 class="fw-bold mb-1 text-primary-emphasis">Permisos con goce de remuneraciones</h5>
                <small class="text-muted">Incluye aprobados, restaurados y descuento neto real.</small>
            </div>
            <span class="summary-badge">{{ number_format($totalConGoce, 1) }} días netos</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table summary-table mb-0">
                    <thead>
                        <tr>
                            <th>F. inicio</th>
                            <th>F. término</th>
                            <th>Días aprobados</th>
                            <th>Días restaurados</th>
                            <th>Descuento neto</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conGoce as $p)
                            <tr>
                                <td>{{ $p->fecha_desde->format('d/m/Y') }}</td>
                                <td>{{ $p->fecha_hasta->format('d/m/Y') }}</td>
                                <td>{{ number_format($p->dias_solicitados, 1) }}</td>
                                <td>{{ number_format($p->dias_restaurados, 1) }}</td>
                                <td>
                                    <span class="fw-semibold text-primary">{{ number_format($p->dias_netos_descontados, 1) }}</span>
                                </td>
                                <td>{{ $p->motivo ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No registra permisos con goce en el período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4 summary-section-card">
        <div class="card-header summary-card-header bg-secondary-subtle border-0">
            <div>
                <h5 class="fw-bold mb-1 text-secondary-emphasis">Permisos sin goce de remuneraciones</h5>
                <small class="text-muted">Solicitudes aprobadas sin remuneración.</small>
            </div>
            <span class="summary-badge">{{ number_format($totalSinGoce, 1) }} días</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table summary-table mb-0">
                    <thead>
                        <tr>
                            <th>F. inicio</th>
                            <th>F. término</th>
                            <th>Días</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sinGoce as $p)
                            <tr>
                                <td>{{ $p->fecha_desde->format('d/m/Y') }}</td>
                                <td>{{ $p->fecha_hasta->format('d/m/Y') }}</td>
                                <td>{{ number_format($p->dias_solicitados, 1) }}</td>
                                <td>{{ $p->motivo ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No registra permisos sin goce en el período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 summary-section-card h-100">
                <div class="card-header summary-card-header bg-dark-subtle border-0">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark-emphasis">Permisos por defunción</h5>
                        <small class="text-muted">Solicitudes aprobadas por fallecimiento.</small>
                    </div>
                    <span class="summary-badge">{{ number_format($totalDefuncion, 1) }} días</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table summary-table mb-0">
                            <thead>
                                <tr>
                                    <th>F. inicio</th>
                                    <th>F. término</th>
                                    <th>Días</th>
                                    <th>Parentesco</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($defuncion as $p)
                                    <tr>
                                        <td>{{ $p->fecha_desde->format('d/m/Y') }}</td>
                                        <td>{{ $p->fecha_hasta->format('d/m/Y') }}</td>
                                        <td>{{ number_format($p->dias_solicitados, 1) }}</td>
                                        <td>{{ $p->parentesco?->nombre ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No registra permisos por defunción.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 summary-section-card h-100">
                <div class="card-header summary-card-header bg-info-subtle border-0">
                    <div>
                        <h5 class="fw-bold mb-1 text-info-emphasis">Permisos administrativos varios</h5>
                        <small class="text-muted">Solicitudes aprobadas en otras categorías.</small>
                    </div>
                    <span class="summary-badge">{{ number_format($totalVarios, 1) }} días</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table summary-table mb-0">
                            <thead>
                                <tr>
                                    <th>F. inicio</th>
                                    <th>F. término</th>
                                    <th>Días</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($varios as $p)
                                    <tr>
                                        <td>{{ $p->fecha_desde->format('d/m/Y') }}</td>
                                        <td>{{ $p->fecha_hasta->format('d/m/Y') }}</td>
                                        <td>{{ number_format($p->dias_solicitados, 1) }}</td>
                                        <td>{{ $p->observaciones ?? $p->motivo ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No registra permisos varios.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 summary-total-card">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <small class="text-muted d-block">Cierre del período administrativo</small>
                <h5 class="mb-0 fw-bold">Total de ausentismo registrado</h5>
            </div>
            <div class="summary-total-value">
                {{ number_format($totalAusentismo, 1) }} días
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.summary-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0d6efd, #62a2ff);
    color: #fff;
    box-shadow: 0 12px 24px rgba(13, 110, 253, 0.18);
}

.summary-icon-wrap i {
    font-size: 1.4rem;
}

.summary-kicker {
    margin-bottom: 0.2rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
}

.summary-period-selector {
    min-width: 180px;
}

.summary-employee-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(244, 248, 255, 0.98));
}

.summary-period-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.55rem 0.95rem;
    border-radius: 999px;
    background: rgba(13, 110, 253, 0.10);
    color: #0d5bd6;
    font-weight: 700;
}

.summary-employee-field {
    height: 100%;
    padding: 0.9rem 1rem;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid #e8eef6;
}

.summary-employee-field small {
    display: block;
    margin-bottom: 0.35rem;
    color: #64748b;
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
}

.summary-employee-field strong {
    color: #1e293b;
    font-size: 0.95rem;
}

.summary-highlight-card {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(98, 162, 255, 0.03));
}

.summary-section-card {
    overflow: hidden;
}

.summary-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
}

.summary-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.7);
    color: #334155;
    font-size: 0.85rem;
    font-weight: 700;
}

.summary-table thead th {
    border-bottom: 1px solid #dbe4f0;
    color: #334155;
    font-size: 0.84rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 0.95rem 1rem;
    background: #f8fafc;
}

.summary-table tbody td {
    padding: 0.95rem 1rem;
    border-color: #edf2f7;
    vertical-align: middle;
}

.summary-total-card {
    background: linear-gradient(135deg, #ffffff, #f8fbff);
}

.summary-total-value {
    font-size: 1.8rem;
    font-weight: 800;
    color: #0d6efd;
}

@media (max-width: 767.98px) {
    .summary-card-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .summary-total-value {
        font-size: 1.45rem;
    }
}

@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 12mm;
    }

    nav,
    aside,
    footer,
    .sidebar,
    .navbar,
    .wrapper > *:not(.main-container),
    .main-container > aside {
        display: none !important;
    }

    html,
    body {
        background: #fff !important;
        font-size: 11px !important;
        line-height: 1.25 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .main-container,
    .resumen-permisos-page,
    .container-fluid,
    .container {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    main.content-area {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .row {
        --bs-gutter-x: 0.6rem;
        --bs-gutter-y: 0.6rem;
    }

    .py-4 {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .mb-4 {
        margin-bottom: 0.6rem !important;
    }

    .d-print-none {
        display: none !important;
    }

    .summary-employee-card,
    .summary-highlight-card,
    .summary-executive-row {
        display: none !important;
    }

    .card,
    .summary-section-card,
    .summary-total-card,
    .summary-employee-card,
    .summary-highlight-card {
        box-shadow: none !important;
        border: 1px solid #dbe4f0 !important;
        border-radius: 12px !important;
        break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 0.6rem !important;
    }

    .card-body,
    .card-header,
    .summary-card-header {
        padding: 0.7rem 0.85rem !important;
    }

    .summary-card-header {
        background: #f8fafc !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.5rem !important;
    }

    .summary-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        box-shadow: none !important;
    }

    .summary-icon-wrap i {
        font-size: 1rem;
    }

    .summary-kicker {
        font-size: 0.65rem !important;
        margin-bottom: 0.1rem !important;
    }

    h4 {
        font-size: 18px !important;
        margin-bottom: 0.15rem !important;
    }

    h5 {
        font-size: 14px !important;
        margin-bottom: 0.15rem !important;
    }

    p,
    small,
    .text-muted,
    .summary-employee-field strong {
        font-size: 11px !important;
    }

    .fs-2,
    .summary-total-value {
        font-size: 18px !important;
    }

    .summary-badge,
    .summary-period-pill {
        padding: 0.3rem 0.55rem !important;
        font-size: 0.72rem !important;
    }

    .summary-employee-field {
        padding: 0.55rem 0.65rem !important;
        border-radius: 10px !important;
        background: #fafcff !important;
    }

    .summary-employee-field small {
        font-size: 0.62rem !important;
        margin-bottom: 0.15rem !important;
    }

    .summary-table thead th {
        padding: 0.45rem 0.55rem !important;
        font-size: 0.68rem !important;
        letter-spacing: 0.02em !important;
    }

    .summary-table tbody td {
        padding: 0.45rem 0.55rem !important;
        font-size: 0.75rem !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    .summary-section-card .card-body,
    .summary-section-card .table-responsive,
    .summary-total-card .card-body {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>
@endsection
