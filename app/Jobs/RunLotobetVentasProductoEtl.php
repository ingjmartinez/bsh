<?php

namespace App\Jobs;

use App\Services\Etl\LotobetVentasProductoEtlService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunLotobetVentasProductoEtl implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function __construct(
        public string $fecha,
        public bool $dryRun = false,
        public int $chunkSize = 1000,
    ) {
    }

    public function handle(LotobetVentasProductoEtlService $service): void
    {
        $service->run($this->fecha, $this->dryRun, $this->chunkSize);
    }
}
