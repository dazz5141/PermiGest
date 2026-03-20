@extends('layouts.app')

@section('title', 'Panel de Dirección - PermiGest Escolar')

@section('content')
@php
    $subordinadosIds = $usuario->subordinados()->pluck('id');
    $aprobadasDireccion = \App\Models\Solicitud::whereIn('user_id', $subordinadosIds)
        ->whereHas('estado', fn($q) => $q->where('nombre', 'Aprobado'))
        ->count();
    $rechazadasDireccion = \App\Models\Solicitud::whereIn('user_id', $subordinadosIds)
        ->whereHas('estado', fn($q) => $q->where('nombre', 'Rechazado'))
        ->count();
@endphp

<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-building-check text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Panel de Dirección</h4>
    </div>

    <div class="alert alert-light border-start border-4 border-primary shadow-sm mb-4">
        <i class="bi bi-person-workspace me-2"></i>
        Bienvenido(a), <strong>{{ $usuario->nombres }} {{ $usuario->apellidos }}</strong>.
        <span class="text-muted">Cargo: {{ $usuario->cargo ?? 'Director del establecimiento' }}</span>
    </div>

    @include('components.alertas')

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Pendientes de revisión</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $pendientes->count() }}</p>
                    <small class="text-muted">Solicitudes por resolver</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Aprobadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $aprobadasDireccion }}</p>
                    <small class="text-muted">Autorizadas por Dirección</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger fs-1 mb-2"></i>
                    <h6 class="fw-semibold mb-0">Rechazadas</h6>
                    <p class="text-muted fs-4 fw-bold mt-2 mb-1">{{ $rechazadasDireccion }}</p>
                    <small class="text-muted">Decisiones registradas</small>
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
                        <a href="{{ route('resoluciones.index') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-journal-check me-2"></i> Revisar solicitudes pendientes
                        </a>
                        <a href="{{ route('reportes.mensuales') }}" class="btn btn-outline-primary text-start py-3">
                            <i class="bi bi-bar-chart-line me-2"></i> Ver reportes mensuales
                        </a>
                    </div>

                    <div class="alert alert-info mt-4 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        La resolución formal de permisos administrativos corresponde a Dirección.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-printer-fill text-primary me-2"></i>
                        Generar reportes y resumen mensual
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Desde este módulo puede descargar un resumen mensual de las solicitudes revisadas por Dirección.</p>

                    <form action="{{ route('reportes.mensual') }}" method="GET" target="_blank" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="mes" class="form-label fw-semibold">Seleccione mes</label>
                            <select id="mes" name="mes" class="form-select">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ (int) $m === (int) date('m') ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="anio" class="form-label fw-semibold">Seleccione año</label>
                            <input type="number" id="anio" name="anio" value="{{ now()->year }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <button type="submit"
                                    class="btn btn-primary w-100"
                                    data-confirm
                                    data-confirm-title="Generar reporte mensual?"
                                    data-confirm-text="Se abrira el PDF con el resumen de solicitudes del mes seleccionado."
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
        </div>
    </div>

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
                                    <td>
                                        <div class="fw-semibold">{{ $solicitud->usuario->nombres }} {{ $solicitud->usuario->apellidos }}</div>
                                        <small class="text-muted">{{ $solicitud->usuario->cargo ?? 'Sin cargo registrado' }}</small>
                                    </td>
                                    <td>{{ $solicitud->tipo->nombre }}</td>
                                    <td>{{ $solicitud->fecha_desde?->format('d/m/Y') }}</td>
                                    <td>{{ $solicitud->fecha_hasta?->format('d/m/Y') }}</td>
                                    <td>{{ $solicitud->dias_solicitados }}</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $solicitud->estado->nombre }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2 flex-wrap justify-content-center">
                                            <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="btn btn-sm btn-outline-primary px-2">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="{{ route('solicitudes.pdf', $solicitud->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2">
                                                <i class="bi bi-printer"></i>
                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-success px-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalResolucion"
                                                    data-id="{{ $solicitud->id }}"
                                                    data-accion="aprobado">
                                                <i class="bi bi-check-circle"></i> Aprobar
                                            </button>

                                            <button type="button"
                                                    class="btn btn-sm btn-danger px-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalResolucion"
                                                    data-id="{{ $solicitud->id }}"
                                                    data-accion="rechazado">
                                                <i class="bi bi-x-circle"></i> Rechazar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

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
                        <i class="bi bi-info-circle me-1"></i> Confirme su decisión. Esta acción quedará registrada en el sistema.
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
