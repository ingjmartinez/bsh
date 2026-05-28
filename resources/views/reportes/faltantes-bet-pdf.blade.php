<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Faltantes {{ $sistema ?? 'Lotobet Real' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .totales {
            margin-top: 20px;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Faltantes {{ $sistema ?? 'Lotobet Real' }}</h1>
        <p>Fecha de Generacion: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cedula</th>
                <th>Nombre Empleado</th>
                <th class="text-center">Cantidad de Faltantes</th>
                <th class="text-end">Monto Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalMonto = 0;
                $totalFaltantes = 0;
            @endphp
            @foreach($registros as $registro)
                @php
                    $totalMonto += $registro->total_monto;
                    $totalFaltantes += $registro->cantidad_faltantes;
                @endphp
                <tr>
                    <td>{{ $registro->identificacion }}</td>
                    <td>{{ trim($registro->nombre_empleado) ?: 'Sin especificar' }}</td>
                    <td class="text-center">{{ $registro->cantidad_faltantes }}</td>
                    <td class="text-end">${{ number_format($registro->total_monto, 2, '.', ',') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <p>Total de Faltantes: {{ $totalFaltantes }}</p>
        <p>Monto Total: ${{ number_format($totalMonto, 2, '.', ',') }}</p>
    </div>
</body>
</html>

