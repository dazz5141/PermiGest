@extends('layouts.app')

@section('title', 'Panel de Administración - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-gear-wide-connected text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Panel de Administración</h4>
    </div>

    {{-- Bienvenida --}}
    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
        <i class="bi bi-person-gear me-2"></i>
        Bienvenido(a), <strong>{{ $usuario->nombres }} {{ $usuario->apellidos }}</strong>.
        <span class="text-muted">Rol: Administrador del sistema</span>
    </div>

    @include('components.alertas')

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

    {{-- Reporte mensual --}}
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
                            <option value="{{ $m }}" {{ (int)$m === (int)date('m') ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="año" class="form-label fw-semibold">Seleccione año</label>
                    <input type="number" id="año" name="año" value="{{ now()->year }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit"
                            class="btn btn-primary w-100"
                            data-confirm
                            data-confirm-title="¿Generar reporte mensual?"
                            data-confirm-text="Se abrirá el PDF con el resumen de solicitudes del mes seleccionado."
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

    {{-- Tabla global de solicitudes --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clipboard-data text-primary me-2"></i>
                Solicitudes registradas ({{ $totalSolicitudes }})
            </h5>
        </div>

        <div class="card-body">
            @if($totalSolicitudes == 0)
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
                            @foreach(\App\Models\Solicitud::with(['usuario','tipo','estado'])->orderByDesc('created_at')->get() as $solicitud)
                                <tr>
                                    <td>{{ $solicitud->usuario->nombres }} {{ $solicitud->usuario->apellidos }}</td>
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
                                        <a href="{{ route('solicitudes.pdf', $solicitud->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary me-1">
                                            <i class="bi bi-printer"></i> Imprimir
                                        </a>
                                        <span class="text-muted small">La resolución final corresponde a Dirección.</span>
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
