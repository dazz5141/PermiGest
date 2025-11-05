@extends('layouts.app')

@section('title', 'Roles - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-person-gear text-primary me-2"></i> Roles del Sistema
        </h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearRolModal">
            <i class="bi bi-plus-circle me-2"></i> Nuevo rol
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
                        <th>Descripción</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $rol)
                        <tr>
                            <td>{{ $rol->nombre }}</td>
                            <td>{{ $rol->descripcion ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.roles.edit', $rol->id) }}" class="btn btn-sm btn-warning me-1">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.roles.destroy', $rol->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este rol?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">No hay roles registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Rol -->
<div class="modal fade" id="crearRolModal" tabindex="-1" aria-labelledby="crearRolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
