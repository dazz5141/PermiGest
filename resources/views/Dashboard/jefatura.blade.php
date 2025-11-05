@extends('layouts.app')

@section('title', 'Panel de Jefatura - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-briefcase text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Panel de Jefatura</h4>
    </div>

    {{-- Bienvenida --}}
    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
        <i class="bi bi-person-workspace me-2"></i>
        Bienvenido(a), <strong>{{ $usuario->nombres }} {{ $usuario->apellidos }}</strong>.
        <span class="text-muted">Cargo: {{ $usuario->cargo ?? 'Jefatura Directa' }}</span>
    </div>

    @include('components.alertas')

    {{-- Tarjetas de resumen --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Pendientes de revisión</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">{{ $pendientes->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Aprobadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">
                        {{ \App\Models\Solicitud::whereIn('user_id', $usuario->subordinados()->pluck('id'))->whereHas('estado', fn($q)=>$q->where('nombre','Aprobada'))->count() }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Rechazadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2">
                        {{ \App\Models\Solicitud::whereIn('user_id', $usuario->subordinados()->pluck('id'))->whereHas('estado', fn($q)=>$q->where('nombre','Rechazada'))->count() }}
                    </p>
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
            <p class="text-muted">Desde este módulo puede descargar un resumen mensual de las solicitudes revisadas bajo su supervisión.</p>

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

    {{-- Tabla de solicitudes pendientes --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-clipboard-check text-primary me-2"></i>
                Solicitudes pendientes de revisión ({{ $pendientes->count() }})
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
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            {{ $solicitud->estado->nombre }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('solicitudes.pdf', $solicitud->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary me-1">
                                            <i class="bi bi-printer"></i> Imprimir
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalResolucion"
                                            data-id="{{ $solicitud->id }}"
                                            data-accion="aprobado">
                                            <i class="bi bi-check-circle"></i> Aprobar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalResolucion"
                                            data-id="{{ $solicitud->id }}"
                                            data-accion="rechazado">
                                            <i class="bi bi-x-circle"></i> Rechazar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de resolución --}}
    <div class="modal fade" id="modalResolucion" tabindex="-1" aria-labelledby="modalResolucionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formResolucion" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalResolucionLabel">
                        <i class="bi bi-pencil-square text-primary me-2"></i> Resolver solicitud
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="accion" id="accionInput">
                    <div class="mb-3">
                        <label for="comentario" class="form-label fw-semibold">Comentario (opcional)</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="4"
                            placeholder="Escriba observaciones o fundamentos de su decisión..."></textarea>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Confirme su decisión. Esta acción será registrada.
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Confirmar resolución
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- Script modal --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalResolucion');
    const form = document.getElementById('formResolucion');
    const accionInput = document.getElementById('accionInput');

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const accion = button.getAttribute('data-accion');
        accionInput.value = accion;
        form.action = `/resoluciones/${id}`;
    });
});
</script>
@endsection

@include('components.confirm')
