@extends('layouts.app')

@section('title', 'Permiso por defunción - PermiGest Escolar')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-heart text-primary me-3 fs-2"></i>
            <div>
                <h2 class="mb-1 fw-bold">Solicitud de permiso por defunción</h2>
                <p class="text-muted mb-0">Complete el formulario para solicitar permiso por fallecimiento de familiar</p>
            </div>
        </div>
    </div>
</div>

@include('components.alertas')

<form action="{{ route('solicitudes.store') }}" method="POST">
    @csrf
    <input type="hidden" name="tipo_solicitud_id" value="3"> {{-- ID real del tipo Defunción --}}

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
                    <div class="alert alert-info mb-4" role="alert">
                        <h6 class="alert-heading fw-semibold">
                            <i class="bi bi-info-circle me-2"></i>
                            Información sobre días de permiso
                        </h6>
                        <p class="mb-0 small">Los días otorgados varían según el parentesco del familiar fallecido, de acuerdo con la normativa vigente.</p>
                    </div>

                    <div class="mb-3">
                        <label for="parentesco" class="form-label fw-semibold">Parentesco con el fallecido <span class="text-danger">*</span></label>
                        <select class="form-select @error('parentesco_id') is-invalid @enderror"
                                id="parentesco_id"
                                name="parentesco_id"
                                required>
                            <option value="">Seleccione el parentesco...</option>
                            @foreach ($parentescos as $parentesco)
                                <option value="{{ $parentesco->id }}"
                                    {{ old('parentesco_id') == $parentesco->id ? 'selected' : '' }}>
                                    {{ $parentesco->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('parentesco_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="dias" class="form-label fw-semibold">Cantidad de días <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            class="form-control @error('dias') is-invalid @enderror"
                            id="dias_solicitados"
                            name="dias_solicitados"
                            min="1"
                            max="7"
                            placeholder="Ej: 3"
                            value="{{ old('dias_solicitados') }}"
                            required
                        >
                        <small class="text-muted">Según normativa: hijo/cónyuge (7 días), padres (7 días), otros (1-3 días)</small>
                        @error('dias')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_desde" class="form-label fw-semibold">Desde <span class="text-danger">*</span></label>
                            <input
                                type="date"
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
                                type="date"
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
                        <label for="motivo" class="form-label fw-semibold">Observaciones</label>
                        <textarea
                            class="form-control @error('motivo') is-invalid @enderror"
                            id="motivo"
                            name="motivo"
                            rows="3"
                            placeholder="Información adicional (opcional)..."
                        >{{ old('motivo') }}</textarea>
                        @error('motivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-secondary mb-3" role="alert">
                        <small>
                            <strong>Nota:</strong> Deberá presentar el certificado de defunción al departamento de Recursos Humanos dentro de los próximos 5 días hábiles.
                        </small>
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
