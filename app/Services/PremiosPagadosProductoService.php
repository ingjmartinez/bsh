<?php

namespace App\Services;

use App\Models\Agencia;
use App\Models\CatalogoJuego;
use App\Models\PagoAOtraEmpresa;
use App\Models\PagoMismaEmpresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class PremiosPagadosProductoService
{
    /** @return array{grupos: array, resumen: array, fuentes_sin_producto: array, disponibilidad: array} */
    public function report(string $fechaInicio, string $fechaFin): array
    {
        $catalogo = CatalogoJuego::query()->orderBy('descripcion')->get()->keyBy('producto_id');
        $grupos = [];
        foreach (['tradicional' => 'Tradicionales', 'no tradicional' => 'No tradicionales', 'sin identificar' => 'Sin producto identificado'] as $tipo => $nombre) {
            $grupos[$tipo] = ['nombre' => $nombre, 'productos' => [], 'misma_empresa' => 0, 'otra_empresa' => 0, 'total' => 0];
        }

        $fuentesSinProducto = [];
        $registrosIncluidos = 0;
        foreach (['misma_empresa' => PagoMismaEmpresa::class, 'otra_empresa' => PagoAOtraEmpresa::class] as $fuente => $modelo) {
            $tieneProducto = Schema::hasColumn((new $modelo)->getTable(), 'producto_id');
            if (! $tieneProducto) {
                $fuentesSinProducto[] = $fuente;
            }

            $pagos = $modelo::query()
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->where(function (Builder $query): void {
                    $query->whereIn('agencia_id', Agencia::query()->select('terminal')->whereNotNull('terminal')->where('terminal', '<>', ''))
                        ->orWhereIn(
                            $query->getQuery()->raw('CAST(agencia_id AS DECIMAL(20, 0))'),
                            Agencia::query()->selectRaw('CAST(terminal AS DECIMAL(20, 0))')->whereRaw('CAST(terminal AS DECIMAL(20, 0)) > 0')
                        );
                })
                ->selectRaw('SUM(monto) AS total, COUNT(*) AS registros')
                ->addSelect('agencia_id')
                ->groupBy('agencia_id');

            if ($tieneProducto) {
                $pagos->addSelect('producto_id')->groupBy('producto_id');
            } else {
                $pagos->selectRaw('NULL AS producto_id');
            }

            foreach ($pagos->get() as $pago) {
                $registrosIncluidos += (int) $pago->registros;
                if ($pago->total === null) {
                    continue;
                }

                $producto = $catalogo->get($pago->producto_id);
                $tipo = strtolower(trim(str_replace('_', ' ', $producto?->tipo ?? '')));
                if (! in_array($tipo, ['tradicional', 'no tradicional'], true)) {
                    $tipo = 'sin identificar';
                }

                $clave = $pago->producto_id ?? 'sin_producto';
                $grupos[$tipo]['productos'][$clave] ??= [
                    'producto_id' => $pago->producto_id,
                    'nombre' => $producto?->descripcion ?? ($pago->producto_id === null ? 'Pagos sin producto informado' : 'Producto '.$pago->producto_id.' (sin clasificación)'),
                    'misma_empresa' => 0,
                    'otra_empresa' => 0,
                    'total' => 0,
                    'terminales' => [],
                ];
                $monto = round((float) $pago->total, 2);
                $terminal = trim((string) $pago->agencia_id);
                if (ctype_digit($terminal)) {
                    $terminal = ltrim($terminal, '0') ?: '0';
                }
                $grupos[$tipo]['productos'][$clave]['terminales'][$terminal] ??= [
                    'terminal' => $terminal,
                    'misma_empresa' => 0,
                    'otra_empresa' => 0,
                    'total' => 0,
                ];
                $grupos[$tipo]['productos'][$clave]['terminales'][$terminal][$fuente] += $monto;
                $grupos[$tipo]['productos'][$clave]['terminales'][$terminal]['total'] += $monto;
                $grupos[$tipo]['productos'][$clave][$fuente] += $monto;
                $grupos[$tipo]['productos'][$clave]['total'] += $monto;
                $grupos[$tipo][$fuente] += $monto;
                $grupos[$tipo]['total'] += $monto;
            }
        }

        foreach ($catalogo as $producto) {
            $tipo = strtolower(trim(str_replace('_', ' ', $producto->tipo ?? '')));
            if (! in_array($tipo, ['tradicional', 'no tradicional'], true)) {
                continue;
            }

            $grupos[$tipo]['productos'][$producto->producto_id] ??= [
                'producto_id' => $producto->producto_id,
                'nombre' => $producto->descripcion,
                'misma_empresa' => 0,
                'otra_empresa' => 0,
                'total' => 0,
                'terminales' => [],
            ];
        }

        $resumen = [
            'misma_empresa' => round(array_sum(array_column($grupos, 'misma_empresa')), 2),
            'otra_empresa' => round(array_sum(array_column($grupos, 'otra_empresa')), 2),
            'total' => round(array_sum(array_column($grupos, 'total')), 2),
        ];

        foreach ($grupos as $tipo => &$grupo) {
            $grupo['productos'] = collect($grupo['productos'])->sortBy('nombre')->values()->all();
            foreach ($grupo['productos'] as &$producto) {
                $producto['terminales'] = collect($producto['terminales'])->sortBy('terminal', SORT_NATURAL)->values()->all();
                if ($tipo !== 'sin identificar') {
                    foreach ($producto['terminales'] as &$terminal) {
                        foreach ($fuentesSinProducto as $fuente) {
                            $terminal[$fuente] = null;
                        }
                        if ($fuentesSinProducto !== []) {
                            $terminal['total'] = null;
                        }
                    }
                    unset($terminal);
                }
            }
            unset($producto);
            if ($tipo !== 'sin identificar') {
                foreach ($grupo['productos'] as &$producto) {
                    foreach ($fuentesSinProducto as $fuente) {
                        $producto[$fuente] = null;
                    }
                    if ($fuentesSinProducto !== []) {
                        $producto['total'] = null;
                    }
                }
                unset($producto);
                foreach ($fuentesSinProducto as $fuente) {
                    $grupo[$fuente] = null;
                }
                if ($fuentesSinProducto !== []) {
                    $grupo['total'] = null;
                }
            }
        }
        unset($grupo);

        $disponibilidad = [];
        if ($registrosIncluidos === 0) {
            foreach (['Pagos misma empresa' => PagoMismaEmpresa::class, 'Pagos a otra empresa' => PagoAOtraEmpresa::class] as $nombre => $modelo) {
                $fechas = $modelo::query()
                    ->selectRaw('MIN(fecha) AS fecha_inicio, MAX(fecha) AS fecha_fin')
                    ->selectRaw('COUNT(CASE WHEN fecha BETWEEN ? AND ? THEN 1 END) AS registros_periodo', [$fechaInicio, $fechaFin])
                    ->first();
                $disponibilidad[] = [
                    'nombre' => $nombre,
                    'fecha_inicio' => $fechas->fecha_inicio,
                    'fecha_fin' => $fechas->fecha_fin,
                    'registros_periodo' => (int) $fechas->registros_periodo,
                ];
            }
        }

        return [
            'grupos' => array_values($grupos),
            'resumen' => $resumen,
            'fuentes_sin_producto' => $fuentesSinProducto,
            'disponibilidad' => $disponibilidad,
        ];
    }
}
