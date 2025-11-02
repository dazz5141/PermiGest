@extends('layouts.app')

@section('title', 'Panel de Secretaría - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-folder2-open text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Panel de Secretaría</h4>
    </div>

    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
        <i class="bi bi-person-vcard me-2"></i>
        Bienvenida, <strong>{{ $usuario->nombres }} {{ $usuario->apellidos }}</strong>.
        <span class="text-muted">Rol: Secretaría / Administración</span>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-envelope-paper text-primary fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Solicitudes totales</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $totalSolicitudes }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Pendientes de revisión</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $pendientes }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Aprobadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $aprobadas }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Acciones --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-printer-fill text-primary me-2"></i>
                Generar reportes y resumen mensual
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Desde este módulo puede descargar un resumen mensual de todas las solicitudes registradas.</p>

            <form action="#" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="mes" class="form-label fw-semibold">Seleccione mes</label>
                    <select id="mes" name="mes" class="form-select">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="anio" class="form-label fw-semibold">Seleccione año</label>
                    <input type="number" id="anio" name="anio" value="{{ now()->year }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Generar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
