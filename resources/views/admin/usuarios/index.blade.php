@extends('layouts.app')

@section('title', 'Usuarios - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-people-fill text-primary me-2"></i> Usuarios del Sistema
        </h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
            <i class="bi bi-plus-circle me-2"></i> Nuevo usuario
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>RUN</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Cargo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $u)
                        <tr>
                            <td>{{ $u->nombre_completo }}</td>
                            <td>{{ $u->run }}</td>
                            <td>{{ $u->correo_institucional }}</td>
                            <td>{{ $u->rol?->nombre ?? '—' }}</td>
                            <td>{{ $u->cargo ?? '—' }}</td>
                            <td>
                                @if($u->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.usuarios.edit', $u->id) }}" class="btn btn-sm btn-warning me-1">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('admin.usuarios.toggle', $u->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $u->activo ? 'btn-danger' : 'btn-success' }}" 
                                        onclick="return confirm('{{ $u->activo ? '¿Deshabilitar este usuario?' : '¿Habilitar este usuario?' }}')">
                                        <i class="bi {{ $u->activo ? 'bi-person-x' : 'bi-person-check' }}"></i>
                                    </button>
                                </form>

                                <button class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#resetPasswordModal"
                                        data-id="{{ $u->id }}"
                                        data-nombre="{{ $u->nombre_completo }}">
                                    <i class="bi bi-key"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No hay usuarios registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-labelledby="crearUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" action="{{ route('admin.usuarios.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control" name="nombres" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" name="apellidos" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">RUN</label>
                        <input type="text" class="form-control" name="run" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Correo institucional</label>
                        <input type="email" class="form-control" name="correo_institucional" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cargo</label>
                        <input type="text" class="form-control" name="cargo">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Departamento</label>
                        <input type="text" class="form-control" name="departamento">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="rol_id" required>
                            <option value="">Seleccione...</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jefe directo</label>
                        <select class="form-select" name="jefe_directo_id">
                            <option value="">Sin jefe directo</option>
                            @foreach($jefes as $j)
                                <option value="{{ $j->id }}">{{ $j->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal global Restablecer Contraseña -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="resetPasswordForm" class="modal-content" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-key me-2"></i>Restablecer contraseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Cambiando contraseña de <span id="resetUserName" class="fw-semibold text-dark"></span>
                </p>
                <div class="mb-3">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const resetModal = document.getElementById('resetPasswordModal');
    const form = document.getElementById('resetPasswordForm');
    const nameSpan = document.getElementById('resetUserName');

    resetModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        nameSpan.textContent = nombre;
        form.action = `/admin/usuarios/${userId}/reset-password`;
    });
});
</script>
@endpush
@endsection
