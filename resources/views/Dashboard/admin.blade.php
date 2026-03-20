@extends('layouts.app')

@section('title', 'Panel de Administración - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-shield-check text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Panel de Administración</h4>
    </div>

    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
        <i class="bi bi-person-badge me-2"></i>
        Bienvenido(a), <strong>{{ $usuario->nombres }} {{ $usuario->apellidos }}</strong>.
        <span class="text-muted">Rol: Administración general del sistema</span>
    </div>

    @include('components.alertas')

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill text-primary fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Usuarios registrados</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $totalUsuarios }}</p>
                    <small class="text-muted">Base completa del sistema</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-envelope-paper text-info fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Solicitudes totales</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $totalSolicitudes }}</p>
                    <small class="text-muted">{{ $pendientes }} pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Solicitudes aprobadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $aprobadas }}</p>
                    <small class="text-muted">Gestión validada por Dirección</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Solicitudes rechazadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $rechazadas }}</p>
                    <small class="text-muted">Control histórico del flujo</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-grid text-primary me-2"></i>
                        Accesos administrativos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-people-fill me-2"></i> Gestionar usuarios
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-shield-lock-fill me-2"></i> Administrar roles
                        </a>
                        <a href="{{ route('admin.feriados.index') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-calendar-event me-2"></i> Configurar feriados
                        </a>
                        <a href="{{ route('auditoria.index') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-search me-2"></i> Revisar auditoría
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-diagram-3 text-primary me-2"></i>
                        Estado global del sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <small class="text-muted d-block">Pendientes</small>
                                <div class="fs-3 fw-bold text-warning">{{ $pendientes }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <small class="text-muted d-block">Aprobadas</small>
                                <div class="fs-3 fw-bold text-success">{{ $aprobadas }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <small class="text-muted d-block">Rechazadas</small>
                                <div class="fs-3 fw-bold text-danger">{{ $rechazadas }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Este panel concentra la administración completa del sistema: catálogos, roles, usuarios, trazabilidad y monitoreo general.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-printer-fill text-primary me-2"></i>
                Generar reportes y resumen mensual
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Desde este módulo puede descargar un resumen mensual global del sistema.</p>

            <form action="{{ route('reportes.mensual') }}" method="GET" target="_blank" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="mes" class="form-label fw-semibold">Seleccione mes</label>
                    <select id="mes" name="mes" class="form-select">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ (int) $m === (int) date('m') ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="anio" class="form-label fw-semibold">Seleccione año</label>
                    <input type="number" id="anio" name="anio" value="{{ now()->year }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit"
                            class="btn btn-primary w-100"
                            data-confirm
                            data-confirm-title="Generar reporte mensual?"
                            data-confirm-text="Se abrira el PDF con el resumen de solicitudes del mes seleccionado."
                            data-confirm-btn="Generar"
                            data-cancel-btn="Cancelar"
                            data-confirm-icon="info">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Generar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clipboard-data text-primary me-2"></i>
                Solicitudes registradas ({{ $totalSolicitudes }})
            </h5>
        </div>
        <div class="card-body">
            @php
                $solicitudesRecientes = \App\Models\Solicitud::with(['usuario', 'tipo', 'estado'])
                    ->latest()
                    ->take(8)
                    ->get();
            @endphp

            @if($solicitudesRecientes->isEmpty())
                <p class="text-muted mb-0">No hay solicitudes registradas en el sistema.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Funcionario</th>
                                <th>Tipo</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Días</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($solicitudesRecientes as $solicitud)
                                <tr>
                                    <td>{{ $solicitud->usuario->nombre_completo }}</td>
                                    <td>{{ $solicitud->tipo->nombre }}</td>
                                    <td>{{ $solicitud->fecha_desde?->format('d/m/Y') }}</td>
                                    <td>{{ $solicitud->fecha_hasta?->format('d/m/Y') }}</td>
                                    <td>{{ $solicitud->dias_solicitados }}</td>
                                    <td>
                                        @if($solicitud->estado->nombre === 'Aprobado')
                                            <span class="badge bg-success">{{ $solicitud->estado->nombre }}</span>
                                        @elseif($solicitud->estado->nombre === 'Rechazado')
                                            <span class="badge bg-danger">{{ $solicitud->estado->nombre }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ $solicitud->estado->nombre }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('solicitudes.pdf', $solicitud->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-printer"></i> Imprimir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@include('components.confirm')
