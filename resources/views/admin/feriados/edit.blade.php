@extends('layouts.app')

@section('title', 'Editar Feriado')

@section('content')
<div class="container py-4">

    <h3 class="fw-bold mb-3">Editar Feriado</h3>

    @include('components.alertas')

    <form action="{{ route('admin.feriados.update', $feriado->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label>Fecha *</label>
            <input type="date" name="fecha" class="form-control" value="{{ $feriado->fecha }}" required>
        </div>

        <div class="mb-3">
            <label>Nombre *</label>
            <input type="text" name="nombre" class="form-control" value="{{ $feriado->nombre }}" required>
        </div>

        <div class="mb-3">
            <label>Tipo</label>
            <input type="text" name="tipo" class="form-control" value="{{ $feriado->tipo }}">
        </div>

        <button class="btn btn-primary">Actualizar</button>
        <a href="{{ route('admin.feriados.index') }}" class="btn btn-secondary">Volver</a>

    </form>

</div>
@endsection
