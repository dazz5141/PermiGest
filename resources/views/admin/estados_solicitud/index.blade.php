@extends('layouts.app')

@section('title', 'Estados de Solicitud - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-flag text-primary me-2"></i> Estados de Solicitud
        </h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearEstadoModal">
            <i class="bi bi-plus-circle me-2"></i> Nuevo estado
        </button>
    </div>

    @include('components.alertas')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($estados as $estado)
                        <tr>
                            <td>{{ $estado->nombre }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('estados.edit', $estado->id) }}" class="btn btn-sm btn-warning me-1">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('estados.destroy', $estado->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este estado?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted">No hay estados registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Estado -->
<div class="modal fade" id="crearEstadoModal" tabindex="-1" aria-labelledby="crearEstadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('estados.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="crearEstadoLabel">Nuevo estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
