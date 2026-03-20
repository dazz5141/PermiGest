@extends('layouts.app')

@section('title', 'Panel Operativo - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-cpu text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Panel Operativo del Sistema</h4>
    </div>

    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
        <i class="bi bi-person-gear me-2"></i>
        Bienvenido(a), <strong>{{ $usuario->nombres }} {{ $usuario->apellidos }}</strong>.
        <span class="text-muted">Rol: Encargado del sistema</span>
    </div>

    @include('components.alertas')

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill text-primary fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Usuarios registrados</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $totalUsuarios }}</p>
                    <small class="text-muted">{{ $usuariosActivos }} activos</small>
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
                    <i class="bi bi-calendar-event text-warning fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Feriados cargados</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $feriadosRegistrados }}</p>
                    <small class="text-muted">Calendario operativo</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-counterclockwise text-success fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Restauraciones</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $restauracionesRegistradas }}</p>
                    <small class="text-muted">Ajustes registrados</small>
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
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-people-fill me-2"></i> Gestionar usuarios
                        </a>
                        <a href="{{ route('admin.feriados.index') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-calendar-event me-2"></i> Administrar feriados
                        </a>
                        <a href="{{ route('admin.restauraciones.index') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-arrow-counterclockwise me-2"></i> Registrar restauraciones
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
                        <i class="bi bi-bar-chart-line text-primary me-2"></i>
                        Estado general de solicitudes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
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

                    <div class="alert alert-info mt-4 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Este panel está orientado a la operación diaria del sistema: usuarios, feriados, restauraciones y trazabilidad.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i>
                Restauraciones recientes
            </h5>
        </div>
        <div class="card-body">
            @if($restauracionesRecientes->isEmpty())
                <p class="text-muted mb-0">Aún no hay restauraciones registradas en el sistema.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Funcionario</th>
                                <th>Tipo</th>
                                <th>Días</th>
                                <th>Motivo</th>
                                <th>Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($restauracionesRecientes as $restauracion)
                                <tr>
                                    <td>{{ $restauracion->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $restauracion->solicitud->usuario->nombre_completo ?? '-' }}</td>
                                    <td>{{ ucfirst($restauracion->tipo) }}</td>
                                    <td>{{ number_format((float) $restauracion->dias_restaurados, 1) }}</td>
                                    <td>{{ $restauracion->motivo }}</td>
                                    <td>{{ $restauracion->usuario->nombre_completo ?? '-' }}</td>
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
