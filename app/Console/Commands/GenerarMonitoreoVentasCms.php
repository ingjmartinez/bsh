<?php

namespace App\Console\Commands;

use App\Services\MonitoreoVentasCmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerarMonitoreoVentasCms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoreo-ventas:generar {fecha? : Fecha de ventas YYYY-MM-DD}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera alertas CMS para agencias bajo los umbrales diarios de ventas';

    /**
     * Execute the console command.
     */
    public function handle(MonitoreoVentasCmsService $servicio): int
    {
        $fecha = $this->argument('fecha');
        if ($fecha !== null && (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || ! Carbon::hasFormat($fecha, 'Y-m-d'))) {
            $this->error('La fecha debe tener formato YYYY-MM-DD.');

            return self::FAILURE;
        }

        $generados = $servicio->generar($fecha ? Carbon::parse($fecha) : now()->subDay());
        $this->info("Alertas nuevas: {$generados}");

        return self::SUCCESS;
    }
}
