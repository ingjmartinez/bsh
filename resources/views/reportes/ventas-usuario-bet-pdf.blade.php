<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas sin Cédula Registradas en Empleados y Vacias</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #666; padding: 6px; text-align: left; }
        th { background-color: #e0e0e0; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h2>Reporte de Ventas sin Cédula Registradas en Empleados y Vacias</h2>
    <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Consorcio</th>
                <th>Agencia</th>
                <th>Cédula</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registros as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->consorcio_id }}</td>
                    <td>{{ $r->agencia_id }}</td>
                    <td>{{ $r->cedula }}</td>
                    <td>{{ $r->tipo }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:20px; text-align:right;">Total registros: {{ count($registros) }}</p>
</body>
</html>
