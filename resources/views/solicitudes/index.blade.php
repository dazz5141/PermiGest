@extends('layouts.app')

@section('title', 'Mis solicitudes - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-folder2-open text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Historial de Solicitudes</h4>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Días</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>0012</td>
                        <td>Permiso con goce</td>
                        <td>2025-10-25</td>
                        <td>2025-10-26</td>
                        <td>1</td>
                        <td><span class="badge bg-success">Aprobado</span></td>
                        <td>
                            <a href="{{ url('solicitudes/12') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>0013</td>
                        <td>Permiso varios</td>
                        <td>2025-10-27</td>
                        <td>2025-10-29</td>
                        <td>3</td>
                        <td><span class="badge bg-warning text-dark">En revisión</span></td>
                        <td>
                            <a href="{{ url('solicitudes/13') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
