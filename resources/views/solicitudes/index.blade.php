@extends('layouts.app')

@section('title', 'Mis solicitudes - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-folder2-open text-primary me-3 fs-3"></i>
            <h4 class="fw-bold mb-0">Historial de Solicitudes</h4>
        </div>
        <a href="{{ route('solicitudes.create', ['tipo' => 'con_goce']) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva solicitud
        </a>
    </div>

    {{-- Mensaje flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            setTimeout(() => document.querySelector('.alert-success')?.classList.remove('show'), 4000);
        </script>
    @endif

    {{-- Tabla --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            @if($solicitudes->isEmpty())
                <p class="text-muted text-center mb-0">
                    <i class="bi bi-inbox me-1"></i> Aún no tienes solicitudes registradas.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo de Permiso</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Días</th>
                                <th>Estado</th>
                                <th>Revisado por</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($solicitudes as $solicitud)
                                <tr>
                                    <td>{{ $solicitud->tipo->nombre ?? '—' }}</td>
                                    <td>{{ optional($solicitud->fecha_desde)->format('Y-m-d') }}</td>
                                    <td>{{ optional($solicitud->fecha_hasta)->format('Y-m-d') }}</td>
                                    <td>{{ $solicitud->dias_solicitados ?? '—' }}</td>
                                    <td>
                                        <span class="badge
                                            @if($solicitud->estado->nombre === 'Aprobado') bg-success
                                            @elseif($solicitud->estado->nombre === 'En revisión') bg-warning text-dark
                                            @elseif($solicitud->estado->nombre === 'Pendiente') bg-secondary
                                            @elseif($solicitud->estado->nombre === 'Rechazado') bg-danger
                                            @else bg-light text-dark @endif">
                                            {{ $solicitud->estado->nombre }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $solicitud->validador?->nombres ?? '—' }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="btn btn-outline-primary btn-sm">
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
