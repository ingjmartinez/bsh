<?php

namespace App\Services;

use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\FaltantesController;
use App\Http\Controllers\PagoAOtraEmpresaController;
use App\Http\Controllers\PagoMismaEmpresaController;
use App\Http\Controllers\PagoPorOtraEmpresaController;
use App\Http\Controllers\PaqueticoController;
use App\Http\Controllers\PremioController;
use App\Http\Controllers\RecargasController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\VentasProductosController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutoProcesoService
{
    public function execute(string $sistema, string $fecha, int $maxSeconds = 300): array
    {
        $startedAt = microtime(true);
        $sistema = strtolower($sistema);

        if (!in_array($sistema, ['lotobet', 'lotonet'], true)) {
            return [
                'ok' => false,
                'message' => 'Sistema no soportado',
                'details' => [],
            ];
        }

        $details = [];

        $tokenStep = $this->runTokenStep($sistema);
        $details[] = $tokenStep;

        if (empty($tokenStep['ok'])) {
            return $this->buildResult($details, $startedAt);
        }

        $modules = $this->getModules($sistema);
        $totalModules = count($modules);

        for ($i = 0; $i < $totalModules; $i++) {
            $module = $modules[$i];
            if ($this->hasTimedOut($startedAt, $maxSeconds)) {
                for ($j = $i; $j < $totalModules; $j++) {
                    $details[] = [
                        'modulo' => $modules[$j]['modulo'],
                        'ok' => false,
                        'message' => "No ejecutado: proceso cancelado por tiempo limite ({$maxSeconds}s). Ejecutar manualmente este modulo.",
                        'total' => null,
                        'no_data' => false,
                        'timed_out' => true,
                    ];
                }
                break;
            }

            $details[] = $this->runModule($module['modulo'], $module['controller'], $module['method'], $fecha);
        }

        return $this->buildResult($details, $startedAt);
    }

    private function runTokenStep(string $sistema): array
    {
        $method = $sistema === 'lotobet' ? 'generateToken' : 'iniciarSession';

        return $this->runModule('Token', TokenController::class, $method, null);
    }

    private function runModule(string $modulo, string $controllerClass, string $method, ?string $fecha): array
    {
        try {
            $controller = app($controllerClass);
            $request = Request::create('/', 'GET', $fecha ? ['fecha' => $fecha] : []);
            $reflection = new \ReflectionMethod($controller, $method);
            $response = $reflection->getNumberOfParameters() > 0
                ? $controller->{$method}($request)
                : $controller->{$method}();

            if (!$response instanceof JsonResponse) {
                return [
                    'modulo' => $modulo,
                    'ok' => false,
                    'message' => 'Respuesta no valida del controlador',
                    'total' => null,
                ];
            }

            $statusCode = $response->getStatusCode();
            $payload = $response->getData(true);

            $total = isset($payload['total']) && is_numeric($payload['total'])
                ? (int) $payload['total']
                : ($payload['total'] ?? null);
            $message = $payload['message'] ?? $payload['success'] ?? $payload['error'] ?? 'Sin mensaje';
            $hasNoData = $this->isNoDataResult($modulo, $statusCode, $payload, (string) $message, $total);
            $ok = $statusCode < 400 && !isset($payload['error']) && !$hasNoData;

            if ($hasNoData) {
                $message = 'API sin datos: no se guardaron datos.';
            }

            return [
                'modulo' => $modulo,
                'ok' => $ok,
                'message' => (string) $message,
                'total' => $total,
                'no_data' => $hasNoData,
                'timed_out' => false,
            ];
        } catch (\Throwable $e) {
            return [
                'modulo' => $modulo,
                'ok' => false,
                'message' => $e->getMessage(),
                'total' => null,
                'no_data' => false,
                'timed_out' => false,
            ];
        }
    }

    private function hasTimedOut(float $startedAt, int $maxSeconds): bool
    {
        return (microtime(true) - $startedAt) >= $maxSeconds;
    }

    private function buildResult(array $details, float $startedAt): array
    {
        $timedOut = collect($details)->contains(fn (array $row) => !empty($row['timed_out']));
        $noDataCount = collect($details)->where('no_data', true)->count();
        $okCount = collect($details)->where('ok', true)->count();
        $errorCount = collect($details)
            ->reject(fn (array $row) => !empty($row['ok']) || !empty($row['no_data']))
            ->count();
        $elapsedSeconds = (int) floor(microtime(true) - $startedAt);

        return [
            'ok' => $errorCount === 0 && !$timedOut,
            'message' => $timedOut
                ? 'Proceso cancelado por tiempo limite'
                : ($errorCount === 0 ? 'Proceso completado' : 'Proceso completado con errores'),
            'ok_count' => $okCount,
            'error_count' => $errorCount,
            'no_data_count' => $noDataCount,
            'timed_out' => $timedOut,
            'elapsed_seconds' => $elapsedSeconds,
            'details' => $details,
        ];
    }

    private function isNoDataResult(string $modulo, int $statusCode, array $payload, string $message, mixed $total): bool
    {
        if ($modulo === 'Token' || $statusCode >= 400 || isset($payload['error'])) {
            return false;
        }

        if (is_numeric($total) && (int) $total === 0) {
            return true;
        }

        if (isset($payload['data']) && is_array($payload['data']) && count($payload['data']) === 0) {
            return true;
        }

        $normalized = strtolower(trim($message));
        $markers = [
            'sin datos',
            'no hay datos',
            'no se encontraron',
            'sin registros',
            '0 registros',
            'vacio',
            'vacío',
        ];

        foreach ($markers as $marker) {
            if (str_contains($normalized, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function getModules(string $sistema): array
    {
        if ($sistema === 'lotobet') {
            return [
                ['modulo' => 'Asistencias', 'controller' => AsistenciaController::class, 'method' => 'saveAsistenciasLotobet'],
                ['modulo' => 'Faltantes', 'controller' => FaltantesController::class, 'method' => 'saveFaltantesLotobet'],
                ['modulo' => 'Pagos a otra empresa', 'controller' => PagoAOtraEmpresaController::class, 'method' => 'savePagosLotobet'],
                ['modulo' => 'Pagos misma empresa', 'controller' => PagoMismaEmpresaController::class, 'method' => 'savePagosMismaEmpresaLotobet'],
                ['modulo' => 'Pagos por otra empresa', 'controller' => PagoPorOtraEmpresaController::class, 'method' => 'savePagosLotobet'],
                ['modulo' => 'Premios', 'controller' => PremioController::class, 'method' => 'savePremiosLotobet'],
                ['modulo' => 'Recargas', 'controller' => RecargasController::class, 'method' => 'saveRecargasLotobet'],
                ['modulo' => 'Ventas por producto', 'controller' => VentasProductosController::class, 'method' => 'saveVentasProductosLotobet'],
                ['modulo' => 'Ventas por usuario', 'controller' => VentasController::class, 'method' => 'saveVentasUsuariosLotobet'],
            ];
        }

        return [
            ['modulo' => 'Asistencias', 'controller' => AsistenciaController::class, 'method' => 'saveAsistenciasLotonet'],
            ['modulo' => 'Faltantes', 'controller' => FaltantesController::class, 'method' => 'saveFaltantesLotonet'],
            ['modulo' => 'Pagos a otra empresa', 'controller' => PagoAOtraEmpresaController::class, 'method' => 'savePagosLotonet'],
            ['modulo' => 'Pagos misma empresa', 'controller' => PagoMismaEmpresaController::class, 'method' => 'savePagosLotonet'],
            ['modulo' => 'Pagos por otra empresa', 'controller' => PagoPorOtraEmpresaController::class, 'method' => 'savePagosLotonet'],
            ['modulo' => 'Paquetico', 'controller' => PaqueticoController::class, 'method' => 'save'],
            ['modulo' => 'Premios', 'controller' => PremioController::class, 'method' => 'savePremiosLotonet'],
            ['modulo' => 'Recargas', 'controller' => RecargasController::class, 'method' => 'saveRecargasLotonet'],
            ['modulo' => 'Ventas por producto', 'controller' => VentasProductosController::class, 'method' => 'saveVentasProductosLotonet'],
            ['modulo' => 'Ventas por usuario', 'controller' => VentasController::class, 'method' => 'saveVentasUsuariosLotonet'],
        ];
    }
}
