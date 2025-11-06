@extends('layouts.app')

@section('title', 'Auditoría del Sistema - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-search text-primary me-2"></i> Auditoría del Sistema
        </h4>
    </div>

    @include('components.alertas')

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            @if ($registros->isEmpty())
                <p class="text-muted text-center">No existen registros de auditoría.</p>
            @else

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Tabla</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach ($registros as $a)

                            <tr>
                                {{-- FECHA --}}
                                <td>{{ $a->created_at->format('d/m/Y H:i') }}</td>

                                {{-- USUARIO --}}
                                <td>
                                    @if($a->usuario)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-person-circle text-primary fs-5"></i>
                                            <div>
                                                <div class="fw-semibold">{{ $a->usuario->nombres }} {{ $a->usuario->apellidos }}</div>
                                                <div class="text-muted small">{{ $a->usuario->rol->nombre ?? '—' }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Desconocido</span>
                                    @endif
                                </td>

                                {{-- ACCIÓN --}}
                                <td>
                                    <span class="badge
                                        @if($a->accion === 'crear') bg-success
                                        @elseif($a->accion === 'actualizar') bg-warning text-dark
                                        @elseif($a->accion === 'eliminar') bg-danger
                                        @else bg-secondary @endif">
                                        {{ strtoupper($a->accion) }}
                                    </span>
                                </td>

                                {{-- TABLA --}}
                                <td>{{ $a->tabla }}</td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINACIÓN --}}
                <div class="mt-3">
                    {{ $registros->links() }}
                </div>

            @endif

        </div>
    </div>

</div>
@endsection
