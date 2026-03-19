<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Ficha de Permiso #{{ $solicitud->id }}</title>
<style>
  * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
  .w-100 { width: 100%; }
  .mt-2 { margin-top: 12px; }
  .mb-2 { margin-bottom: 12px; }
  .text-center { text-align: center; }
  .small { font-size: 11px; color: #555; }
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #333; padding: 6px; vertical-align: top; }
  .firmas td { height: 70px; }
</style>
</head>
<body>
  <div class="text-center mb-2">
    <div style="font-weight:700; font-size:16px;">FICHA DE PERMISO</div>
    <div class="small">Establecimiento: ______________________________</div>
  </div>

  <table class="mb-2">
    <tr>
      <th style="width: 35%;">Funcionario</th>
      <td>{{ $solicitud->usuario->nombres ?? '' }} {{ $solicitud->usuario->apellidos ?? '' }}</td>
    </tr>
    <tr>
      <th>RUT</th>
      <td>{{ $solicitud->usuario->run ?? '' }}</td>
    </tr>
    <tr>
      <th>Correo</th>
      <td>{{ $solicitud->usuario->correo_institucional ?? '' }}</td>
    </tr>
    <tr>
      <th>Cargo</th>
      <td>{{ $solicitud->usuario->cargo ?? '' }}</td>
    </tr>
  </table>

  <table class="mb-2">
    <tr>
      <th style="width: 35%;">Tipo de permiso</th>
      <td>{{ $solicitud->tipo?->nombre ?? '-' }}</td>
    </tr>
    <tr>
      <th>Desde</th>
      <td>{{ $solicitud->fecha_desde?->format('d-m-Y') ?? '-' }}</td>
    </tr>
    <tr>
      <th>Hasta</th>
      <td>{{ $solicitud->fecha_hasta?->format('d-m-Y') ?? '-' }}</td>
    </tr>
    <tr>
      <th>Días</th>
      <td>{{ $solicitud->dias_solicitados ?? '-' }}</td>
    </tr>
    @if(!empty($solicitud->hora_desde) || !empty($solicitud->hora_hasta))
      <tr>
        <th>Horario</th>
        <td>{{ $solicitud->hora_desde }} - {{ $solicitud->hora_hasta }}</td>
      </tr>
    @endif
    <tr>
      <th>Detalle / Observaciones</th>
      <td>{{ $solicitud->ultimaResolucion?->comentario ?? $solicitud->motivo ?? $solicitud->observaciones ?? '-' }}</td>
    </tr>
  </table>

  <table class="mb-2">
    <tr>
      <th style="width: 35%;">Estado</th>
      <td>{{ $solicitud->estado?->nombre ?? 'Pendiente' }}</td>
    </tr>
    <tr>
      <th>Resuelto por (Dirección)</th>
      <td>{{ $solicitud->validador->nombres ?? '' }} {{ $solicitud->validador->apellidos ?? '' }}</td>
    </tr>
    <tr>
      <th>Fecha de resolución</th>
      <td>
        @if(!empty($solicitud->fecha_revision))
          {{ \Carbon\Carbon::parse($solicitud->fecha_revision)->format('d-m-Y H:i') }}
        @else
          -
        @endif
      </td>
    </tr>
  </table>

  <table class="firmas mt-2">
    <tr>
      <td class="text-center">
        ________________________________<br>
        Firma Funcionario
      </td>
      <td class="text-center">
        ________________________________<br>
        Dirección del Establecimiento
      </td>
      <td class="text-center">
        ________________________________<br>
        Secretaría / Administración
      </td>
    </tr>
  </table>

  <div class="small mt-2">
    Ficha N. {{ str_pad($solicitud->id, 4, '0', STR_PAD_LEFT) }} - Generado el {{ now()->format('d-m-Y H:i') }}
  </div>
</body>
</html>
