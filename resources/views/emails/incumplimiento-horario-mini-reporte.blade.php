<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Reporte de Incumplimiento</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f3f9;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f3f9; padding: 32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #405189 0%, #0ab39c 100%); padding: 26px 34px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700;">Mini reporte de incumplimiento</h1>
                            <p style="margin: 8px 0 0; color: rgba(255, 255, 255, 0.9); font-size: 13px;">Control de asistencia por agencia - {{ $data['fecha'] ?? '-' }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 28px 34px;">
                            <p style="margin: 0 0 16px; color: #495057; font-size: 14px; line-height: 1.6;">
                                Se comparte el detalle de la agencia evaluada en la fecha indicada.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border: 1px solid #e9ebec; border-radius: 8px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; width: 38%; font-size: 13px;">Fecha</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['fecha'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Código Agencia</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['agencia'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Nombre Agencia</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['nombre_agencia'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Terminal</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['terminal'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 10px 14px; background-color: #eef2ff; color: #405189; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px;">Horario programado</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Entrada AM</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['entrada_am_programada'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Salida AM</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['salida_am_programada'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Entrada PM</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['entrada_pm_programada'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Salida PM</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['salida_pm_programada'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding: 10px 14px; background-color: #e8f7f4; color: #0f766e; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px;">Marcas reales capturadas</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Entrada AM real</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['entrada_real'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Salida AM real</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['salida_am_real'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Entrada PM real</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['entrada_pm_real'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Salida PM real</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['salida_real'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Minutos tarde</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['minutos_tarde'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Minutos salida antes</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['minutos_salida_antes'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Fuente</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['fuente'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Estado</td>
                                    <td style="padding: 12px 14px; color: {{ ($data['estado'] ?? '') === 'INCUMPLE' ? '#dc3545' : '#0ab39c' }}; font-size: 13px; font-weight: 700;">{{ $data['estado'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 14px; background-color: #f8f9fb; color: #878a99; font-size: 13px;">Observaciones</td>
                                    <td style="padding: 12px 14px; color: #212529; font-size: 13px;">{{ $data['observaciones'] ?? '-' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f3f6f9; border-top: 1px solid #e9ebec; padding: 18px 34px; text-align: center;">
                            <p style="margin: 0; color: #878a99; font-size: 12px; line-height: 1.6;">
                                Mensaje automático generado por el ERP de BSH Support.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

