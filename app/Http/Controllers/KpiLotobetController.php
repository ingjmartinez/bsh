<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiLotobetController extends Controller
{
    public function index()
    {
        return view('kpi-lotobet.index');
    }

    public function getData(Request $request)
    {
        $anio = $request->input('anio', date('Y'));
        $mes = $request->input('mes', date('m'));
        
        // Parámetros de meta
        $metaTotal = 300000;
        $pctTrad = 0.70;
        $pctNotrad = 0.20;
        $pctRec = 0.10;
        
        // Tradicional exige el 100% del total
        $metaTrad = $metaTotal;
        $metaNotrad = $metaTotal * $pctNotrad;
        $metaRec = $metaTotal * $pctRec;
        
        // Fechas
        $ini = date('Y-m-d', strtotime("$anio-$mes-01"));
        $fin = date('Y-m-t', strtotime($ini));
        $diasMes = date('t', strtotime($ini));
        
        $metaTradD = $metaTrad / $diasMes;
        $metaNotradD = $metaNotrad / $diasMes;
        $metaRecD = $metaRec / $diasMes;

        // Query principal
        $resultados = DB::select("
            SELECT
                a.agencia_id AS agencia,

                -- META MENSUAL TRADICIONAL
                CASE
                    WHEN COALESCE(m.trad_mes,0) >= ? THEN 'Cumplio'
                    ELSE CONCAT(
                        'Falto ',
                        ROUND(((? - COALESCE(m.trad_mes,0)) / ?) * 100, 2),
                        '%'
                    )
                END AS MetaMensual_Tra,

                -- META MENSUAL NO TRADICIONAL
                CASE
                    WHEN COALESCE(m.notrad_mes,0) >= ? THEN 'Cumplio'
                    ELSE CONCAT(
                        'Falto ',
                        ROUND(((? - COALESCE(m.notrad_mes,0)) / ?) * 100, 2),
                        '%'
                    )
                END AS MetaMensual_NoTra,

                -- META MENSUAL RECARGAS
                CASE
                    WHEN COALESCE(m.rec_mes,0) >= ? THEN 'Cumplio'
                    ELSE CONCAT(
                        'Falto ',
                        ROUND(((? - COALESCE(m.rec_mes,0)) / ?) * 100, 2),
                        '%'
                    )
                END AS MetaMensual_Rec,

                -- DIAS
                COALESCE(d.dias_cumplido,0) AS Cant_Dias_Cumplido,
                (? - COALESCE(d.dias_cumplido,0)) AS Cant_No_Cumplido,

                -- SEVERIDAD
                CASE
                    WHEN COALESCE(d.dias_cumplido,0) BETWEEN 21 AND ? THEN 'Excelencia'
                    WHEN COALESCE(d.dias_cumplido,0) BETWEEN 11 AND 20 THEN 'Estable'
                    WHEN COALESCE(d.dias_cumplido,0) BETWEEN 3 AND 10 THEN 'En riesgo'
                    ELSE 'Crítica'
                END AS Severidad

            FROM
                (
                    SELECT DISTINCT agencia_id
                    FROM vt_usuarios_bet
                    WHERE fecha BETWEEN ? AND ?
                ) a

            LEFT JOIN
                (
                    SELECT
                        agencia_id,
                        SUM(CASE WHEN LOWER(tipo) = 'tradicional' THEN monto ELSE 0 END) AS trad_mes,
                        SUM(CASE WHEN LOWER(tipo) IN ('no tradicional','no_tradicional') THEN monto ELSE 0 END) AS notrad_mes,
                        SUM(CASE WHEN LOWER(tipo) IN ('recargas','recarga') THEN monto ELSE 0 END) AS rec_mes
                    FROM vt_usuarios_bet
                    WHERE fecha BETWEEN ? AND ?
                    GROUP BY agencia_id
                ) m
                ON m.agencia_id = a.agencia_id

            LEFT JOIN
                (
                    SELECT
                        agencia_id,
                        SUM(
                            CASE
                                WHEN trad_d >= ?
                                AND notrad_d >= ?
                                AND rec_d >= ?
                                THEN 1 ELSE 0
                            END
                        ) AS dias_cumplido
                    FROM (
                        SELECT
                            agencia_id,
                            fecha,
                            SUM(CASE WHEN LOWER(tipo) = 'tradicional' THEN monto ELSE 0 END) AS trad_d,
                            SUM(CASE WHEN LOWER(tipo) IN ('no tradicional','no_tradicional') THEN monto ELSE 0 END) AS notrad_d,
                            SUM(CASE WHEN LOWER(tipo) IN ('recargas','recarga') THEN monto ELSE 0 END) AS rec_d
                        FROM vt_usuarios_bet
                        WHERE fecha BETWEEN ? AND ?
                        GROUP BY agencia_id, fecha
                    ) x
                    GROUP BY agencia_id
                ) d
                ON d.agencia_id = a.agencia_id

            ORDER BY Cant_Dias_Cumplido DESC, a.agencia_id
        ", [
            $metaTrad, $metaTrad, $metaTrad,
            $metaNotrad, $metaNotrad, $metaNotrad,
            $metaRec, $metaRec, $metaRec,
            $diasMes, $diasMes,
            $ini, $fin,
            $ini, $fin,
            $metaTradD, $metaNotradD, $metaRecD,
            $ini, $fin
        ]);

        // Procesar resultados para KPIs
        $totalAgencias = count($resultados);
        
        $cumplioTrad = 0;
        $cumplioNotrad = 0;
        $cumplioRec = 0;
        $totalDiasCumplidos = 0;
        $totalDiasNoCumplidos = 0;
        $agenciasCumplieron = 0;
        $agenciasNoCumplieron = 0;
        
        $severidadCount = [
            'Excelencia' => 0,
            'Estable' => 0,
            'En riesgo' => 0,
            'Crítica' => 0
        ];

        foreach ($resultados as $row) {
            if ($row->MetaMensual_Tra === 'Cumplio') $cumplioTrad++;
            if ($row->MetaMensual_NoTra === 'Cumplio') $cumplioNotrad++;
            if ($row->MetaMensual_Rec === 'Cumplio') $cumplioRec++;
            
            $totalDiasCumplidos += $row->Cant_Dias_Cumplido;
            $totalDiasNoCumplidos += $row->Cant_No_Cumplido;
            
            // Contar agencias que cumplieron al menos un día
            if ($row->Cant_Dias_Cumplido > 0) {
                $agenciasCumplieron++;
            }
            
            // Contar agencias que NO cumplieron ningún día
            if ($row->Cant_Dias_Cumplido == 0) {
                $agenciasNoCumplieron++;
            }
            
            $severidadCount[$row->Severidad]++;
        }

        return response()->json([
            'parametros' => [
                'anio' => $anio,
                'mes' => $mes,
                'meta_total' => $metaTotal,
                'meta_trad' => $metaTrad,
                'meta_notrad' => $metaNotrad,
                'meta_rec' => $metaRec,
                'meta_trad_d' => $metaTradD,
                'meta_notrad_d' => $metaNotradD,
                'meta_rec_d' => $metaRecD,
                'dias_mes' => $diasMes
            ],
            'kpis' => [
                'total_agencias' => $totalAgencias,
                'cumplio_trad' => $cumplioTrad,
                'cumplio_notrad' => $cumplioNotrad,
                'cumplio_rec' => $cumplioRec,
                'pct_cumplio_trad' => $totalAgencias > 0 ? round(($cumplioTrad / $totalAgencias) * 100, 2) : 0,
                'pct_cumplio_notrad' => $totalAgencias > 0 ? round(($cumplioNotrad / $totalAgencias) * 100, 2) : 0,
                'pct_cumplio_rec' => $totalAgencias > 0 ? round(($cumplioRec / $totalAgencias) * 100, 2) : 0,
                'agencias_cumplieron' => $agenciasCumplieron,
                'agencias_no_cumplieron' => $agenciasNoCumplieron,
                'total_dias_cumplidos' => $totalDiasCumplidos,
                'total_dias_no_cumplidos' => $totalDiasNoCumplidos,
                'promedio_dias_cumplidos' => $totalAgencias > 0 ? round($totalDiasCumplidos / $totalAgencias, 1) : 0,
                'promedio_dias_no_cumplidos' => $totalAgencias > 0 ? round($totalDiasNoCumplidos / $totalAgencias, 1) : 0
            ],
            'severidad' => $severidadCount,
            'tabla' => $resultados
        ]);
    }

    public function getProductosAgencia(Request $request)
    {
        $agencia = $request->input('agencia');
        $anio = $request->input('anio', date('Y'));
        $mes = $request->input('mes', date('m'));
        
        // Fechas
        $ini = date('Y-m-d', strtotime("$anio-$mes-01"));
        $fin = date('Y-m-t', strtotime($ini));

        // Query para obtener ventas por producto (combinando bet y net)
        $productos = DB::select("
            SELECT 
                descripcion AS producto,
                SUM(monto) AS ventas
            FROM (
                SELECT descripcion, monto
                FROM ventas_producto_bet
                WHERE agencia_id = ?
                    AND fecha BETWEEN ? AND ?
                    AND descripcion NOT IN (
                        '19','42','53','66','41','54','106','55','65','204','205','61','58','107',
                        '90219','90319','90419','90619','206','207','91119','105','62','64','108',
                        '109','110','111','63','90119','57'
                    )
                UNION ALL
                SELECT descripcion, monto
                FROM ventas_producto_net
                WHERE agencia_id = ?
                    AND fecha BETWEEN ? AND ?
                    AND descripcion NOT IN (
                        '19','42','53','66','41','54','106','55','65','204','205','61','58','107',
                        '90219','90319','90419','90619','206','207','91119','105','62','64','108',
                        '109','110','111','63','90119','57'
                    )
            ) AS combined
            GROUP BY descripcion
            HAVING ventas > 0
            ORDER BY ventas DESC
        ", [$agencia, $ini, $fin, $agencia, $ini, $fin]);

        $totalProductos = count($productos);

        // Top 10 más vendidos
        $masVendidos = array_slice($productos, 0, 10);

        // Top 10 regulares (del medio)
        $regulares = [];
        if ($totalProductos > 20) {
            $inicio = floor($totalProductos / 2) - 5;
            $regulares = array_slice($productos, max(0, $inicio), 10);
        } elseif ($totalProductos > 10) {
            $regulares = array_slice($productos, 10, 10);
        }

        // Top 10 menos vendidos
        $menosVendidos = [];
        if ($totalProductos > 10) {
            $menosVendidos = array_slice($productos, -10);
            $menosVendidos = array_reverse($menosVendidos);
        }

        return response()->json([
            'mas_vendidos' => $masVendidos,
            'regulares' => $regulares,
            'menos_vendidos' => $menosVendidos
        ]);
    }
}
