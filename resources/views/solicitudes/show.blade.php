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
            <div class="row mb-3 align-items-center">
                <div class="col-md-6">
                    <h6 class="text-muted mb-1">Tipo de solicitud</h6>
                    <p class="fw-semibold">{{ $solicitud->tipo?->nombre ?? '-' }}</p>
                </div>

                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Estado</h6>
                    @php
                        $estado = strtolower($solicitud->estado?->nombre ?? '');
                        $badgeClass = match($estado) {
                            'aprobado' => 'bg-success',
                            'pendiente' => 'bg-warning text-dark',
                            'rechazado' => 'bg-danger',
                            default => 'bg-light text-dark',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">
                        {{ $solicitud->estado?->nombre ?? '-' }}
                    </span>
                </div>

                <div class="col-md-3 text-end">
                    @php
                        $rol = strtolower(auth()->user()->rol?->nombre ?? '');
                        $rutaVolver = match($rol) {
                            'funcionario' => route('solicitudes.index'),
                            'jefe_directo' => route('resoluciones.index'),
                            'encargado_sistema' => route('dashboard'),
                            'secretaria' => route('reportes.mensuales'),
                            'admin' => route('dashboard'),
                            default => route('dashboard'),
                        };
                    @endphp

                    <a href="{{ $rutaVolver }}" class="btn btn-outline-primary btn-sm me-2">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>

                    @if(in_array($rol, ['admin', 'encargado_sistema', 'secretaria', 'jefe_directo']))
                        <a href="{{ route('solicitudes.pdf', $solicitud->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-printer"></i> Imprimir ficha
                        </a>
                    @endif
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="text-muted mb-1">Motivo</h6>
                    <p>{{ $solicitud->motivo ?? $solicitud->observaciones ?? '-' }}</p>
                </div>

                <div class="col-md-3 mb-3">
                    <h6 class="text-muted mb-1">Desde</h6>
                    <p>{{ $solicitud->fecha_desde?->format('d/m/Y') ?? '-' }}</p>
                </div>

                <div class="col-md-3 mb-3">
                    <h6 class="text-muted mb-1">Hasta</h6>
                    <p>{{ $solicitud->fecha_hasta?->format('d/m/Y') ?? '-' }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Dias solicitados</h6>
                    <p>{{ $solicitud->dias_solicitados ?? '-' }}</p>
                </div>

                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Jornada</h6>
                    <p>
                        @if(!empty($solicitud->jornada))
                            {{ $solicitud->jornada === 'manana' ? 'Manana' : ucfirst($solicitud->jornada) }}
                        @else
                            {{ (!empty($solicitud->hora_desde) || !empty($solicitud->hora_hasta)) ? 'Media jornada' : 'Completa' }}
                        @endif
                    </p>
                </div>

                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Dias restaurados</h6>
                    <p>{{ $solicitud->dias_restaurados }}</p>
                </div>

                <div class="col-md-3">
                    <h6 class="text-muted mb-1">Dias netos descontados</h6>
                    <p>{{ $solicitud->dias_netos_descontados }}</p>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <h6 class="text-muted mb-1">Comentario de resolucion</h6>
                    <p>{{ $solicitud->ultimaResolucion?->comentario ?? '-' }}</p>
                </div>

                <div class="col-md-6">
                    <h6 class="text-muted mb-1">Historial de restauraciones</h6>
                    @if($solicitud->restauraciones->isEmpty())
                        <p class="mb-0 text-muted">No hay restauraciones registradas.</p>
                    @else
                        <ul class="mb-0 ps-3">
                            @foreach($solicitud->restauraciones as $restauracion)
                                <li>
                                    {{ $restauracion->created_at?->format('d/m/Y') }}:
                                    {{ ucfirst($restauracion->tipo) }} de {{ $restauracion->dias_restaurados }} dias
                                    por {{ $restauracion->motivo }}.
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
