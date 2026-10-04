<?php

namespace App\Console\Commands;

use App\Services\Lotobet\LotobetIngestionService;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Throwable;

class BackfillLotobetPaymentProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lotobet:backfill-payment-products {fecha_inicio} {fecha_fin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Completa el producto de pagos Lotobet guardados usando la API';

    /**
     * Execute the console command.
     */
    public function handle(LotobetIngestionService $service): int
    {
        try {
            $inicio = CarbonImmutable::createFromFormat('!Y-m-d', $this->argument('fecha_inicio'));
            $fin = CarbonImmutable::createFromFormat('!Y-m-d', $this->argument('fecha_fin'));
        } catch (Throwable) {
            $this->error('Las fechas deben tener formato YYYY-MM-DD.');

            return self::FAILURE;
        }

        if ($inicio === false || $fin === false || $inicio->greaterThan($fin)) {
            $this->error('El rango de fechas no es válido.');

            return self::FAILURE;
        }

        $failed = false;
        foreach (CarbonPeriod::create($inicio, $fin) as $date) {
            $fecha = $date->format('Y-m-d');
            foreach (['pagos_misma_empresa', 'pagos_aotra_empresa'] as $module) {
                try {
                    $result = $service->backfillPaymentProducts($module, $fecha);
                    $this->line("{$fecha} {$module}: {$result['updated']} de {$result['matched']} pagos completados.");
                } catch (Throwable $exception) {
                    $failed = true;
                    $this->error("{$fecha} {$module}: {$exception->getMessage()}");
                }
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
