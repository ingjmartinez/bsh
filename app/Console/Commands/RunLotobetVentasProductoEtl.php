<?php

namespace App\Console\Commands;

use App\Jobs\RunLotobetVentasProductoEtl as RunLotobetVentasProductoEtlJob;
use App\Services\Etl\LotobetVentasProductoEtlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class RunLotobetVentasProductoEtl extends Command
{
    protected $signature = 'etl:lotobet-ventas-producto
        {fecha : Fecha a procesar en formato YYYY-MM-DD}
        {--queue : Despachar a la cola en vez de ejecutar inmediatamente}
        {--dry-run : Extraer y validar sin insertar}
        {--chunk=1000 : Cantidad de filas por lote}';

    protected $description = 'Ejecuta ETL auditable para ventas por producto de Lotobet';

    public function handle(LotobetVentasProductoEtlService $service): int
    {
        $fecha = (string) $this->argument('fecha');
        $validator = Validator::make(['fecha' => $fecha], [
            'fecha' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            $this->error('Fecha invalida. Use YYYY-MM-DD.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) $this->option('chunk'));

        if ($this->option('queue')) {
            RunLotobetVentasProductoEtlJob::dispatch($fecha, $dryRun, $chunkSize);
            $this->info('Job ETL despachado.');
            return self::SUCCESS;
        }

        $result = $service->run($fecha, $dryRun, $chunkSize);
        $this->info('ETL completado.');
        $this->line('Run ID: ' . $result['run_id']);
        $this->line('Extraidas: ' . $result['expected']);
        $this->line('Insertadas: ' . $result['inserted']);
        $this->line('Omitidas: ' . $result['skipped']);
        $this->line('Conflictos: ' . $result['failed']);

        return self::SUCCESS;
    }
}
