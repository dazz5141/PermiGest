<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte Mensual de Permisos - {{ ucfirst($nombreMes) }} {{ $año }}</title>
<style>
  * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
  h3 { text-align: center; margin-bottom: 5px; }
  p { text-align: center; margin: 0 0 10px 0; font-size: 13px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th, td { border: 1px solid #333; padding: 5px; vertical-align: top; }
  th { background: #f2f2f2; }
  .text-center { text-align: center; }
</style>
</head>
<body>

  <h3>REPORTE MENSUAL DE PERMISOS</h3>
  <p><strong>Mes:</strong> {{ ucfirst($nombreMes) }} {{ $año }}</p>

  <table>
    <thead>
      <tr>
        <th>Funcionario</th>
        <th>Tipo</th>
        <th>Desde</th>
        <th>Hasta</th>
        <th>Días</th>
        <th>Estado</th>
        <th>Comentario</th>
      </tr>
    </thead>
    <tbody>
      @forelse($resoluciones as $res)
        <tr>
          <td>{{ $res->solicitud->usuario->nombres }} {{ $res->solicitud->usuario->apellidos }}</td>
          <td>{{ $res->solicitud->tipo->nombre }}</td>
          <td>{{ $res->solicitud->fecha_desde?->format('d/m/Y') }}</td>
          <td>{{ $res->solicitud->fecha_hasta?->format('d/m/Y') }}</td>
          <td>{{ $res->solicitud->dias_solicitados ?? '—' }}</td>
          <td>{{ ucfirst($res->solicitud->estado->nombre) }}</td>
          <td>{{ $res->comentario ?? '—' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center">No hay permisos registrados este mes.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <p style="margin-top: 20px; text-align:right; font-size:11px;">
    Generado el {{ now()->format('d/m/Y H:i') }}
  </p>

</body>
</html>
