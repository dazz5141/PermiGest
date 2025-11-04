<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Ficha de Permiso #{{ $solicitud->id }}</title>
<style>
  * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
  .w-100{ width:100%; } .mt-1{ margin-top:6px; } .mt-2{ margin-top:12px; }
  .mb-1{ margin-bottom:6px; } .mb-2{ margin-bottom:12px; }
  .text-center{ text-align:center; } .text-right{ text-align:right; }
  .border{ border:1px solid #333; } .p-1{ padding:8px; }
  .title{ font-weight:700; font-size:16px; }
  table { border-collapse: collapse; width: 100%; }
  th, td { border:1px solid #333; padding:6px; vertical-align:top; }
  .small { font-size: 11px; color: #555; }
  .firmas td { height: 70px; }
</style>
</head>
<body>
  <div class="text-center mb-2">
    <div class="title">FICHA DE PERMISO</div>
    <div class="small">Establecimiento: ______________________________</div>
  </div>

  <table class="mb-2">
    <tr>
      <th style="width: 35%;">Funcionario</th>
      <td>{{ $solicitud->usuario->nombres ?? '' }} {{ $solicitud->funcionario->apellidos ?? '' }}</td>
    </tr>
    <tr>
      <th>RUT</th>
      <td>{{ $solicitud->usuario->run ?? '' }}</td>
    </tr>
    <tr>
      <th>Correo</th>
      <td>{{ $solicitud->usuario->correo_institucional ?? $solicitud->funcionario->email ?? '' }}</td>
    </tr>
    <tr>
      <th>Cargo / Unidad</th>
      <td>{{ $solicitud->usuario->cargo ?? '' }} / {{ $solicitud->funcionario->unidad ?? '' }}</td>
    </tr>
  </table>

  <table class="mb-2">
    <tr>
      <th style="width: 35%;">Tipo de Permiso</th>
      <td>{{ $solicitud->tipo?->nombre ?? '—' }}</td>
    </tr>
    <tr>
      <th>Desde</th>
      <td>{{ \Carbon\Carbon::parse($solicitud->fecha_desde)->format('d-m-Y') }}</td>
    </tr>
    <tr>
      <th>Hasta</th>
      <td>{{ \Carbon\Carbon::parse($solicitud->fecha_hasta)->format('d-m-Y') }}</td>
    </tr>
    <tr>
      <th>Días</th>
      <td>{{ $solicitud->dias_solicitados ?? $solicitud->dias ?? '—' }}</td>
    </tr>
    @if(!empty($solicitud->hora_desde) || !empty($solicitud->hora_hasta))
      <tr>
        <th>Horario</th>
        <td>{{ $solicitud->hora_desde }} – {{ $solicitud->hora_hasta }}</td>
      </tr>
    @endif
    <tr>
      <th>Detalle / Observaciones</th>
      <td>{{ $solicitud->ultimaResolucion?->comentario ?? $solicitud->motivo ?? $solicitud->observaciones ?? '—' }}</td>
    </tr>
  </table>

  <table class="mb-2">
    <tr>
      <th style="width: 35%;">Estado</th>
      <td>{{ $solicitud->estado?->nombre ?? 'Pendiente' }}</td>
    </tr>
    <tr>
      <th>Revisado por (Jefatura)</th>
      <td>{{ $solicitud->validador->nombres ?? '' }} {{ $solicitud->validador->apellidos ?? '' }}</td>
    </tr>
    <tr>
      <th>Fecha de Resolución</th>
      <td>
        @if(!empty($solicitud->updated_at))
          {{ \Carbon\Carbon::parse($solicitud->updated_at)->format('d-m-Y H:i') }}
        @else — @endif
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
        Jefatura Directa
      </td>
      <td class="text-center">
        ________________________________<br>
        Secretaría / Administración
      </td>
    </tr>
  </table>

    <div class="small mt-2 text-muted">
        Ficha N° {{ str_pad($solicitud->id, 4, '0', STR_PAD_LEFT) }} · Generado el {{ now()->format('d-m-Y H:i') }}
    </div>
</body>
</html>
