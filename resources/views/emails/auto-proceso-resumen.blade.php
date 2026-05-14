<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen auto proceso</title>
</head>
<body style="font-family: Arial, sans-serif; color: #212529;">
    <h2 style="margin-bottom: 8px;">Resumen auto proceso</h2>
    <p style="margin: 0 0 6px 0;"><strong>Sistema:</strong> {{ strtoupper($data['sistema'] ?? '-') }}</p>
    <p style="margin: 0 0 6px 0;"><strong>Fecha:</strong> {{ $data['fecha'] ?? '-' }}</p>
    <p style="margin: 0 0 16px 0;"><strong>Estado:</strong> {{ $data['estado'] ?? '-' }}</p>

    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background: #f1f3f5; text-align: left;">
                <th>Paso</th>
                <th>Estado</th>
                <th>Mensaje</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($data['detalles'] ?? []) as $row)
                <tr>
                    <td>{{ $row['modulo'] ?? '-' }}</td>
                    <td>
                        @if(!empty($row['ok']))
                            OK
                        @elseif(!empty($row['no_data']))
                            Sin datos
                        @else
                            Error
                        @endif
                    </td>
                    <td>{{ $row['message'] ?? '' }}</td>
                    <td>{{ $row['total'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 16px;">
        <strong>Resumen:</strong>
        OK={{ $data['ok_count'] ?? 0 }} |
        Sin datos={{ $data['no_data_count'] ?? 0 }} |
        Error={{ $data['error_count'] ?? 0 }} |
        Timeout={{ !empty($data['timed_out']) ? 'Si' : 'No' }} |
        Duracion={{ $data['elapsed_seconds'] ?? 0 }}s |
        Limite={{ $data['max_seconds'] ?? 1800 }}s
    </p>
</body>
</html>
