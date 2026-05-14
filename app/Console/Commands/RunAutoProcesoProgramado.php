<?php

namespace App\Console\Commands;

use App\Mail\AutoProcesoResumenMail;
use App\Models\AutoProcesoConfig;
use App\Services\AutoProcesoService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RunAutoProcesoProgramado extends Command
{
    protected $signature = 'auto-proceso:run-due {--force}';

    protected $description = 'Ejecuta auto proceso de lotobet y lotonet segun configuracion';

    public function handle(AutoProcesoService $service): int
    {
        $now = Carbon::now(config('app.timezone'));
        $currentTime = $now->format('H:i');
        $configs = AutoProcesoConfig::query()
            ->where('enabled', true)
            ->whereNotNull('hora')
            ->whereNotNull('correo')
            ->get();

        if ($configs->isEmpty()) {
            $this->info('No hay configuraciones habilitadas');
            return self::SUCCESS;
        }

        foreach ($configs as $config) {
            $alreadyRanToday = $config->last_run_at && $config->last_run_at->setTimezone(config('app.timezone'))->isSameDay($now);
            $isDueMinute = substr((string) $config->hora, 0, 5) === $currentTime;

            if (!$this->option('force')) {
                if (!$isDueMinute || $alreadyRanToday) {
                    continue;
                }
            }

            $fechaProceso = $config->process_date
                ? $config->process_date->format('Y-m-d')
                : $now->copy()->addDays((int) ($config->process_day_offset ?? 0))->format('Y-m-d');

            $this->info("Procesando {$config->sistema} para {$fechaProceso}");

            $maxSeconds = max(60, (int) ($config->max_seconds ?? 1800));
            try {
                $result = $service->execute((string) $config->sistema, $fechaProceso, $maxSeconds);
            } catch (\Throwable $e) {
                Log::error('Fallo fatal en auto proceso', [
                    'sistema' => (string) $config->sistema,
                    'fecha' => $fechaProceso,
                    'error' => $e->getMessage(),
                ]);

                $result = [
                    'ok' => false,
                    'ok_count' => 0,
                    'error_count' => 1,
                    'no_data_count' => 0,
                    'timed_out' => false,
                    'elapsed_seconds' => 0,
                    'details' => [[
                        'modulo' => 'Auto proceso',
                        'ok' => false,
                        'message' => $e->getMessage(),
                        'total' => null,
                        'no_data' => false,
                        'timed_out' => false,
                    ]],
                ];
            }

            $this->storeAndSendSummary($config, $fechaProceso, $maxSeconds, $result);
        }

        return self::SUCCESS;
    }

    private function storeAndSendSummary(AutoProcesoConfig $config, string $fechaProceso, int $maxSeconds, array $result): void
    {
        Log::info('Auto proceso ejecutado', [
            'sistema' => (string) $config->sistema,
            'fecha' => $fechaProceso,
            'ok_count' => $result['ok_count'] ?? 0,
            'error_count' => $result['error_count'] ?? 0,
            'no_data_count' => $result['no_data_count'] ?? 0,
            'timed_out' => (bool) ($result['timed_out'] ?? false),
            'elapsed_seconds' => $result['elapsed_seconds'] ?? null,
            'max_seconds' => $maxSeconds,
        ]);

        $config->update([
            'last_run_at' => now(),
            'last_status' => !empty($result['ok']) ? 'ok' : 'error',
            'last_summary' => [
                'fecha' => $fechaProceso,
                'ok_count' => $result['ok_count'] ?? 0,
                'error_count' => $result['error_count'] ?? 0,
                'no_data_count' => $result['no_data_count'] ?? 0,
                'timed_out' => (bool) ($result['timed_out'] ?? false),
                'elapsed_seconds' => $result['elapsed_seconds'] ?? null,
                'max_seconds' => $maxSeconds,
            ],
        ]);

        try {
            Log::info('Intentando enviar correo de auto proceso', [
                'sistema' => (string) $config->sistema,
                'correo' => (string) $config->correo,
                'fecha' => $fechaProceso,
            ]);

            Mail::to((string) $config->correo)->send(new AutoProcesoResumenMail([
                'sistema' => (string) $config->sistema,
                'fecha' => $fechaProceso,
                'estado' => !empty($result['ok']) ? 'OK' : 'Con errores',
                'ok_count' => $result['ok_count'] ?? 0,
                'error_count' => $result['error_count'] ?? 0,
                'no_data_count' => $result['no_data_count'] ?? 0,
                'timed_out' => (bool) ($result['timed_out'] ?? false),
                'elapsed_seconds' => $result['elapsed_seconds'] ?? null,
                'max_seconds' => $maxSeconds,
                'detalles' => $result['details'] ?? [],
            ]));

            Log::info('Correo de auto proceso enviado', [
                'sistema' => (string) $config->sistema,
                'correo' => (string) $config->correo,
                'fecha' => $fechaProceso,
            ]);
        } catch (\Throwable $e) {
            Log::error('Fallo al enviar correo de auto proceso', [
                'sistema' => (string) $config->sistema,
                'correo' => (string) $config->correo,
                'fecha' => $fechaProceso,
                'error' => $e->getMessage(),
            ]);

            $summary = (array) ($config->last_summary ?? []);
            $summary['mail_error'] = $e->getMessage();

            $config->update([
                'last_status' => 'error',
                'last_summary' => $summary,
            ]);
        }
    }
}
