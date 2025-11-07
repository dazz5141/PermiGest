@extends('layouts.app')

@section('title', 'Feriados del Sistema')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Feriados</h3>
        <a href="{{ route('admin.feriados.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Feriado
        </a>
    </div>

    @include('components.alertas')

    <div class="card">
        <div class="card-body">

            @if($feriados->isEmpty())
                <p class="text-muted">No hay feriados registrados.</p>
            @else
                <table class="table table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($feriados as $f)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($f->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $f->nombre }}</td>
                            <td>{{ $f->tipo ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.feriados.edit', $f->id) }}" class="btn btn-sm btn-warning">Editar</a>

                                <form action="{{ route('admin.feriados.destroy', $f->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar feriado?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

        </div>
    </div>

</div>
@endsection
