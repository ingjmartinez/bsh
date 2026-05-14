<?php

namespace App\Services\Etl;

use App\Services\Lotobet\LotobetSessionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class LotobetVentasProductoEtlService
{
    private const TABLE = 'ventas_producto_bet';

    private ?array $availableOptionalColumns = null;

    public function __construct(private readonly LotobetSessionService $lotobet)
    {
    }

    public function run(string $fecha, bool $dryRun = false, int $chunkSize = 1000): array
    {
        $runId = DB::table('etl_runs')->insertGetId([
            'tabla' => self::TABLE,
            'status' => 'running',
            'fecha_ini' => $fecha,
            'fecha_fin' => $fecha,
            'dry_run' => $dryRun,
            'chunk_size' => $chunkSize,
            'rows_expected' => null,
            'rows_migrated' => 0,
            'rows_failed' => 0,
            'rows_skipped' => 0,
            'last_offset' => 0,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $payload = $this->lotobet->getVentasProducto($fecha);
            $content = $payload['Content'] ?? [];

            if (!is_array($content)) {
                throw new \RuntimeException('Lotobet no devolvio un listado valido en Content.');
            }

            DB::table('etl_runs')->where('id', $runId)->update([
                'rows_expected' => count($content),
                'updated_at' => now(),
            ]);

            $inserted = 0;
            $failed = 0;
            $skipped = 0;
            $processed = 0;
            $batchNum = 0;

            if (!$dryRun) {
                DB::table(self::TABLE)->whereDate('fecha', $fecha)->delete();
            }

            foreach (array_chunk($content, $chunkSize) as $chunk) {
                $batchNum++;
                $rows = [];
                $chunkFailed = 0;

                foreach ($chunk as $item) {
                    $transformed = $this->transform($item, $fecha);
                    if (!$transformed['ok']) {
                        $chunkFailed++;
                        $this->recordConflict($runId, $item, $transformed['error']);
                        continue;
                    }

                    $rows[] = $transformed['row'];
                }

                $chunkInserted = 0;
                if (!$dryRun && $rows !== []) {
                    $chunkInserted = DB::table(self::TABLE)->insertOrIgnore($rows);
                }

                $chunkSkipped = max(0, count($rows) - $chunkInserted);
                $processed += count($chunk);
                $inserted += $chunkInserted;
                $failed += $chunkFailed;
                $skipped += $dryRun ? count($rows) : $chunkSkipped;

                DB::table('etl_run_items')->insert([
                    'etl_run_id' => $runId,
                    'batch_num' => $batchNum,
                    'status' => $chunkFailed > 0 ? 'partial' : 'done',
                    'rows_processed' => count($chunk),
                    'rows_inserted' => $dryRun ? 0 : $chunkInserted,
                    'rows_skipped' => $dryRun ? count($rows) : $chunkSkipped,
                    'error' => $chunkFailed > 0 ? "{$chunkFailed} fila(s) con conflicto" : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('etl_runs')->where('id', $runId)->update([
                    'rows_migrated' => $inserted,
                    'rows_failed' => $failed,
                    'rows_skipped' => $skipped,
                    'last_offset' => $processed,
                    'updated_at' => now(),
                ]);
            }

            $status = $failed > 0 ? 'done_with_conflicts' : 'done';

            DB::table('etl_runs')->where('id', $runId)->update([
                'status' => $status,
                'rows_migrated' => $inserted,
                'rows_failed' => $failed,
                'rows_skipped' => $skipped,
                'finished_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'ok' => true,
                'run_id' => $runId,
                'status' => $status,
                'expected' => count($content),
                'inserted' => $inserted,
                'failed' => $failed,
                'skipped' => $skipped,
                'dry_run' => $dryRun,
            ];
        } catch (Throwable $e) {
            DB::table('etl_runs')->where('id', $runId)->update([
                'status' => 'failed',
                'error' => Str::limit($e->getMessage(), 60000, ''),
                'finished_at' => now(),
                'updated_at' => now(),
            ]);

            throw $e;
        }
    }

    private function transform(array $item, string $fecha): array
    {
        $agenciaId = trim((string) ($item['agencia_id'] ?? ''));
        $productoId = $item['producto_id'] ?? null;
        $monto = $item['monto'] ?? null;
        $numeroSorteo = $item['numero_sorteo'] ?? null;
        $descripcion = trim((string) ($item['descripcion'] ?? ''));
        $fechaSorteo = trim((string) ($item['fecha_sorteo'] ?? ''));

        if ($agenciaId === '') {
            return ['ok' => false, 'error' => 'agencia_id vacio'];
        }

        if (!is_numeric($productoId)) {
            return ['ok' => false, 'error' => 'producto_id invalido'];
        }

        if (!is_numeric($monto)) {
            return ['ok' => false, 'error' => 'monto invalido'];
        }

        $sourceHash = hash('sha256', json_encode([
            'fecha' => $fecha,
            'consorcio_id' => isset($item['consorcio_id']) ? (string) $item['consorcio_id'] : null,
            'agencia_id' => $agenciaId,
            'producto_id' => (int) $productoId,
            'descripcion' => $descripcion,
            'monto' => number_format((float) $monto, 2, '.', ''),
            'numero_sorteo' => $numeroSorteo !== null ? (string) $numeroSorteo : null,
            'fecha_sorteo' => $fechaSorteo,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $row = [
            'agencia_id' => $agenciaId,
            'producto_id' => (int) $productoId,
            'monto' => (float) $monto,
            'fecha' => $fecha,
            'sorteo_id' => is_numeric($numeroSorteo) ? (int) $numeroSorteo : null,
            'source_hash' => $sourceHash,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $optionalColumns = [
            'consorcio_id' => $item['consorcio_id'] ?? null,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'fecha_sorteo' => $fechaSorteo !== '' ? $fechaSorteo : null,
            'comision' => is_numeric($item['comision'] ?? null) ? (float) $item['comision'] : null,
            'comision_supervisor' => is_numeric($item['comision_supervisor'] ?? null) ? (float) $item['comision_supervisor'] : null,
        ];

        foreach ($optionalColumns as $column => $value) {
            if (in_array($column, $this->availableOptionalColumns(), true)) {
                $row[$column] = $value;
            }
        }

        return [
            'ok' => true,
            'row' => $row,
        ];
    }

    private function availableOptionalColumns(): array
    {
        if ($this->availableOptionalColumns !== null) {
            return $this->availableOptionalColumns;
        }

        $columns = ['consorcio_id', 'descripcion', 'fecha_sorteo', 'comision', 'comision_supervisor'];

        return $this->availableOptionalColumns = array_values(array_filter(
            $columns,
            fn (string $column): bool => DB::getSchemaBuilder()->hasColumn(self::TABLE, $column)
        ));
    }

    private function recordConflict(int $runId, array $item, string $motivo): void
    {
        DB::table('etl_conflictos')->insert([
            'etl_run_id' => $runId,
            'tabla' => self::TABLE,
            'legacy_id' => isset($item['numero_sorteo']) ? (string) $item['numero_sorteo'] : null,
            'motivo' => $motivo,
            'data' => json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
