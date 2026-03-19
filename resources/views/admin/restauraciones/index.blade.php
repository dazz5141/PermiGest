@extends('layouts.app')

@section('title', 'Restauraciones - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-arrow-counterclockwise text-primary me-2"></i> Restauraciones de Permisos
        </h4>
    </div>

    @include('components.alertas')

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-2">Permisos aprobados con goce</small>
                    <h3 class="fw-bold mb-1">{{ $solicitudes->count() }}</h3>
                    <p class="text-muted mb-0">Disponibles para restauracion administrativa.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-2">Restauraciones registradas</small>
                    <h3 class="fw-bold mb-1">{{ $restauraciones->count() }}</h3>
                    <p class="text-muted mb-0">Ultimos ajustes aplicados en el sistema.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-2">Criterio de uso</small>
                    <h6 class="fw-semibold mb-2">Licencia medica u otra causal valida</h6>
                    <p class="text-muted mb-0">La restauracion devuelve dias sin borrar la solicitud aprobada.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0">Permisos aprobados disponibles</h5>
                <span class="badge bg-light text-dark">{{ $solicitudes->count() }} registros</span>
            </div>

            @if($solicitudes->isEmpty())
                <p class="text-muted mb-0">No hay permisos aprobados disponibles para restaurar.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Funcionario</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Dias aprobados</th>
                                <th>Dias restaurados</th>
                                <th>Saldo restaurable</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($solicitudes as $solicitud)
                                @php
                                    $diasRestaurados = (float) ($solicitud->restauraciones_sum_dias_restaurados ?? 0);
                                    $saldoRestaurable = max((float) $solicitud->dias_solicitados - $diasRestaurados, 0);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $solicitud->usuario->nombre_completo }}</div>
                                        <small class="text-muted">{{ $solicitud->usuario->cargo ?? 'Sin cargo registrado' }}</small>
                                    </td>
                                    <td>{{ $solicitud->fecha_desde?->format('d/m/Y') }}</td>
                                    <td>{{ $solicitud->fecha_hasta?->format('d/m/Y') }}</td>
                                    <td>{{ number_format((float) $solicitud->dias_solicitados, 1) }}</td>
                                    <td>{{ number_format($diasRestaurados, 1) }}</td>
                                    <td>
                                        <span class="badge {{ $saldoRestaurable > 0 ? 'bg-success' : 'bg-secondary' }}">
                                            {{ number_format($saldoRestaurable, 1) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($saldoRestaurable > 0)
                                            <button type="button"
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalRestauracion"
                                                    data-id="{{ $solicitud->id }}"
                                                    data-funcionario="{{ $solicitud->usuario->nombre_completo }}"
                                                    data-saldo="{{ $saldoRestaurable }}">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Restaurar
                                            </button>
                                        @else
                                            <span class="text-muted small">Sin saldo</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0">Ultimas restauraciones registradas</h5>
                <span class="badge bg-light text-dark">{{ $restauraciones->count() }} registros</span>
            </div>

            @if($restauraciones->isEmpty())
                <p class="text-muted mb-0">Todavia no se han registrado restauraciones.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Funcionario</th>
                                <th>Tipo</th>
                                <th>Dias</th>
                                <th>Motivo</th>
                                <th>Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($restauraciones as $restauracion)
                                <tr>
                                    <td>{{ $restauracion->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $restauracion->solicitud->usuario->nombre_completo ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $restauracion->tipo === 'total' ? 'bg-primary' : 'bg-info text-dark' }}">
                                            {{ ucfirst($restauracion->tipo) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format((float) $restauracion->dias_restaurados, 1) }}</td>
                                    <td>{{ $restauracion->motivo }}</td>
                                    <td>{{ $restauracion->usuario->nombre_completo ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="modalRestauracion" tabindex="-1" aria-labelledby="modalRestauracionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('admin.restauraciones.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalRestauracionLabel">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Registrar restauracion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="solicitud_id" id="restauracionSolicitudId">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Funcionario</label>
                        <input type="text" class="form-control" id="restauracionFuncionario" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Saldo restaurable</label>
                        <input type="text" class="form-control" id="restauracionSaldo" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="restauracionTipo" class="form-label">Tipo de restauracion</label>
                        <select class="form-select" name="tipo" id="restauracionTipo" required>
                            <option value="total">Total</option>
                            <option value="parcial">Parcial</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="restauracionDias" class="form-label">Dias a restaurar</label>
                        <input type="number" step="0.5" min="0.5" class="form-control" name="dias_restaurados" id="restauracionDias">
                        <small class="text-muted">En restauracion total se usa automaticamente el saldo completo.</small>
                    </div>
                    <div class="col-md-6">
                        <label for="motivo" class="form-label">Motivo</label>
                        <input type="text" class="form-control" name="motivo" id="motivo" required>
                    </div>
                    <div class="col-md-6">
                        <label for="documento_referencia" class="form-label">Documento de referencia</label>
                        <input type="text" class="form-control" name="documento_referencia" id="documento_referencia" placeholder="Ej: Licencia medica N. 12345">
                    </div>
                    <div class="col-12">
                        <label for="observacion" class="form-label">Observacion</label>
                        <textarea class="form-control" name="observacion" id="observacion" rows="4" required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalRestauracion');
    const solicitudIdInput = document.getElementById('restauracionSolicitudId');
    const funcionarioInput = document.getElementById('restauracionFuncionario');
    const saldoInput = document.getElementById('restauracionSaldo');
    const tipoInput = document.getElementById('restauracionTipo');
    const diasInput = document.getElementById('restauracionDias');

    if (!modal) {
        return;
    }

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const saldo = button.getAttribute('data-saldo');

        solicitudIdInput.value = button.getAttribute('data-id');
        funcionarioInput.value = button.getAttribute('data-funcionario');
        saldoInput.value = saldo;
        diasInput.value = saldo;
        diasInput.max = saldo;
        diasInput.readOnly = true;
        tipoInput.value = 'total';
    });

    tipoInput.addEventListener('change', () => {
        if (tipoInput.value === 'total') {
            diasInput.readOnly = true;
            diasInput.value = saldoInput.value;
        } else {
            diasInput.readOnly = false;
            diasInput.value = '';
        }
    });
});
</script>
@endpush
@endsection
