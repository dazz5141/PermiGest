@extends('layouts.app')

@section('title', 'Editar Parentesco - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('parentescos.index') }}">Parentescos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar Parentesco</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-pencil-square text-warning fs-3 me-3"></i>
        <div>
            <h3 class="fw-bold mb-0">Editar Parentesco</h3>
            <p class="text-muted mb-0">Modifica la información del parentesco seleccionado</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('parentescos.update', $parentesco->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre', $parentesco->nombre) }}" required>
                </div>

                <div class="mb-3">
                    <label for="observacion" class="form-label fw-semibold">Observación</label>
                    <textarea id="observacion" name="observacion" class="form-control" rows="3">{{ old('observacion', $parentesco->observacion) }}</textarea>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('parentescos.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle me-1"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i> Actualizar parentesco
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
