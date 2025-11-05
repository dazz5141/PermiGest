@extends('layouts.app')

@section('title', 'Parentescos - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-people text-primary me-2"></i> Parentescos
        </h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearParentescoModal">
            <i class="bi bi-plus-circle me-2"></i> Nuevo parentesco
        </button>
    </div>

    @include('components.alertas')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Observación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parentescos as $p)
                        <tr>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $p->observacion ?? '—' }}</td>
                            <td>
                                <a href="{{ route('parentescos.edit', $p->id) }}" class="btn btn-sm btn-warning me-1">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('parentescos.destroy', $p->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este parentesco?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No hay parentescos registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Parentesco -->
<div class="modal fade" id="crearParentescoModal" tabindex="-1" aria-labelledby="crearParentescoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('parentescos.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="crearParentescoLabel">Nuevo parentesco</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observación</label>
                    <textarea class="form-control" name="observacion" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
