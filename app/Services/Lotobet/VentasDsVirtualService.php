<?php

namespace App\Services\Lotobet;

use App\Models\VentaDsVirtual;
use Illuminate\Support\Collection;
use RuntimeException;

class VentasDsVirtualService
{
    public function __construct(private readonly LotobetSessionService $lotobetSession) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function fetch(string $fecha): Collection
    {
        $response = $this->lotobetSession->getVentasDsVirtual($fecha);
        $content = $response['Content'] ?? null;

        if (! is_array($content)) {
            throw new RuntimeException('La API de Ventas DS Virtual devolvió una respuesta inválida.');
        }

        return collect($content)
            ->filter(fn (mixed $row): bool => is_array($row))
            ->map(fn (array $row): array => $this->normalizeRow($row, $fecha))
            ->values();
    }

    /**
     * @return array{received: int, stored: int}
     */
    public function sync(string $fecha): array
    {
        $rows = $this->fetch($fecha);

        if ($rows->isNotEmpty()) {
            VentaDsVirtual::query()->upsert(
                $rows->all(),
                ['fecha', 'consorcio_id', 'agencia_id', 'proveedor_id'],
                ['ventas', 'premios_pagados', 'premios', 'proveedor_nombre', 'updated_at']
            );
        }

        return [
            'received' => $rows->count(),
            'stored' => VentaDsVirtual::query()->whereDate('fecha', $fecha)->count(),
        ];
    }

    public function delete(string $fecha): int
    {
        return VentaDsVirtual::query()->whereDate('fecha', $fecha)->delete();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row, string $requestedDate): array
    {
        $consorcioId = (int) ($row['consorcio_id'] ?? 0);
        $agenciaId = trim((string) ($row['agencia_id'] ?? ''));
        $proveedorId = (int) ($row['proveedor_id'] ?? 0);

        if ($consorcioId < 1 || $agenciaId === '' || $proveedorId < 1) {
            throw new RuntimeException('La API de Ventas DS Virtual devolvió un registro incompleto.');
        }

        $now = now();

        return [
            'consorcio_id' => $consorcioId,
            'fecha' => (string) ($row['fecha'] ?? $requestedDate),
            'agencia_id' => $agenciaId,
            'ventas' => round((float) ($row['ventas'] ?? 0), 2),
            'premios_pagados' => round((float) ($row['premios_pagados'] ?? 0), 2),
            'proveedor_id' => $proveedorId,
            'premios' => round((float) ($row['premios'] ?? 0), 2),
            'proveedor_nombre' => trim((string) ($row['proveedor_nombre'] ?? 'DS Virtual')),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
