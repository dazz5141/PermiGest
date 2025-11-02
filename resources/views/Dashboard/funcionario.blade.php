@extends('layouts.app')

@section('title', 'Panel del Funcionario - PermiGest Escolar')

@section('content')
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

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-envelope-paper text-primary fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Solicitudes enviadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $total ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">En revisión</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $enRevision ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Aprobadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $aprobadas ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Rechazadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $rechazadas ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
