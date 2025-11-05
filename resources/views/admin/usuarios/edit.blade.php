@extends('layouts.app')

@section('title', 'Editar Usuario - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-pencil-square text-warning me-2"></i> Editar Usuario
        </h4>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.usuarios.update', $usuario->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control" name="nombres" value="{{ $usuario->nombres }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" name="apellidos" value="{{ $usuario->apellidos }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">RUN</label>
                        <input type="text" class="form-control" name="run" value="{{ $usuario->run }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Correo institucional</label>
                        <input type="email" class="form-control" name="correo_institucional" value="{{ $usuario->correo_institucional }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cargo</label>
                        <input type="text" class="form-control" name="cargo" value="{{ $usuario->cargo }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Departamento</label>
                        <input type="text" class="form-control" name="departamento" value="{{ $usuario->departamento }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="rol_id" required>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" @selected($usuario->rol_id == $r->id)>{{ $r->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jefe directo</label>
                        <select class="form-select" name="jefe_directo_id">
                            <option value="">Sin jefe directo</option>
                            @foreach($jefes as $j)
                                <option value="{{ $j->id }}" @selected($usuario->jefe_directo_id == $j->id)>{{ $j->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
