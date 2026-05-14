<?php

namespace App\Services\Lotobet;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LotobetIngestionService
{
    private const MODULES = [
        'faltantes' => ['endpoint' => 'qmLJoQxThPKErmLtEG', 'table' => 'faltantes_bet'],
        'pagos_aotra_empresa' => ['endpoint' => 'XCu6kLrhpbrkYOIvt6', 'table' => 'pagos_aotra_empresa_bet'],
        'pagos_misma_empresa' => ['endpoint' => 'zGt9KSp2k3B87uDbcN', 'table' => 'pagos_misma_empresa_bet'],
        'pagos_porotra_empresa' => ['endpoint' => 'sKE9VduKjpdK6jXy9x', 'table' => 'pagos_porotra_empresa_bet'],
        'premios' => ['endpoint' => 'YhJ23fkZyVNDVy4ilB', 'table' => 'premios_bet'],
        'recargas' => ['endpoint' => 'drc0PcA35U7oMvsnz7', 'table' => 'recargas_bet'],
        'ventas_usuarios' => ['endpoint' => 'EQsEpamN7MuKb0Y7', 'table' => 'ventas_usuarios_bet'],
    ];

    public function __construct(private LotobetSessionService $session)
    {
    }

    public function save(string $module, string $fecha): array
    {
        $config = self::MODULES[$module] ?? null;
        if (!$config) {
            throw new InvalidArgumentException("Modulo Lotobet no soportado: {$module}");
        }

        $table = $config['table'];

        if (DB::table($table)->whereDate('fecha', $fecha)->exists()) {
            return [
                'message' => 'Ya hay data guardada en la fecha: ' . $fecha,
                'total' => 0,
            ];
        }

        $payload = $this->session->getReport($config['endpoint'], $fecha);
        $rows = $payload['Content'] ?? [];

        if (!is_array($rows)) {
            throw new \RuntimeException('Lotobet no devolvio el listado esperado.');
        }

        $data = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $mapped = $this->mapRow($module, $row, $fecha);
            if ($mapped !== null) {
                $data[] = $mapped;
            }
        }

        foreach (array_chunk($data, 5000) as $chunk) {
            DB::table($table)->insert($chunk);
        }

        return [
            'message' => 'Datos guardados correctamente. Total insertados: ' . count($data),
            'total' => count($data),
        ];
    }

    private function mapRow(string $module, array $row, string $fecha): ?array
    {
        return match ($module) {
            'faltantes' => [
                'agencia_id' => $this->stringValue($row['agencia_id'] ?? $row['agencia'] ?? null),
                'fecha' => $row['fecha'] ?? $fecha,
                'monto' => $this->decimalValue($row['monto'] ?? 0),
                'motivo' => $this->stringValue($row['motivo'] ?? null),
                'observacion' => $this->stringValue($row['descripcion'] ?? $row['observacion'] ?? $row['identificacion'] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'premios' => [
                'agencia_id' => $this->stringValue($row['agencia_id'] ?? $row['agencia'] ?? null),
                'producto_id' => $this->intValue($row['producto_id'] ?? null),
                'monto' => $this->decimalValue($row['monto'] ?? 0),
                'fecha' => $row['fecha'] ?? $fecha,
                'cedula' => $this->normalizeCedula($row['cedula'] ?? $row['identificacion'] ?? null),
                'sorteo_id' => $this->intValue($row['sorteo_id'] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'recargas', 'ventas_usuarios' => [
                'agencia_id' => $this->stringValue($row['agencia_id'] ?? $row['agencia'] ?? null),
                'cedula' => $this->normalizeCedula($row['cedula'] ?? $row['identificacion'] ?? null),
                'monto' => $this->decimalValue($row['monto'] ?? 0),
                'fecha' => $row['fecha'] ?? $fecha,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'pagos_aotra_empresa', 'pagos_misma_empresa', 'pagos_porotra_empresa' => [
                'agencia_id' => $this->stringValue($row['agencia_id'] ?? $row['agencia'] ?? null),
                'monto' => $this->decimalValue($row['monto'] ?? $row['importe'] ?? 0),
                'fecha' => $row['fecha'] ?? $fecha,
                'cedula' => $this->normalizeCedula($row['cedula'] ?? $row['identificacion'] ?? null),
                'tipo_pago' => $this->stringValue($row['tipo_pago'] ?? $row['plataforma_pago'] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            default => null,
        };
    }

    private function normalizeCedula(mixed $value): ?string
    {
        $cedula = preg_replace('/\D/', '', (string) $value);
        if ($cedula === '') {
            return null;
        }

        return str_pad(substr($cedula, 0, 11), 11, '0', STR_PAD_LEFT);
    }

    private function stringValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function decimalValue(mixed $value): float
    {
        return (float) str_replace(',', '', (string) $value);
    }

    private function intValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
