<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte de incumplimientos por coordinador</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f5f7fb; padding:20px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="900" style="max-width:900px; width:100%; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; font-family:Arial, sans-serif; color:#111827;">
                    <tr>
                        <td style="padding:18px 20px; background:#0b5ed7; color:#ffffff;">
                            <h2 style="margin:0; font-size:18px;">Reporte de incumplimientos</h2>
                            <p style="margin:6px 0 0; font-size:13px; opacity:.95;">
                                Coordinador: <strong>{{ $data['coordinador'] ?? '-' }}</strong> | Fecha: <strong>{{ $data['fecha'] ?? '-' }}</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 20px 20px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse; font-size:13px;">
                                <thead>
                                    <tr>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Agencia</th>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Terminal</th>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Estado</th>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Min. tarde</th>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Min. salida antes</th>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Fuente</th>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($data['rows'] ?? []) as $fila)
                                        <tr>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">{{ $fila['nombre_agencia'] ?? '-' }} ({{ $fila['agencia'] ?? '-' }})</td>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">{{ $fila['terminal'] ?? '-' }}</td>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">{{ $fila['estado'] ?? '-' }}</td>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">{{ (int) ($fila['minutos_tarde'] ?? 0) }}</td>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">{{ (int) ($fila['minutos_salida_antes'] ?? 0) }}</td>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">{{ $fila['fuente'] ?? '-' }}</td>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">{{ $fila['observaciones'] ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" style="border:1px solid #e5e7eb; padding:10px; color:#6b7280;">No hay datos para mostrar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
