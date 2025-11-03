@extends('layouts.app')

@section('title', 'Detalle de solicitud - PermiGest Escolar')

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex align-items-center mb-4">
    <i class="bi bi-file-earmark-text text-primary me-3 fs-3"></i>
    <h4 class="fw-bold mb-0">Detalle de la Solicitud</h4>
  </div>

  <div class="card shadow-sm border-0 rounded-3 mb-4">
    <div class="card-body">
      <div class="row mb-3 align-items-center">
        <div class="col-md-6">
          <h6 class="text-muted mb-1">Tipo de solicitud</h6>
          <p class="fw-semibold">{{ $solicitud->tipo?->nombre ?? '—' }}</p>
        </div>

        <div class="col-md-3">
          <h6 class="text-muted mb-1">Estado</h6>
          @php
            $estado = strtolower($solicitud->estado?->nombre ?? '');
            $badgeClass = match($estado) {
              'aprobado'      => 'bg-success',
              'en revisión', 'en revision' => 'bg-warning text-dark',
              'pendiente'     => 'bg-secondary',
              'rechazado'     => 'bg-danger',
              default         => 'bg-light text-dark',
            };
          @endphp
          <span class="badge {{ $badgeClass }}">
            {{ $solicitud->estado?->nombre ?? '—' }}
          </span>
        </div>

        <div class="col-md-3 text-end">
          @php
            $rol = strtolower(auth()->user()->rol?->nombre ?? '');
            $rutaVolver = match($rol) {
              'funcionario'    => route('solicitudes.index'),
              'jefe_directo'   => route('dashboard'),
              'secretaria'     => route('reportes.mensuales'),
              'admin'          => route('dashboard'),
              default          => route('dashboard'),
            };
          @endphp

          <a href="{{ $rutaVolver }}" class="btn btn-outline-primary btn-sm me-2">
            <i class="bi bi-arrow-left"></i> Volver
          </a>

          @if(in_array($rol, ['administrador','secretaria','inspector_general']))
            <a href="{{ route('solicitudes.pdf', $solicitud->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-printer"></i> Imprimir ficha
            </a>
          @endif
        </div>
      </div>

      <hr>

      <div class="row">
        <div class="col-md-6 mb-3">
          <h6 class="text-muted mb-1">Motivo</h6>
          {{-- Usa el campo que realmente tengas: motivo / asunto / descripcion / observaciones --}}
          <p>{{ $solicitud->motivo ?? $solicitud->descripcion ?? $solicitud->observaciones ?? '—' }}</p>
        </div>

        <div class="col-md-3 mb-3">
          <h6 class="text-muted mb-1">Desde</h6>
          <p>
            @if($solicitud->fecha_desde)
              {{ \Carbon\Carbon::parse($solicitud->fecha_desde)->format('Y-m-d') }}
            @else — @endif
          </p>
        </div>

        <div class="col-md-3 mb-3">
          <h6 class="text-muted mb-1">Hasta</h6>
          <p>
            @if($solicitud->fecha_hasta)
              {{ \Carbon\Carbon::parse($solicitud->fecha_hasta)->format('Y-m-d') }}
            @else — @endif
          </p>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Días solicitados</h6>
          <p>{{ $solicitud->dias_solicitados ?? '—' }}</p>
        </div>

        <div class="col-md-3">
          <h6 class="text-muted mb-1">Jornada</h6>
          {{-- Si tienes campo jornada (completa/media), úsalo. Si no, infiere: si hay hora_desde/hasta => Parcial, sino Completa --}}
          <p>
            @if(!empty($solicitud->jornada))
              {{ ucfirst($solicitud->jornada) }}
            @else
              {{ (!empty($solicitud->hora_desde) || !empty($solicitud->hora_hasta)) ? 'Parcial' : 'Completa' }}
            @endif
          </p>
        </div>

        <div class="col-md-6">
            <h6 class="text-muted mb-1">Comentario de resolución</h6>
            <p>{{ $solicitud->ultimaResolucion?->comentario ?? '—' }}</p>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
