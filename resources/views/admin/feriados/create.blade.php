@extends('layouts.app')

@section('title', 'Nuevo Feriado')

@section('content')
<div class="container py-4">

    <h3 class="fw-bold mb-3">Agregar Feriado</h3>

    @include('components.alertas')

    <form action="{{ route('admin.feriados.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Fecha *</label>
            <input type="date" name="fecha" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nombre *</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Tipo</label>
            <input type="text" name="tipo" class="form-control">
        </div>

        <button class="btn btn-primary">Guardar</button>
        <a href="{{ route('admin.feriados.index') }}" class="btn btn-secondary">Volver</a>

    </form>

</div>
@endsection
