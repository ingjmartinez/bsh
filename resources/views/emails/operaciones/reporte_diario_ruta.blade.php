<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuadre Diario de Ruta</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <h2 style="margin-bottom: 8px;">Cuadre Diario de Ruta</h2>
    <p style="margin-top: 0;">Informe generado desde el modulo de Operaciones.</p>

    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%; max-width: 680px;">
        <tr>
            <th align="left">Fecha</th>
            <td>{{ $fecha }}</td>
        </tr>
        <tr>
            <th align="left">Ruta</th>
            <td>{{ $ruta }}</td>
        </tr>
        <tr>
            <th align="left">Operador</th>
            <td>{{ $operador }}</td>
        </tr>
        <tr>
            <th align="left">Monto Entregado</th>
            <td>{{ $entregado }}</td>
        </tr>
        <tr>
            <th align="left">Monto Procesado</th>
            <td>{{ $procesado }}</td>
        </tr>
        <tr>
            <th align="left">Gasto</th>
            <td>{{ $gasto }}</td>
        </tr>
        <tr>
            <th align="left">Diferencia</th>
            <td>{{ $diferencia }}</td>
        </tr>
        <tr>
            <th align="left">Observacion</th>
            <td>{{ $observacion !== '' ? $observacion : 'Sin observacion' }}</td>
        </tr>
    </table>
</body>
</html>
