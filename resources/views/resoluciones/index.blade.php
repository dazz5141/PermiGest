@extends('layouts.app')

@section('title', 'Panel de revisión - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-journal-check text-primary me-3 fs-3"></i>
        <h4 class="fw-bold mb-0">Revisión y Aprobación de Permisos</h4>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Funcionario</th>
                        <th>Tipo</th>
                        <th>Fechas</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>0008</td>
                        <td>María Soto</td>
                        <td>Permiso con goce</td>
                        <td>25/10 - 26/10</td>
                        <td>Trámite médico</td>
                        <td><span class="badge bg-warning text-dark">Pendiente</span></td>
                        <td>
                            <a href="#" class="btn btn-success btn-sm">
                                <i class="bi bi-check-lg"></i> Aprobar
                            </a>
                            <a href="#" class="btn btn-danger btn-sm">
                                <i class="bi bi-x-lg"></i> Rechazar
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>0009</td>
                        <td>Carlos Rivera</td>
                        <td>Permiso sin goce</td>
                        <td>01/11 - 04/11</td>
                        <td>Viaje personal</td>
                        <td><span class="badge bg-success">Aprobado</span></td>
                        <td>
                            <a href="#" class="btn btn-outline-secondary btn-sm">
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
