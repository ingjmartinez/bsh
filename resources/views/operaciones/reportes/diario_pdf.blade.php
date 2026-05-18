<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Diario de Operaciones</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; }
        h2 { margin: 0 0 6px 0; }
        p { margin: 0 0 12px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        .summary-cards { margin: 0 0 12px 0; }
        .summary-cards td { width: 25%; vertical-align: top; border: 1px solid #d1d5db; background: #f9fafb; padding: 8px; }
        .summary-label { color: #6b7280; font-size: 10px; margin-bottom: 4px; }
        .summary-value { text-align: right; font-size: 16px; font-weight: bold; }
        .text-end { text-align: right; }
        .text-success { color: #0f766e; }
        .text-danger { color: #b91c1c; }
    </style>
</head>
<body>
    @php
        $totalProcesado = (float) $reportes->sum('procesado');
        $totalEntregado = (float) $reportes->sum('entregado');
        $totalGasto = (float) $reportes->sum('gasto');
        $totalDiferencia = (float) $reportes->sum('diferencia');
    @endphp

    <h2>Cuadre de ruta Business Support Hub</h2>
    <p>Fecha: {{ \Carbon\Carbon::parse($fechaFiltro)->format('d/m/Y') }}</p>

    <table class="summary-cards">
        <tr>
            <td>
                <div class="summary-label">Monto Procesado en Agencia</div>
                <div class="summary-value">{{ number_format($totalProcesado, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">Monto Entregado en Banco</div>
                <div class="summary-value">{{ number_format($totalEntregado, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">Gasto</div>
                <div class="summary-value">{{ number_format($totalGasto, 2) }}</div>
            </td>
            <td>
                <div class="summary-label">Diferencia</div>
                <div class="summary-value {{ $totalDiferencia < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($totalDiferencia, 2) }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Ruta</th>
                <th>Operador</th>
                <th class="text-end">Monto Procesado en Agencia</th>
                <th class="text-end">Monto Entregado en Banco</th>
                <th class="text-end">Gasto</th>
                <th class="text-end">Diferencia</th>
                <th>Correo Operador</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportes as $item)
                @php
                    $pendiente = abs((float) $item->diferencia) > 0.00001;
                    $operador = trim((($item->operador->nombre ?? '') . ' ' . ($item->operador->apellido ?? '')));
                @endphp
                <tr>
                    <td>{{ optional($item->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $item->ruta->nombre_ruta ?? '-' }}</td>
                    <td>{{ $operador !== '' ? $operador : '-' }}</td>
                    <td class="text-end">{{ number_format((float) $item->procesado, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $item->entregado, 2) }}</td>
                    <td class="text-end">{{ number_format((float) ($item->gasto ?? 0), 2) }}</td>
                    <td class="text-end {{ (float) $item->diferencia < 0 ? 'text-danger' : 'text-success' }}">{{ number_format((float) $item->diferencia, 2) }}</td>
                    <td>{{ $item->correo_destino }}</td>
                    <td>{{ $pendiente ? 'Pendiente' : 'Completada' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No hay informes para la fecha seleccionada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

