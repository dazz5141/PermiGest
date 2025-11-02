@extends('layouts.app')

@section('title', 'Permiso con goce de sueldo - PermiGest Escolar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-file-earmark-check text-primary me-3 fs-2"></i>
            <div>
                <h2 class="mb-1 fw-bold">Solicitud de permiso administrativo con goce de sueldo</h2>
                <p class="text-muted mb-0">Complete el formulario para solicitar su permiso administrativo</p>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('solicitudes.store') }}" method="POST">
    @csrf
    <input type="hidden" name="tipo_solicitud_id" value="1">

    <div class="row">
        <!-- Card Información -->
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
                        <p class="form-control-plaintext fw-semibold">{{ Auth::user()->rut ?? '12.345.678-9' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Nombre completo</label>
                        <p class="form-control-plaintext fw-semibold">{{ Auth::user()->name ?? 'Juan Pérez González' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Correo institucional</label>
                        <p class="form-control-plaintext">{{ Auth::user()->email ?? 'juan.perez@institucion.cl' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Cargo</label>
                        <p class="form-control-plaintext">{{ Auth::user()->cargo ?? 'Docente de Matemáticas' }}</p>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-muted small">Jefe directo</label>
                        <p class="form-control-plaintext">{{ Auth::user()->jefe ?? 'María López Sánchez' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Días disponibles -->
        <div class="col-lg-6 mb-4">
            <div class="card rounded-3 shadow-sm border-0">
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

                    <div class="alert alert-info mt-4 mb-0" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Puede solicitar medio día (mañana o tarde) o días completos según sus necesidades.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Solicitud -->
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
                                placeholder="Describa el motivo de su solicitud de permiso..."
                                required
                            >{{ old('motivo') }}</textarea>
                            @error('motivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dias" class="form-label fw-semibold">Cantidad de días <span class="text-danger">*</span></label>
                                    <input type="number"
                                        step="0.5"
                                        min="0.5"
                                        class="form-control"
                                        id="dias_solicitados"
                                        name="dias_solicitados"
                                        placeholder="Ej: 0.5, 1, 2, 3..."
                                        value="{{ old('dias_solicitados') }}"
                                        required
                                        oninput="actualizarCamposJornada()">
                                    <small class="text-muted">Use 0.5 para medio día</small>
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
                                    <small class="text-muted">Solo para medio día</small>
                                    @error('jornada')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Fechas -->
                            <div class="row mt-2 align-items-start">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_desde" class="form-label fw-semibold">
                                        Desde <span class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                        class="form-control @error('fecha_desde') is-invalid @enderror"
                                        id="fecha_desde"
                                        name="fecha_desde"
                                        value="{{ old('fecha_desde') }}"
                                        required>
                                    @error('fecha_desde')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3" id="fechaHastaContainer">
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_hasta" class="form-label fw-semibold">
                                            Hasta <span class="text-danger">*</span>
                                        </label>
                                        <input type="date"
                                            class="form-control @error('fecha_hasta') is-invalid @enderror"
                                            id="fecha_hasta"
                                            name="fecha_hasta"
                                            value="{{ old('fecha_hasta') }}"
                                            required>
                                        @error('fecha_hasta')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Horas automáticas (solo si es medio día) -->
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
                                placeholder="Ingrese su contraseña para confirmar"
                                required
                            >
                            <small class="text-muted">Por seguridad, confirme su identidad con su contraseña</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <hr class="mb-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="#" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
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
<script>
document.addEventListener('DOMContentLoaded', () => {
    const diasInput = document.getElementById('dias_solicitados');
    const jornadaSelect = document.getElementById('jornada');
    const fechaDesdeInput = document.getElementById('fecha_desde');
    const fechaHastaInput = document.getElementById('fecha_hasta');
    const fechaHastaContainer = document.getElementById('fechaHastaContainer');
    const horasContainer = document.getElementById('horasContainer');
    const horaDesdeInput = document.getElementById('hora_desde');
    const horaHastaInput = document.getElementById('hora_hasta');

    // Horarios fijos
    const HORARIO = {
        manana: { desde: '08:00', hasta: '12:30' },
        tarde:  { desde: '13:30', hasta: '17:00' },
        completo: { desde: '08:00', hasta: '17:00' }
    };

    // Mostrar u ocultar contenedores
    const show = el => el && (el.style.display = 'flex');
    const hide = el => el && (el.style.display = 'none');

    function actualizarCampos() {
        const dias = parseFloat(diasInput.value || 0);
        const jornada = jornadaSelect.value;
        const fechaDesde = fechaDesdeInput.value;

        // Reset
        horaDesdeInput.value = '';
        horaHastaInput.value = '';
        fechaHastaInput.value = '';

        // Medio día
        if (dias === 0.5) {
            show(horasContainer);
            hide(fechaHastaContainer);

            // Si hay jornada seleccionada
            if (jornada === 'manana') {
                horaDesdeInput.value = HORARIO.manana.desde;
                horaHastaInput.value = HORARIO.manana.hasta;
            } else if (jornada === 'tarde') {
                horaDesdeInput.value = HORARIO.tarde.desde;
                horaHastaInput.value = HORARIO.tarde.hasta;
            } else {
                // Por defecto horario completo
                horaDesdeInput.value = HORARIO.completo.desde;
                horaHastaInput.value = HORARIO.completo.hasta;
            }

            // La fecha hasta = igual a desde
            if (fechaDesde) fechaHastaInput.value = fechaDesde;

        } else if (dias >= 1) {
            // Días completos
            hide(horasContainer);
            show(fechaHastaContainer);

            // Calcular fecha hasta automáticamente
            if (fechaDesde) {
                const fecha = new Date(fechaDesde);
                fecha.setDate(fecha.getDate() + dias - 1);
                fechaHastaInput.value = fecha.toISOString().split('T')[0];
            }
        } else {
            // Por defecto (nada seleccionado)
            hide(horasContainer);
            show(fechaHastaContainer);
        }
    }

    // Listeners
    diasInput.addEventListener('input', actualizarCampos);
    jornadaSelect.addEventListener('change', actualizarCampos);
    fechaDesdeInput.addEventListener('change', actualizarCampos);

    // Inicial
    actualizarCampos();

    // 🚨 Validación si excede días disponibles
    const diasDisponibles = {{ $diasDisponibles }};
    diasInput.addEventListener('input', () => {
        const valor = parseFloat(diasInput.value || 0);
        if (valor > diasDisponibles) {
            diasInput.classList.add('is-invalid');
            diasInput.setCustomValidity('No tiene suficientes días disponibles.');
        } else {
            diasInput.classList.remove('is-invalid');
            diasInput.setCustomValidity('');
        }
    });
});
</script>

@endsection
