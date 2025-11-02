@extends('layouts.app')

@section('title', 'Panel de Jefatura - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-briefcase text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Panel de Jefatura</h4>
    </div>

    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
        <i class="bi bi-person-workspace me-2"></i>
        Bienvenido(a), <strong>{{ $usuario->nombres }} {{ $usuario->apellidos }}</strong>.
        <span class="text-muted">Cargo: {{ $usuario->cargo ?? 'Jefatura Directa' }}</span>
    </div>

    {{-- Tabla de solicitudes pendientes --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-hourglass-split text-primary me-2"></i>
                Solicitudes pendientes de revisión
            </h5>
        </div>
        <div class="card-body">
            @if($pendientes->isEmpty())
                <p class="text-muted mb-0">No hay solicitudes pendientes por revisar.</p>
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
                            @foreach($pendientes as $solicitud)
                                <tr>
                                    <td>{{ $solicitud->usuario->nombres }} {{ $solicitud->usuario->apellidos }}</td>
                                    <td>{{ $solicitud->tipo->nombre }}</td>
                                    <td>{{ $solicitud->fecha_desde?->format('d/m/Y') }}</td>
                                    <td>{{ $solicitud->fecha_hasta?->format('d/m/Y') }}</td>
                                    <td>{{ $solicitud->dias_solicitados }}</td>
                                    <td><span class="badge bg-warning text-dark">{{ $solicitud->estado->nombre }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
