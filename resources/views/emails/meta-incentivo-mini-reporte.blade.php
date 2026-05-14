<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mini reporte Meta Incentivo</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f5f7fb; padding:20px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="680" style="max-width:680px; width:100%; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; font-family:Arial, sans-serif; color:#111827;">
                    <tr>
                        <td style="padding:18px 20px; background:#0b5ed7; color:#ffffff;">
                            <h2 style="margin:0; font-size:18px;">Mini reporte Meta Incentivo</h2>
                            <p style="margin:6px 0 0; font-size:13px; opacity:.95;">
                                Coordinador: <strong>{{ $data['coordinador'] ?? '-' }}</strong> | Período: <strong>{{ str_pad((string) ($data['periodo_mes'] ?? $data['mes'] ?? ''), 2, '0', STR_PAD_LEFT) }}/{{ $data['periodo_anio'] ?? $data['anio'] ?? '-' }}</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 20px 6px; font-size:13px; color:#374151;">
                            Rango evaluado: <strong>{{ $data['fecha_inicio'] ?? '-' }}</strong> al <strong>{{ $data['fecha_fin'] ?? '-' }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 20px 20px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse; font-size:13px;">
                                <thead>
                                    <tr>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Agencia</th>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Coordinador</th>
                                        <th align="left" style="border:1px solid #e5e7eb; background:#f9fafb; padding:10px;">Cumplimiento meta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($data['filas'] ?? []) as $fila)
                                        <tr>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">
                                                <div style="font-weight:600;">{{ $fila['agencia'] ?? '-' }}</div>
                                                @if(!empty($fila['codigo']))
                                                    <div style="font-size:12px; color:#6b7280; margin-top:4px;">Código: {{ $fila['codigo'] }}</div>
                                                @endif
                                            </td>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">{{ $fila['coordinador'] ?? '-' }}</td>
                                            <td style="border:1px solid #e5e7eb; padding:10px;">{{ $fila['cumplimiento_meta'] ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" style="border:1px solid #e5e7eb; padding:10px; color:#6b7280;">No hay datos para mostrar.</td>
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
