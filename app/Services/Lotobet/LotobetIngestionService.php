<?php

namespace App\Services\Lotobet;

use App\Support\InicioVentasCache;
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

    public function __construct(private LotobetSessionService $session) {}

    public function save(string $module, string $fecha): array
    {
        $config = self::MODULES[$module] ?? null;
        if (! $config) {
            throw new InvalidArgumentException("Modulo Lotobet no soportado: {$module}");
        }

        $table = $config['table'];

        if (DB::table($table)->whereDate('fecha', $fecha)->exists()) {
            return [
                'message' => 'Ya hay data guardada en la fecha: '.$fecha,
                'total' => 0,
                'table' => $table,
                'fecha' => $fecha,
            ];
        }

        $payload = $this->session->getReport($config['endpoint'], $fecha);
        $rows = $payload['Content'] ?? [];

        if (! is_array($rows)) {
            throw new \RuntimeException('Lotobet no devolvio el listado esperado.');
        }

        $data = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $mapped = $this->mapRow($module, $row, $fecha);
            if ($mapped !== null) {
                $data[] = $mapped;
            }
        }

        DB::transaction(function () use ($table, $data) {
            foreach (array_chunk($data, 5000) as $chunk) {
                DB::table($table)->insert($chunk);
            }
        });

        InicioVentasCache::bust();

        return [
            'message' => 'Datos guardados correctamente. Total insertados: '.count($data),
            'total' => count($data),
            'table' => $table,
            'fecha' => $fecha,
        ];
    }

    /** @return array{matched: int, updated: int} */
    public function backfillPaymentProducts(string $module, string $fecha): array
    {
        if (! in_array($module, ['pagos_misma_empresa', 'pagos_aotra_empresa'], true)) {
            throw new InvalidArgumentException("Modulo de pagos no soportado: {$module}");
        }

        $config = self::MODULES[$module];
        $rows = $this->session->getReport($config['endpoint'], $fecha)['Content'] ?? [];
        if (! is_array($rows)) {
            throw new \RuntimeException('La API no devolvió un listado de pagos válido.');
        }

        $saved = DB::table($config['table'])->whereDate('fecha', $fecha)->orderBy('id')->get();
        if ($saved->count() !== count($rows)) {
            throw new \RuntimeException("Cantidad de pagos diferente en {$module} para {$fecha}.");
        }

        $updates = [];
        foreach ($saved as $index => $payment) {
            if (! is_array($rows[$index])) {
                throw new \RuntimeException("Registro inválido en {$module} para {$fecha}.");
            }

            $source = $this->mapRow($module, $rows[$index], $fecha);
            if ($source === null
                || trim((string) $payment->agencia_id) !== $source['agencia_id']
                || (string) $payment->fecha !== (string) $source['fecha']
                || round((float) $payment->monto, 2) !== round((float) $source['monto'], 2)
                || ($payment->producto_id !== null && (int) $payment->producto_id !== $source['producto_id'])) {
                throw new \RuntimeException("Los pagos guardados no coinciden con la API en {$module} para {$fecha}, fila ".($index + 1).'.');
            }

            if ($payment->producto_id === null && $source['producto_id'] !== null) {
                $updates[] = ['id' => $payment->id, 'producto_id' => $source['producto_id']];
            }
        }

        DB::transaction(function () use ($config, $updates): void {
            foreach (array_chunk($updates, 200) as $chunk) {
                $cases = [];
                $bindings = [];
                $ids = [];
                foreach ($chunk as $update) {
                    $cases[] = 'WHEN ? THEN ?';
                    $bindings[] = $update['id'];
                    $bindings[] = $update['producto_id'];
                    $ids[] = $update['id'];
                }

                $placeholders = implode(', ', array_fill(0, count($ids), '?'));
                DB::update(
                    "UPDATE {$config['table']} SET producto_id = CASE id ".implode(' ', $cases)." ELSE producto_id END WHERE id IN ({$placeholders}) AND producto_id IS NULL",
                    [...$bindings, ...$ids]
                );
            }
        });

        return ['matched' => $saved->count(), 'updated' => count($updates)];
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
            'recargas' => [
                'agencia_id' => $this->stringValue($row['agencia_id'] ?? $row['agencia'] ?? null),
                'cedula' => $this->normalizeCedula($row['cedula'] ?? $row['identificacion'] ?? null),
                'monto' => $this->decimalValue($row['monto'] ?? 0),
                'fecha' => $row['fecha'] ?? $fecha,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'ventas_usuarios' => [
                'consorcio_id' => $this->intValue($this->rowValue($row, ['consorcio_id', 'ConsorcioId', 'ConsorcioID', 'consorcio', 'Consorcio'])),
                'agencia_id' => $this->stringValue($this->rowValue($row, ['agencia_id', 'AgenciaId', 'AgenciaID', 'agencia', 'Agencia'])),
                'producto_id' => $productoId = $this->intValue($this->rowValue($row, ['producto_id', 'ProductoId', 'ProductoID', 'producto', 'Producto'])),
                'descripcion' => $productoId === -1 ? 'RECARGAS' : $this->stringValue($this->rowValue($row, ['descripcion', 'Descripcion', 'descripción', 'Descripción'])),
                'tipo' => $this->stringValue($this->rowValue($row, ['tipo', 'Tipo'])),
                'cedula' => $this->normalizeCedula($this->rowValue($row, ['cedula', 'Cedula', 'cédula', 'Cédula', 'identificacion', 'Identificacion', 'Identificación'])),
                'monto' => $this->decimalValue($this->rowValue($row, ['monto', 'Monto']) ?? 0),
                'fecha' => $this->rowValue($row, ['fecha', 'Fecha']) ?? $fecha,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'pagos_aotra_empresa', 'pagos_misma_empresa', 'pagos_porotra_empresa' => [
                'agencia_id' => $this->stringValue($row['agencia_id'] ?? $row['agencia'] ?? null),
                ...($module === 'pagos_porotra_empresa' ? [] : [
                    'producto_id' => $this->intValue($this->rowValue($row, ['producto_id', 'id_producto', 'ProductoId', 'ProductoID', 'producto', 'Producto'])),
                ]),
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

    private function rowValue(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }
}
