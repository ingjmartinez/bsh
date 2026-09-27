<?php

namespace App\Support;

use Carbon\Carbon;

class LotedomRowMapper
{
    public static function asistenciaParaConsulta(array $row, string $fecha): array
    {
        $row['fecha'] = $fecha;
        $row['identificacion'] = self::digitsOnly($row['identificacion'] ?? null);
        $row['entrada'] = self::timeValue($row['entrada'] ?? null);
        $row['salida'] = self::timeValue($row['salida'] ?? null);

        return $row;
    }

    public static function asistencia(array $row, string $fecha): array
    {
        return [
            'fecha' => $fecha,
            'consorcio' => self::stringValue($row['consorcio'] ?? null),
            'agencia' => self::stringValue($row['agencia'] ?? null),
            'usuario' => self::stringValue($row['usuario'] ?? null),
            'entrada' => self::dateTimeValue($row['entrada'] ?? null),
            'salida' => self::dateTimeValue($row['salida'] ?? null),
            'identificacion' => self::digitsOnly($row['identificacion'] ?? null),
            'username' => self::stringValue($row['username'] ?? null),
            'banca' => self::stringValue($row['banca'] ?? null),
            'terminal' => self::stringValue($row['terminal'] ?? null),
            'salida_inactividad' => self::stringValue($row['salida_inactividad'] ?? null),
            'turno' => self::stringValue($row['turno'] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public static function faltante(array $row, string $fecha): array
    {
        return [
            'agencia_id' => self::stringValue($row['agencia_id'] ?? $row['codigo'] ?? null),
            'fecha' => $row['fecha'] ?? $fecha,
            'monto' => abs(self::decimalValue($row['monto'] ?? 0)),
            'motivo' => self::stringValue($row['motivo'] ?? null),
            'observacion' => self::stringValue($row['descripcion'] ?? $row['observacion'] ?? $row['identificacion'] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public static function pago(array $row, string $fecha): array
    {
        return [
            'agencia_id' => self::stringValue(
                $row['agencia_id']
                    ?? $row['venta_terminal_codigo']
                    ?? $row['pago_terminal_codigo']
                    ?? $row['codigo']
                    ?? null
            ),
            'monto' => self::decimalValue($row['monto'] ?? $row['importe'] ?? 0),
            'fecha' => $row['fecha'] ?? $fecha,
            'cedula' => self::normalizeCedula($row['cedula'] ?? $row['identificacion'] ?? null),
            'tipo_pago' => self::stringValue(
                $row['tipo_pago'] ?? $row['plataforma'] ?? $row['producto_nombre'] ?? $row['descripcion'] ?? null
            ),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public static function premio(array $row, string $fecha): array
    {
        return [
            'agencia_id' => self::stringValue($row['agencia_id'] ?? $row['codigo'] ?? null),
            'producto_id' => self::intValue($row['producto_id'] ?? null),
            'monto' => self::decimalValue($row['monto'] ?? 0),
            'fecha' => $row['fecha'] ?? $fecha,
            'cedula' => self::normalizeCedula($row['cedula'] ?? $row['identificacion'] ?? null),
            'sorteo_id' => self::intValue($row['sorteo_id'] ?? $row['numero_sorteo'] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public static function recarga(array $row, string $fecha): array
    {
        return [
            'agencia_id' => self::stringValue($row['agencia_id'] ?? $row['codigo'] ?? null),
            'monto' => self::decimalValue($row['monto'] ?? 0),
            'fecha' => $row['fecha'] ?? $fecha,
            'cedula' => self::normalizeCedula($row['cedula'] ?? $row['identificacion'] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public static function ventaProducto(array $row, string $fecha): array
    {
        $terminalCodigo = self::stringValue($row['terminal_codigo'] ?? $row['agencia_id'] ?? $row['codigo'] ?? null);
        $juegoId = self::intValue($row['juego_id'] ?? $row['producto_id'] ?? null);
        $juegoDesc = self::stringValue($row['juego_desc'] ?? $row['descripcion'] ?? null);
        $montoJugado = self::decimalValue($row['monto_jugado'] ?? $row['monto'] ?? 0);

        return [
            'consorcio_id' => self::intValue($row['consorcio_id'] ?? null),
            'consorcio_codigo' => self::stringValue($row['consorcio_codigo'] ?? null),
            'consorcio_desc' => self::stringValue($row['consorcio_desc'] ?? null),
            'banca_id' => self::intValue($row['banca_id'] ?? null),
            'banca_desc' => self::stringValue($row['banca_desc'] ?? null),
            'agencia_id' => $terminalCodigo,
            'terminal_codigo' => $terminalCodigo,
            'terminal_desc' => self::stringValue($row['terminal_desc'] ?? null),
            'loteria_id' => self::intValue($row['loteria_id'] ?? null),
            'loteria_desc' => self::stringValue($row['loteria_desc'] ?? null),
            'producto_id' => $juegoId,
            'juego_id' => $juegoId,
            'juego_prefijo' => self::stringValue($row['juego_prefijo'] ?? null),
            'juego_desc' => $juegoDesc,
            'descripcion' => $juegoDesc,
            'monto' => $montoJugado,
            'monto_jugado' => $montoJugado,
            'monto_pagado' => self::decimalValue($row['monto_pagado'] ?? 0),
            'monto_premiado' => self::decimalValue($row['monto_premiado'] ?? 0),
            'impuesto_retenido' => self::decimalValue($row['impuesto_retenido'] ?? 0),
            'fecha' => $row['fecha'] ?? $fecha,
            'sorteo_id' => self::intValue($row['sorteo_id'] ?? $row['numero_sorteo'] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private static function normalizeCedula(mixed $value): ?string
    {
        $cedula = preg_replace('/\D/', '', (string) $value);
        if ($cedula === '') {
            return null;
        }

        return str_pad(substr($cedula, 0, 11), 11, '0', STR_PAD_LEFT);
    }

    private static function dateTimeValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private static function digitsOnly(mixed $value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return $digits === '' ? null : $digits;
    }

    private static function timeValue(mixed $value): ?string
    {
        $dateTime = self::dateTimeValue($value);

        return $dateTime === null ? null : substr($dateTime, 11);
    }

    private static function stringValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function decimalValue(mixed $value): float
    {
        return (float) str_replace(',', '', (string) $value);
    }

    private static function intValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
