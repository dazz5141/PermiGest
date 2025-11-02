@extends('layouts.app')

@section('title', 'Detalle de solicitud - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-file-earmark-text text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Detalle de la Solicitud</h4>
    </div>

    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="text-muted mb-1">Tipo de solicitud</h6>
                    <p class="fw-semibold">Permiso con goce de sueldo</p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Estado</h6>
                    <span class="badge bg-success">Aprobado</span>
                </div>
                <div class="col-md-3 text-end">
                    <a href="#" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-printer"></i> Imprimir
                    </a>
                </div>
            </div>
            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="text-muted mb-1">Motivo</h6>
                    <p>Trámite personal urgente</p>
                </div>
                <div class="col-md-3 mb-3">
                    <h6 class="text-muted mb-1">Desde</h6>
                    <p>2025-10-25</p>
                </div>
                <div class="col-md-3 mb-3">
                    <h6 class="text-muted mb-1">Hasta</h6>
                    <p>2025-10-26</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Días solicitados</h6>
                    <p>1</p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Jornada</h6>
                    <p>Completa</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-1">Observaciones</h6>
                    <p>Ninguna observación registrada.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
