@extends('layouts.app')

@section('title', 'Permiso sin goce de sueldo - PermiGest Escolar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-file-earmark-minus text-primary me-3 fs-2"></i>
            <div>
                <h2 class="mb-1 fw-bold">Solicitud de permiso administrativo sin goce de sueldo</h2>
                <p class="text-muted mb-0">Complete el formulario para solicitar su permiso administrativo</p>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('solicitudes.store') }}" method="POST">
    @csrf
    <input type="hidden" name="tipo_solicitud_id" value="2">

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
                        <p class="form-control-plaintext fw-semibold">{{ Auth::user()->run ?? '—' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Nombre completo</label>
                        <p class="form-control-plaintext fw-semibold">
                            {{ Auth::user()->nombres }} {{ Auth::user()->apellidos }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Correo institucional</label>
                        <p class="form-control-plaintext">
                            {{ Auth::user()->correo_institucional ?? '—' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Cargo</label>
                        <p class="form-control-plaintext">
                            {{ Auth::user()->cargo ?? '—' }}
                        </p>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-muted small">Jefe directo</label>
                        <p class="form-control-plaintext">
                            {{ Auth::user()->jefeDirecto
                                ? Auth::user()->jefeDirecto->nombres . ' ' . Auth::user()->jefeDirecto->apellidos . ' — ' . Auth::user()->jefeDirecto->cargo
                                : '—' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Información importante -->
        <div class="col-lg-6 mb-4">
            <div class="card rounded-3 shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Información importante
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-warning mb-3" role="alert">
                        <h6 class="alert-heading fw-semibold">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Permiso sin goce de sueldo
                        </h6>
                        <p class="mb-0 small">Este tipo de permiso implica que no recibirá remuneración durante los días solicitados. Solo se permiten días completos.</p>
                    </div>

                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">Requisitos:</h6>
                            <ul class="mb-0 small">
                                <li class="mb-2">Solicitud con mínimo 15 días de anticipación</li>
                                <li class="mb-2">Justificación detallada del motivo</li>
                                <li class="mb-2">Solo días completos (no se permite medio día)</li>
                                <li class="mb-0">Aprobación de jefatura directa</li>
                            </ul>
                        </div>
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
                                rows="6"
                                placeholder="Describa detalladamente el motivo de su solicitud de permiso..."
                                required
                            >{{ old('motivo') }}</textarea>
                            <small class="text-muted">Es importante que justifique su solicitud de manera clara y completa.</small>
                            @error('motivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dias" class="form-label fw-semibold">Cantidad de días completos <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    class="form-control @error('dias') is-invalid @enderror"
                                    id="dias_solicitados"
                                    name="dias_solicitados"
                                    min="1"
                                    placeholder="Ej: 5"
                                    value="{{ old('dias_solicitados') }}"
                                    required
                                >
                                <small class="text-muted">Solo días completos, no se permite medio día</small>
                                @error('dias_solicitados')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
  const diasInput   = document.getElementById('dias_solicitados');
  const fechaDesde  = document.getElementById('fecha_desde');
  const fechaHasta  = document.getElementById('fecha_hasta');

  // Parsear YYYY-MM-DD a Date local (evita el bug de UTC)
  function parseLocalDate(yyyyMmDd) {
    const [y, m, d] = yyyyMmDd.split('-').map(Number);
    return new Date(y, m - 1, d); // <-- local
  }

  // Formatear Date a YYYY-MM-DD sin usar toISOString()
  function formatYmd(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }

  function calcularFechaHasta() {
    const dias  = parseInt(diasInput.value || 0, 10);
    const desde = fechaDesde.value; // siempre viene en YYYY-MM-DD

    if (!dias || !desde) {
      fechaHasta.value = '';
      return;
    }

    const inicio = parseLocalDate(desde);     // ← local
    const fin    = new Date(inicio);
    fin.setDate(inicio.getDate() + (dias - 1)); // rango inclusivo

    fechaHasta.value = formatYmd(fin);        // ← sin UTC
  }

  diasInput.addEventListener('input',  calcularFechaHasta);
  diasInput.addEventListener('change', calcularFechaHasta);
  fechaDesde.addEventListener('change', calcularFechaHasta);

  // si ya viene algo desde old(), recalcula al cargar
  calcularFechaHasta();
});
</script>
@endsection
