@extends('layouts.app')

@section('title', 'Permiso con goce de sueldo - PermiGest Escolar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-file-earmark-check text-primary me-3 fs-2"></i>
            <div>
                <h2 class="mb-1 fw-bold">Solicitud de permiso administrativo con goce de sueldo</h2>
                <p class="text-muted mb-0">Complete el formulario para solicitar su permiso administrativo.</p>
            </div>
        </div>
    </div>
</div>

@include('components.alertas')

<form action="{{ route('solicitudes.store') }}" method="POST">
    @csrf
    <input type="hidden" name="tipo_codigo" value="{{ $tipoSolicitud->codigo }}">

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card rounded-3 shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-person-badge me-2 text-primary"></i>
                        Información del solicitante
                    </h5>
                </div>

                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">RUT</label>
                        <p class="form-control-plaintext fw-semibold">{{ Auth::user()->run ?? '-' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Nombre completo</label>
                        <p class="form-control-plaintext fw-semibold">
                            {{ Auth::user()->nombres }} {{ Auth::user()->apellidos }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Correo institucional</label>
                        <p class="form-control-plaintext">{{ Auth::user()->correo_institucional ?? '-' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Cargo</label>
                        <p class="form-control-plaintext">{{ Auth::user()->cargo ?? '-' }}</p>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-muted small">Director asignado</label>
                        <p class="form-control-plaintext">
                            {{ Auth::user()->jefeDirecto
                                ? Auth::user()->jefeDirecto->nombres . ' ' . Auth::user()->jefeDirecto->apellidos . ' - ' . Auth::user()->jefeDirecto->cargo
                                : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card rounded-3 shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-calendar-check me-2 text-primary"></i>
                        Días disponibles
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center g-3">
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3">
                                <div class="display-5 fw-bold text-primary mb-2">{{ $totalDias }}</div>
                                <p class="text-muted small mb-0">Días totales</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3">
                                <div class="display-5 fw-bold text-warning mb-2">{{ $diasTomados }}</div>
                                <p class="text-muted small mb-0">Días tomados</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3">
                                <div class="display-5 fw-bold text-success mb-2">{{ $diasDisponibles }}</div>
                                <p class="text-muted small mb-0">Días disponibles</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-3" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Puede solicitar días completos o medio día, en la mañana o en la tarde.</small>
                    </div>

                    @php
                        $porcentajeUso = ($diasTomados / max($totalDias, 1)) * 100;
                        if ($porcentajeUso < 50) {
                            $colorBarra = 'bg-success';
                        } elseif ($porcentajeUso < 80) {
                            $colorBarra = 'bg-warning';
                        } else {
                            $colorBarra = 'bg-danger';
                        }
                    @endphp

                    <div class="text-center mb-3">
                        <p class="small mb-2 fw-semibold text-muted">Uso de permisos</p>
                        <div class="progress" style="height: 8px;" data-bs-toggle="tooltip"
                            title="Ha utilizado {{ $diasTomados }} de {{ $totalDias }} días">
                            <div class="progress-bar {{ $colorBarra }}"
                                role="progressbar"
                                style="width: {{ $porcentajeUso }}%;">
                            </div>
                        </div>
                        <small class="text-muted">{{ round($porcentajeUso) }}% utilizados</small>
                    </div>

                    <div class="text-muted small border-top pt-3">
                        <i class="bi bi-info-circle me-1 text-primary"></i>
                        Los permisos con goce descuentan del cupo anual de 6 días y deben ser autorizados por Dirección.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card rounded-3 shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>
                        Detalles de la solicitud
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="motivo" class="form-label fw-semibold">Motivo del permiso <span class="text-danger">*</span></label>
                            <textarea
                                class="form-control @error('motivo') is-invalid @enderror"
                                id="motivo"
                                name="motivo"
                                rows="4"
                                placeholder="Describa el motivo de su solicitud..."
                                required
                            >{{ old('motivo') }}</textarea>
                            @error('motivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dias_solicitados" class="form-label fw-semibold">Cantidad de días <span class="text-danger">*</span></label>
                                    <input type="number"
                                        step="0.5"
                                        min="0.5"
                                        class="form-control @error('dias_solicitados') is-invalid @enderror"
                                        id="dias_solicitados"
                                        name="dias_solicitados"
                                        placeholder="Ej: 0.5, 1, 2"
                                        value="{{ old('dias_solicitados') }}"
                                        required>
                                    <small class="text-muted">Use 0.5 para medio día.</small>
                                    @error('dias_solicitados')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="jornada" class="form-label fw-semibold">Jornada</label>
                                    <select class="form-select @error('jornada') is-invalid @enderror" id="jornada" name="jornada">
                                        <option value="">Seleccione...</option>
                                        <option value="manana" {{ old('jornada') == 'manana' ? 'selected' : '' }}>Mañana</option>
                                        <option value="tarde" {{ old('jornada') == 'tarde' ? 'selected' : '' }}>Tarde</option>
                                    </select>
                                    <small class="text-muted">Solo se usa para medio día.</small>
                                    @error('jornada')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_desde" class="form-label fw-semibold">Desde <span class="text-danger">*</span></label>
                                    <input type="date"
                                        class="form-control @error('fecha_desde') is-invalid @enderror"
                                        id="fecha_desde"
                                        name="fecha_desde"
                                        min="{{ now()->toDateString() }}"
                                        value="{{ old('fecha_desde') }}"
                                        required>
                                    @error('fecha_desde')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="fecha_hasta" class="form-label fw-semibold">Hasta <span class="text-danger">*</span></label>
                                    <input type="date"
                                        class="form-control @error('fecha_hasta') is-invalid @enderror"
                                        id="fecha_hasta"
                                        name="fecha_hasta"
                                        min="{{ now()->toDateString() }}"
                                        value="{{ old('fecha_hasta') }}"
                                        required>
                                    @error('fecha_hasta')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row" id="horasContainer" style="display: none;">
                                <div class="col-md-6 mb-3">
                                    <label for="hora_desde" class="form-label fw-semibold">Hora desde</label>
                                    <input type="time"
                                        class="form-control @error('hora_desde') is-invalid @enderror"
                                        id="hora_desde"
                                        name="hora_desde"
                                        readonly>
                                    @error('hora_desde')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="hora_hasta" class="form-label fw-semibold">Hora hasta</label>
                                    <input type="time"
                                        class="form-control @error('hora_hasta') is-invalid @enderror"
                                        id="hora_hasta"
                                        name="hora_hasta"
                                        readonly>
                                    @error('hora_hasta')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold">Confirme su contraseña <span class="text-danger">*</span></label>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="Ingrese su contraseña actual"
                                required
                            >
                            <small class="text-muted">Esta validación confirma la identidad del solicitante.</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <hr class="mb-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('solicitudes.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Cancelar
                                </a>
                                <button type="submit"
                                        class="btn btn-primary"
                                        data-confirm
                                        data-confirm-title="Enviar solicitud"
                                        data-confirm-text="La solicitud será enviada a Dirección para su revisión."
                                        data-confirm-btn="Enviar"
                                        data-cancel-btn="Cancelar"
                                        data-confirm-icon="question">
                                    <i class="bi bi-send me-2"></i>
                                    Enviar solicitud
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const feriados = @json($feriados ?? []);
    const diasDisponibles = {{ $diasDisponibles }};
    const diasInput = document.getElementById('dias_solicitados');
    const jornadaSelect = document.getElementById('jornada');
    const fechaDesdeInput = document.getElementById('fecha_desde');
    const fechaHastaInput = document.getElementById('fecha_hasta');
    const horasContainer = document.getElementById('horasContainer');
    const horaDesdeInput = document.getElementById('hora_desde');
    const horaHastaInput = document.getElementById('hora_hasta');

    const HORARIO = {
        manana: { desde: '08:00', hasta: '12:30' },
        tarde: { desde: '13:30', hasta: '17:00' }
    };

    function parseLocalDate(value) {
        const [y, m, d] = value.split('-').map(Number);
        return new Date(y, m - 1, d);
    }

    function formatYmd(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function esNoHabil(date) {
        const day = date.getDay();
        return day === 0 || day === 6 || feriados.includes(formatYmd(date));
    }

    function sumarDiasHabiles(fechaInicio, cantidadDias) {
        const fecha = new Date(fechaInicio);
        let diasSumados = 0;

        while (diasSumados < cantidadDias) {
            fecha.setDate(fecha.getDate() + 1);
            if (!esNoHabil(fecha)) {
                diasSumados++;
            }
        }

        return fecha;
    }

    function actualizarCampos() {
        const dias = parseFloat(diasInput.value || 0);
        const jornada = jornadaSelect.value;
        const fechaDesde = fechaDesdeInput.value;

        horaDesdeInput.value = '';
        horaHastaInput.value = '';

        if (dias === 0.5) {
            jornadaSelect.disabled = false;
            horasContainer.style.display = 'flex';
            if (fechaDesde) {
                fechaHastaInput.value = fechaDesde;
            }

            if (HORARIO[jornada]) {
                horaDesdeInput.value = HORARIO[jornada].desde;
                horaHastaInput.value = HORARIO[jornada].hasta;
            }
        } else {
            jornadaSelect.disabled = true;
            jornadaSelect.value = '';
            horasContainer.style.display = 'none';

            if (fechaDesde && dias >= 1) {
                const inicio = parseLocalDate(fechaDesde);
                const fin = sumarDiasHabiles(inicio, dias - 1);
                fechaHastaInput.value = formatYmd(fin);
            }
        }
    }

    function validarFechaNoHabil(input) {
        input.addEventListener('change', function () {
            if (!this.value) return;

            const fecha = parseLocalDate(this.value);
            if (esNoHabil(fecha)) {
                this.value = '';
                this.classList.add('is-invalid');
                return;
            }

            this.classList.remove('is-invalid');
        });
    }

    diasInput.addEventListener('input', () => {
        const valor = parseFloat(diasInput.value || 0);
        if (valor > diasDisponibles) {
            diasInput.classList.add('is-invalid');
            diasInput.setCustomValidity('No tiene suficientes días disponibles.');
        } else {
            diasInput.classList.remove('is-invalid');
            diasInput.setCustomValidity('');
        }
        actualizarCampos();
    });

    jornadaSelect.addEventListener('change', actualizarCampos);
    fechaDesdeInput.addEventListener('change', actualizarCampos);

    validarFechaNoHabil(fechaDesdeInput);
    validarFechaNoHabil(fechaHastaInput);

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        new bootstrap.Tooltip(el);
    });

    actualizarCampos();
});
</script>
@endpush
@endsection

@include('components.confirm')
