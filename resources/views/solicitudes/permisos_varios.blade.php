@extends('layouts.app')

@section('title', 'Permisos varios - PermiGest Escolar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-files text-primary me-3 fs-2"></i>
            <div>
                <h2 class="mb-1 fw-bold">Solicitud de permisos varios</h2>
                <p class="text-muted mb-0">Complete el formulario según el tipo de permiso que necesita solicitar</p>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('solicitudes.store') }}" method="POST">
    @csrf
    <input type="hidden" name="tipo" value="varios">

    <div class="row">
        <!-- Card Información -->
        <div class="col-lg-5 mb-4">
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

        <!-- Card Solicitud -->
        <div class="col-lg-7 mb-4">
            <div class="card rounded-3 shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>
                        Detalles de la solicitud
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="tipo_solicitud" class="form-label fw-semibold">Tipo de solicitud <span class="text-danger">*</span></label>
                        <select class="form-select @error('tipo_solicitud') is-invalid @enderror" id="tipo_solicitud" name="tipo_solicitud" required>
                            <option value="">Seleccione el tipo de permiso...</option>
                            <option value="comision_servicio" {{ old('tipo_solicitud') == 'comision_servicio' ? 'selected' : '' }}>Comisión de servicio</option>
                            <option value="capacitacion" {{ old('tipo_solicitud') == 'capacitacion' ? 'selected' : '' }}>Capacitación o perfeccionamiento</option>
                            <option value="representacion" {{ old('tipo_solicitud') == 'representacion' ? 'selected' : '' }}>Representación institucional</option>
                            <option value="tramite_personal" {{ old('tipo_solicitud') == 'tramite_personal' ? 'selected' : '' }}>Trámite personal</option>
                            <option value="atencion_medica" {{ old('tipo_solicitud') == 'atencion_medica' ? 'selected' : '' }}>Atención médica</option>
                            <option value="otro" {{ old('tipo_solicitud') == 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('tipo_solicitud')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_desde" class="form-label fw-semibold">Desde <span class="text-danger">*</span></label>
                            <input
                                type="datetime-local"
                                class="form-control @error('fecha_desde') is-invalid @enderror"
                                id="fecha_desde"
                                name="fecha_desde"
                                value="{{ old('fecha_desde') }}"
                                required
                            >
                            @error('fecha_desde')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="fecha_hasta" class="form-label fw-semibold">Hasta <span class="text-danger">*</span></label>
                            <input
                                type="datetime-local"
                                class="form-control @error('fecha_hasta') is-invalid @enderror"
                                id="fecha_hasta"
                                name="fecha_hasta"
                                value="{{ old('fecha_hasta') }}"
                                required
                            >
                            @error('fecha_hasta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="incluir_viatico"
                                name="incluir_viatico"
                                {{ old('incluir_viatico') ? 'checked' : '' }}
                            >
                            <label class="form-check-label fw-semibold" for="incluir_viatico">
                                Incluir solicitud de viático
                            </label>
                        </div>
                        <small class="text-muted">Active esta opción si requiere viático para traslados o alimentación</small>
                    </div>

                    <div class="mb-3">
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

                    <div class="mb-3">
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
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="row">
        <div class="col-12">
            <div class="card rounded-3 shadow-sm border-0">
                <div class="card-body p-4">
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
</form>
@endsection
