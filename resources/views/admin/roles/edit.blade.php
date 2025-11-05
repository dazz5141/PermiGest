@extends('layouts.app')

@section('title', 'Editar Rol - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-pencil-square text-warning me-2"></i> Editar Rol
        </h4>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.update', $rol->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" value="{{ $rol->nombre }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3">{{ $rol->descripcion }}</textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
