@extends('layouts.app')

@section('title', 'Panel del Funcionario - PermiGest Escolar')

@section('content')
@php
    $solicitudesRecientes = $usuario->solicitudes()
        ->with(['tipo', 'estado'])
        ->latest()
        ->take(5)
        ->get();

    $directorAsignado = $usuario->jefeDirecto?->nombre_completo ?? null;
@endphp

<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-person-badge text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Panel del Funcionario</h4>
    </div>

    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
        <i class="bi bi-person-circle me-2"></i>
        Bienvenido(a), <strong>{{ $usuario->nombres }} {{ $usuario->apellidos }}</strong>.
        <span class="text-muted">Cargo: {{ $usuario->cargo ?? 'Sin definir' }}</span>
    </div>

    @include('components.alertas')

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-envelope-paper text-primary fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Solicitudes enviadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $total ?? 0 }}</p>
                    <small class="text-muted">Historial acumulado</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Pendientes</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $enRevision ?? 0 }}</p>
                    <small class="text-muted">Esperando resolución</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Aprobadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $aprobadas ?? 0 }}</p>
                    <small class="text-muted">Permisos autorizados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Rechazadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $rechazadas ?? 0 }}</p>
                    <small class="text-muted">Solicitudes denegadas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-lightning-charge text-primary me-2"></i>
                        Accesos rápidos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('solicitudes.index') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-folder2-open me-2"></i> Ver historial de solicitudes
                        </a>
                        <a href="{{ route('solicitudes.create', ['tipo' => 'con_goce']) }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-plus-circle me-2"></i> Crear nueva solicitud
                        </a>
                        <a href="{{ route('mis-permisos.resumen') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-printer me-2"></i> Ver resumen e imprimir
                        </a>
                    </div>

                    <div class="alert alert-info mt-4 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Director asignado:
                        <strong>{{ $directorAsignado ?? 'Sin director asignado' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-clipboard-pulse text-primary me-2"></i>
                        Estado de tus solicitudes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <small class="text-muted d-block">Pendientes</small>
                                <div class="fs-3 fw-bold text-warning">{{ $enRevision ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <small class="text-muted d-block">Aprobadas</small>
                                <div class="fs-3 fw-bold text-success">{{ $aprobadas ?? 0 }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <small class="text-muted d-block">Rechazadas</small>
                                <div class="fs-3 fw-bold text-danger">{{ $rechazadas ?? 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Puedes solicitar permisos administrativos y hacer seguimiento a su resolución desde este panel.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i>
                Solicitudes recientes
            </h5>
        </div>
        <div class="card-body">
            @if($solicitudesRecientes->isEmpty())
                <p class="text-muted mb-0">Aún no has registrado solicitudes en el sistema.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Días</th>
                                <th>Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($solicitudesRecientes as $solicitud)
                                <tr>
                                    <td>{{ $solicitud->tipo->nombre ?? '-' }}</td>
                                    <td>{{ $solicitud->fecha_desde?->format('d/m/Y') }}</td>
                                    <td>{{ $solicitud->fecha_hasta?->format('d/m/Y') }}</td>
                                    <td>{{ $solicitud->dias_solicitados }}</td>
                                    <td>
                                        <span class="badge
                                            @if($solicitud->estado->nombre === 'Aprobado') bg-success
                                            @elseif($solicitud->estado->nombre === 'Rechazado') bg-danger
                                            @else bg-warning text-dark @endif">
                                            {{ $solicitud->estado->nombre }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
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
