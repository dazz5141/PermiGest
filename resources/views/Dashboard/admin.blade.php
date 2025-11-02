@extends('layouts.app')

@section('title', 'Panel de Administración - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-gear-wide-connected text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Panel de Administración</h4>
    </div>

    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
        <i class="bi bi-person-gear me-2"></i>
        Bienvenido(a), <strong>{{ $usuario->nombres }} {{ $usuario->apellidos }}</strong>.
        <span class="text-muted">Rol: Administrador del sistema</span>
    </div>

    {{-- Métricas globales --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill text-primary fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Usuarios registrados</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $totalUsuarios }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-envelope-paper text-info fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Total de solicitudes</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $totalSolicitudes }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Solicitudes aprobadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $aprobadas }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Solicitudes rechazadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $rechazadas }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla resumen --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-bar-chart-line text-primary me-2"></i>
                Solicitudes en revisión
            </h5>
        </div>
        <div class="card-body">
            @if($pendientes == 0)
                <p class="text-muted mb-0">No hay solicitudes en revisión actualmente.</p>
            @else
                <p class="mb-0">
                    <span class="fw-semibold text-warning">{{ $pendientes }}</span> solicitudes están en proceso de revisión.
                </p>
            @endif
        </div>
    </div>

</div>
@endsection
