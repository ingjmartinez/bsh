<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Diario de Operaciones</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <h2 style="margin-bottom: 8px;">Reporte Diario de Operaciones</h2>
    <p style="margin-top: 0;">Fecha: {{ $fecha }}</p>

    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th align="left">Ruta</th>
                <th align="left">Operador</th>
                <th align="right">Monto Procesado</th>
                <th align="right">Monto Entregado</th>
                <th align="right">Gasto</th>
                <th align="right">Diferencia</th>
                <th align="left">Estatus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportes as $item)
                @php
                    $operador = trim((($item->operador->nombre ?? '') . ' ' . ($item->operador->apellido ?? '')));
                    $pendiente = abs((float) $item->diferencia) > 0.00001;
                @endphp
                <tr>
                    <td>{{ $item->ruta->nombre_ruta ?? '-' }}</td>
                    <td>{{ $operador !== '' ? $operador : '-' }}</td>
                    <td align="right">{{ number_format((float) $item->procesado, 2) }}</td>
                    <td align="right">{{ number_format((float) $item->entregado, 2) }}</td>
                    <td align="right">{{ number_format((float) ($item->gasto ?? 0), 2) }}</td>
                    <td align="right">{{ number_format((float) $item->diferencia, 2) }}</td>
                    <td>{{ $pendiente ? 'Pendiente' : 'Completada' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay informes para la fecha seleccionada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
