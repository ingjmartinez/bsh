<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GerencialController extends Controller
{
    public function index(Request $request)
    {
        $anio = $this->normalizeYear($request->query('anio'));
        $mesInicio = $this->normalizeMonth($request->query('mes_inicio'));
        $mesFin = $this->normalizeMonth($request->query('mes_fin'));
        $configuracion = $this->resolveThresholdConfig($request);

        return view('gerencia.gerencial', [
            'anioSeleccionado' => $anio,
            'mesInicioSeleccionado' => $mesInicio,
            'mesFinSeleccionado' => $mesFin,
            'configuracionClasificacion' => $configuracion,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $anio = $this->normalizeYear($request->query('anio'));
        $mesInicio = $this->normalizeMonth($request->query('mes_inicio'));
        $mesFin = $this->normalizeMonth($request->query('mes_fin'));
        $configuracion = $this->resolveThresholdConfig($request);

        if ($mesInicio === null || $mesFin === null || $mesInicio === $mesFin) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'anio' => $anio,
                    'mes_inicio' => $mesInicio,
                    'mes_fin' => $mesFin,
                    'configuracion' => $configuracion,
                ],
            ]);
        }

        set_time_limit(300);

        $sql = <<<'SQL'
WITH ventas_agencia_unificadas AS (
    SELECT
        agencia_id,
        SUM(monto) AS monto,
        MONTH(fecha) AS mes
    FROM vt_usuarios_bet
    WHERE YEAR(fecha) = ?
      AND MONTH(fecha) IN (?, ?)
    GROUP BY agencia_id, MONTH(fecha)

    UNION ALL

    SELECT
        agencia_id,
        SUM(monto) AS monto,
        MONTH(fecha) AS mes
    FROM vt_usuarios_net
    WHERE YEAR(fecha) = ?
      AND MONTH(fecha) IN (?, ?)
    GROUP BY agencia_id, MONTH(fecha)
),
ventas_cedula_unificadas AS (
    SELECT
        cedula,
        SUM(monto) AS monto,
        MONTH(fecha) AS mes
    FROM vt_usuarios_bet
    WHERE YEAR(fecha) = ?
      AND MONTH(fecha) IN (?, ?)
    GROUP BY cedula, MONTH(fecha)

    UNION ALL

    SELECT
        cedula,
        SUM(monto) AS monto,
        MONTH(fecha) AS mes
    FROM vt_usuarios_net
    WHERE YEAR(fecha) = ?
      AND MONTH(fecha) IN (?, ?)
    GROUP BY cedula, MONTH(fecha)
),
agencias_clasificadas AS (
    SELECT
        'AGENCIA' AS Tipo_Conteo,
        mes,
        agencia_id AS identificador,
        SUM(monto) AS total_ventas,
        CASE
            WHEN SUM(monto) >= ? THEN 'A'
            WHEN SUM(monto) >= ? THEN 'B'
            WHEN SUM(monto) >= ? THEN 'C'
            ELSE 'D'
        END AS Clasificacion
    FROM ventas_agencia_unificadas
    GROUP BY mes, agencia_id
),
conteo_agencias AS (
    SELECT
        Tipo_Conteo,
        Clasificacion,
        SUM(CASE WHEN mes = ? THEN 1 ELSE 0 END) AS Conteo_Mes_Inicio,
        SUM(CASE WHEN mes = ? THEN 1 ELSE 0 END) AS Conteo_Mes_Fin
    FROM agencias_clasificadas
    WHERE Clasificacion IN ('A', 'B', 'C', 'D')
    GROUP BY Tipo_Conteo, Clasificacion
),
cedulas_clasificadas AS (
    SELECT
        'AGENTE' AS Tipo_Conteo,
        mes,
        cedula AS identificador,
        SUM(monto) AS total_ventas,
        CASE
            WHEN SUM(monto) >= ? THEN 'A'
            WHEN SUM(monto) >= ? THEN 'B'
            WHEN SUM(monto) >= ? THEN 'C'
            ELSE 'D'
        END AS Clasificacion
    FROM ventas_cedula_unificadas
    GROUP BY mes, cedula
),
conteo_cedulas AS (
    SELECT
        Tipo_Conteo,
        Clasificacion,
        SUM(CASE WHEN mes = ? THEN 1 ELSE 0 END) AS Conteo_Mes_Inicio,
        SUM(CASE WHEN mes = ? THEN 1 ELSE 0 END) AS Conteo_Mes_Fin
    FROM cedulas_clasificadas
    WHERE Clasificacion IN ('A', 'B', 'C', 'D')
    GROUP BY Tipo_Conteo, Clasificacion
)
SELECT
    Tipo_Conteo,
    Clasificacion,
    Conteo_Mes_Inicio,
    Conteo_Mes_Fin,
    (Conteo_Mes_Fin - Conteo_Mes_Inicio) AS Crecimiento,
    CASE
        WHEN Conteo_Mes_Inicio = 0 THEN NULL
        ELSE ROUND(((Conteo_Mes_Fin - Conteo_Mes_Inicio) / Conteo_Mes_Inicio) * 100, 2)
    END AS Porc_Crecimiento
FROM (
    SELECT * FROM conteo_agencias
    UNION ALL
    SELECT * FROM conteo_cedulas
) t
ORDER BY
    FIELD(Tipo_Conteo, 'AGENCIA', 'AGENTE'),
    FIELD(Clasificacion, 'A', 'B', 'C', 'D')
SQL;

        $bindings = [
            $anio,
            $mesInicio,
            $mesFin,
            $anio,
            $mesInicio,
            $mesFin,
            $anio,
            $mesInicio,
            $mesFin,
            $anio,
            $mesInicio,
            $mesFin,
            $configuracion['agencia']['A'],
            $configuracion['agencia']['B'],
            $configuracion['agencia']['C'],
            $mesInicio,
            $mesFin,
            $configuracion['agente']['A'],
            $configuracion['agente']['B'],
            $configuracion['agente']['C'],
            $mesInicio,
            $mesFin,
        ];

        $resultados = collect(DB::select($sql, $bindings))
            ->map(function ($row) {
                return [
                    'tipo_conteo' => (string) ($row->Tipo_Conteo ?? ''),
                    'clasificacion' => (string) ($row->Clasificacion ?? ''),
                    'conteo_mes_inicio' => (int) ($row->Conteo_Mes_Inicio ?? 0),
                    'conteo_mes_fin' => (int) ($row->Conteo_Mes_Fin ?? 0),
                    'crecimiento' => (int) ($row->Crecimiento ?? 0),
                    'porc_crecimiento' => $row->Porc_Crecimiento !== null
                        ? (float) $row->Porc_Crecimiento
                        : null,
                ];
            })
            ->values();

        $sqlTransicionesAgencias = <<<'SQL'
WITH ventas_agencia_unificadas AS (
    SELECT
        agencia_id,
        SUM(monto) AS monto,
        MONTH(fecha) AS mes
    FROM vt_usuarios_bet
    WHERE YEAR(fecha) = ?
      AND MONTH(fecha) IN (?, ?)
    GROUP BY agencia_id, MONTH(fecha)

    UNION ALL

    SELECT
        agencia_id,
        SUM(monto) AS monto,
        MONTH(fecha) AS mes
    FROM vt_usuarios_net
    WHERE YEAR(fecha) = ?
      AND MONTH(fecha) IN (?, ?)
    GROUP BY agencia_id, MONTH(fecha)
),
agencias_clasificadas AS (
    SELECT
        agencia_id,
        mes,
        CASE
            WHEN SUM(monto) >= ? THEN 'A'
            WHEN SUM(monto) >= ? THEN 'B'
            WHEN SUM(monto) >= ? THEN 'C'
            ELSE 'D'
        END AS categoria
    FROM ventas_agencia_unificadas
    GROUP BY agencia_id, mes
),
mes_inicio AS (
    SELECT agencia_id, categoria AS categoria_inicio
    FROM agencias_clasificadas
    WHERE mes = ?
),
mes_fin AS (
    SELECT agencia_id, categoria AS categoria_fin
    FROM agencias_clasificadas
    WHERE mes = ?
)
SELECT
    t.categoria_inicio,
    t.categoria_fin,
    t.total
FROM (
    SELECT
        i.categoria_inicio AS categoria_inicio,
        COALESCE(f.categoria_fin, 'D') AS categoria_fin,
        COUNT(*) AS total
    FROM mes_inicio i
    LEFT JOIN mes_fin f ON f.agencia_id = i.agencia_id
    GROUP BY i.categoria_inicio, COALESCE(f.categoria_fin, 'D')
) t
ORDER BY
    FIELD(t.categoria_inicio, 'A', 'B', 'C', 'D'),
    FIELD(t.categoria_fin, 'A', 'B', 'C', 'D')
SQL;

        $bindingsTransicionesAgencias = [
            $anio,
            $mesInicio,
            $mesFin,
            $anio,
            $mesInicio,
            $mesFin,
            $configuracion['agencia']['A'],
            $configuracion['agencia']['B'],
            $configuracion['agencia']['C'],
            $mesInicio,
            $mesFin,
        ];

        $transicionesAgencias = collect(DB::select($sqlTransicionesAgencias, $bindingsTransicionesAgencias))
            ->map(function ($row) {
                return [
                    'categoria_inicio' => (string) ($row->categoria_inicio ?? ''),
                    'categoria_fin' => (string) ($row->categoria_fin ?? ''),
                    'total' => (int) ($row->total ?? 0),
                ];
            })
            ->values();

        $sqlDetalleTransicionesAgencias = <<<'SQL'
WITH ventas_agencia_unificadas AS (
    SELECT
        agencia_id,
        SUM(monto) AS monto,
        MONTH(fecha) AS mes
    FROM vt_usuarios_bet
    WHERE YEAR(fecha) = ?
      AND MONTH(fecha) IN (?, ?)
    GROUP BY agencia_id, MONTH(fecha)

    UNION ALL

    SELECT
        agencia_id,
        SUM(monto) AS monto,
        MONTH(fecha) AS mes
    FROM vt_usuarios_net
    WHERE YEAR(fecha) = ?
      AND MONTH(fecha) IN (?, ?)
    GROUP BY agencia_id, MONTH(fecha)
),
agencias_clasificadas AS (
    SELECT
        agencia_id,
        mes,
        CASE
            WHEN SUM(monto) >= ? THEN 'A'
            WHEN SUM(monto) >= ? THEN 'B'
            WHEN SUM(monto) >= ? THEN 'C'
            ELSE 'D'
        END AS categoria
    FROM ventas_agencia_unificadas
    GROUP BY agencia_id, mes
),
mes_inicio AS (
    SELECT agencia_id, categoria AS categoria_inicio
    FROM agencias_clasificadas
    WHERE mes = ?
),
mes_fin AS (
    SELECT agencia_id, categoria AS categoria_fin
    FROM agencias_clasificadas
    WHERE mes = ?
)
SELECT
    TRIM(CAST(i.agencia_id AS CHAR)) AS codigo_agencia,
    COALESCE(NULLIF(TRIM(a.nombre_agencia), ''), CONCAT('Agencia ', TRIM(CAST(i.agencia_id AS CHAR)))) AS nombre_agencia,
    i.categoria_inicio,
    COALESCE(f.categoria_fin, 'D') AS categoria_fin
FROM mes_inicio i
LEFT JOIN mes_fin f ON f.agencia_id = i.agencia_id
LEFT JOIN agencias a ON TRIM(CAST(a.terminal AS CHAR)) = TRIM(CAST(i.agencia_id AS CHAR))
ORDER BY
    FIELD(i.categoria_inicio, 'A', 'B', 'C', 'D'),
    FIELD(COALESCE(f.categoria_fin, 'D'), 'A', 'B', 'C', 'D'),
    nombre_agencia,
    codigo_agencia
SQL;

        $detalleTransicionesAgencias = collect(DB::select($sqlDetalleTransicionesAgencias, $bindingsTransicionesAgencias))
            ->map(function ($row) {
                return [
                    'codigo_agencia' => (string) ($row->codigo_agencia ?? ''),
                    'nombre_agencia' => (string) ($row->nombre_agencia ?? ''),
                    'categoria_inicio' => (string) ($row->categoria_inicio ?? ''),
                    'categoria_fin' => (string) ($row->categoria_fin ?? ''),
                ];
            })
            ->values();

        return response()->json([
            'data' => $resultados,
            'transiciones_agencias' => $transicionesAgencias,
            'transiciones_agencias_detalle' => $detalleTransicionesAgencias,
            'meta' => [
                'anio' => $anio,
                'mes_inicio' => $mesInicio,
                'mes_fin' => $mesFin,
                'configuracion' => $configuracion,
            ],
        ]);
    }

    private function normalizeYear($value): int
    {
        $year = (int) $value;

        if ($year < 2000 || $year > 2100) {
            return (int) now()->year;
        }

        return $year;
    }

    private function normalizeMonth($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $month = (int) $value;
        if ($month < 1 || $month > 12) {
            return null;
        }

        return $month;
    }

    private function resolveThresholdConfig(Request $request): array
    {
        $default = $this->defaultThresholdConfig();

        $agenciaA = $this->normalizeThreshold($request->query('agencia_a'), $default['agencia']['A']);
        $agenciaB = $this->normalizeThreshold($request->query('agencia_b'), $default['agencia']['B']);
        $agenciaC = $this->normalizeThreshold($request->query('agencia_c'), $default['agencia']['C']);
        $agenciaD = $this->normalizeThreshold($request->query('agencia_d'), $default['agencia']['D']);

        $agenteA = $this->normalizeThreshold($request->query('agente_a'), $default['agente']['A']);
        $agenteB = $this->normalizeThreshold($request->query('agente_b'), $default['agente']['B']);
        $agenteC = $this->normalizeThreshold($request->query('agente_c'), $default['agente']['C']);
        $agenteD = $this->normalizeThreshold($request->query('agente_d'), $default['agente']['D']);

        $agenciaValida = $agenciaA > $agenciaB && $agenciaB > $agenciaC && $agenciaC > $agenciaD;
        $agenteValida = $agenteA > $agenteB && $agenteB > $agenteC && $agenteC > $agenteD;

        return [
            'agencia' => $agenciaValida
                ? ['A' => $agenciaA, 'B' => $agenciaB, 'C' => $agenciaC, 'D' => $agenciaD]
                : $default['agencia'],
            'agente' => $agenteValida
                ? ['A' => $agenteA, 'B' => $agenteB, 'C' => $agenteC, 'D' => $agenteD]
                : $default['agente'],
        ];
    }

    private function normalizeThreshold($value, int $fallback): int
    {
        $number = (int) $value;
        if ($number <= 0) {
            return $fallback;
        }

        return $number;
    }

    private function defaultThresholdConfig(): array
    {
        return [
            'agencia' => [
                'A' => 150000,
                'B' => 110000,
                'C' => 60001,
                'D' => 60000,
            ],
            'agente' => [
                'A' => 150000,
                'B' => 110000,
                'C' => 60001,
                'D' => 60000,
            ],
        ];
    }
}
