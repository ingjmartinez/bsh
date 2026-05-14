<?php

namespace App\Http\Controllers;

use App\Models\CoordinadorOperador;
use App\Models\IncentivoAdministrativo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncentivosController extends Controller
{
    public function index()
    {
        $productosExcluidos = DB::table('catalogo_juegos')
            ->whereIn('producto_id', [538, 539])
            ->get();

        return view('incentivos.index', [
            'productosExcluidos' => $productosExcluidos
        ]);
    }

    public function procesar()
    {
        $productosExcluidos = DB::table('catalogo_juegos')
            ->whereIn('producto_id', [538, 539])
            ->get();

        return view('incentivos.procesar', [
            'productosExcluidos' => $productosExcluidos
        ]);
    }

    function list(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');
        $mes = $request->input('mes');
        $excluidos = $request->input('excluidos', '');
        $year = $request->input('year', '');
        $incentivos = DB::select('CALL CalculoIncentivo(?, ?, ?)', [$mes,  $year, $excluidos]);
        return response()->json($incentivos);
    }

    function save(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');

        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $datos = $request->input('datos');
        $reset = $request->boolean('reset', true);
        // Insertar o traer id de incentivo_temporal_c
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');
        if (!$incentivoId) {
            $incentivoId = DB::table('incentivo_temporal_c')->insertGetId([
                'anio' => $anio,
                'mes' => $mes
            ]);
        }

        $data = [];
        // Limpiar tabla solo si es el primer lote
        if ($reset) {
            DB::table('incentivo_temporal')->where('incentivo_id', $incentivoId)->delete();
        }

        foreach ($datos as $dato) {
            $data[] = [
                'incentivo_id' => $incentivoId,
                'agencia_id' => $dato['agencia_id'],
                'tipo_producto' => $dato['tipo_producto'],
                'sistema' => $dato['sistema'],
                'total_trimestre' => floatval(str_replace(',', '', $dato['total_trimestre'])),
                'promedio_mensual' => floatval(str_replace(',', '', $dato['promedio_mensual'])),
                'venta_base' => floatval(str_replace(',', '', $dato['venta_base'])),
                'venta_mes' => floatval(str_replace(',', '', $dato['total_mes'])),
                'nivel' => $dato['nivel'],
                'cumplimiento' => floatval(str_replace(',', '', $dato['cumplimiento'])),
                'meta_incremental' => floatval(str_replace(',', '', $dato['meta_incremental'])),
                'meta_plan' => floatval(str_replace(',', '', $dato['meta_plan'])),
            ];
        }

        foreach (array_chunk($data, 5000) as $chunk) {
            DB::table('incentivo_temporal')->insert($chunk);
        }

        return response()->json(['message' => 'Incentivos guardados exitosamente.']);
    }

    function listPlanAgencia(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');

        // Validar si existen datos en incentivo_temporal_c
        if ($incentivoId === null) {
            return response()->json(['message' => 'No hay datos registrados en el mes.']);
        }

        $planAgencia = DB::select(
            "SELECT 
                it.agencia_id,
                it.tipo_producto,
                it.sistema,
                FORMAT(it.venta_mes, 2) AS venta_mes,
                FORMAT(it.meta_incremental, 2) AS venta_base,
                CASE
                    WHEN it.nivel IN (1,2,3) AND it.venta_mes >= it.meta_incremental THEN
                        FORMAT((it.venta_mes - it.meta_incremental), 2)
                    WHEN it.venta_mes >= it.meta_incremental THEN 
                        FORMAT((it.venta_mes - it.meta_incremental), 2)
                    ELSE
                        CONCAT(
                            'FALTA ',
                            FORMAT(((it.meta_incremental - it.venta_mes) / it.meta_incremental) * 100 , 2),
                            '%'
                        )
                END AS excedente,
                pa_agente.porcentaje AS porcentaje_agente,
                pa_coord.porcentaje AS porcentaje_coordinador,
                pa_admin.porcentaje AS porcentaje_administrativo,
                CASE
                    WHEN it.nivel IN (1,2,3) AND it.venta_mes >= it.meta_incremental THEN
                        FORMAT((it.venta_mes - it.meta_incremental) * pa_agente.porcentaje, 2)
                    WHEN it.venta_mes >= it.meta_incremental THEN 
                        FORMAT((it.venta_mes - it.venta_base) * pa_agente.porcentaje, 2)
                    ELSE ''
                END AS monto_agente,
                CASE
                    WHEN it.nivel IN (1,2,3) AND it.venta_mes >= it.meta_incremental THEN
                        FORMAT((it.venta_mes - it.meta_incremental) * pa_agente.porcentaje, 2)
                    WHEN it.venta_mes >= it.meta_incremental THEN 
                        FORMAT((it.venta_mes - it.venta_base) * pa_coord.porcentaje, 2)
                    ELSE ''
                END AS monto_coordinador,
                CASE
                    WHEN it.nivel IN (1,2,3) AND it.venta_mes >= it.meta_incremental THEN
                        FORMAT((it.venta_mes - it.meta_incremental) * pa_agente.porcentaje, 2)
                    WHEN it.venta_mes >= it.meta_incremental THEN 
                        FORMAT((it.venta_mes - it.venta_base) * pa_admin.porcentaje, 2)
                    ELSE ''
                END AS monto_administrativo,
                CASE
                    WHEN it.nivel IN (1,2,3) AND it.venta_mes >= it.meta_incremental THEN
                        FORMAT(
                            ((it.venta_mes - it.meta_incremental) * pa_agente.porcentaje) +
                            ((it.venta_mes - it.meta_incremental) * pa_coord.porcentaje) +
                            ((it.venta_mes - it.meta_incremental) * pa_admin.porcentaje)
                        , 2)
                    WHEN it.venta_mes >= it.meta_incremental THEN 
                        FORMAT(
                            ((it.venta_mes - it.venta_base) * pa_agente.porcentaje) +
                            ((it.venta_mes - it.venta_base) * pa_coord.porcentaje) +
                            ((it.venta_mes - it.venta_base) * pa_admin.porcentaje)
                        , 2)
                    ELSE ''
                END AS total_distribucion
            FROM incentivo_temporal it
            LEFT JOIN distribucion_porcentajes pa_agente
                ON pa_agente.departamento = 'Agente'
                AND pa_agente.tipo = it.tipo_producto
            LEFT JOIN distribucion_porcentajes pa_coord
                ON pa_coord.departamento = 'Coordinador'
                AND pa_coord.tipo = it.tipo_producto
            LEFT JOIN distribucion_porcentajes pa_admin
                ON pa_admin.departamento = 'Administrativo'
                AND pa_admin.tipo = it.tipo_producto
            INNER JOIN agencias a
                ON CAST(TRIM(it.agencia_id) AS UNSIGNED) = CAST(a.terminal AS UNSIGNED)
                AND a.aplica_incentivo = 1
            WHERE it.incentivo_id = $incentivoId AND it.venta_mes > 0;"
        );

        // FORMAT(it.venta_base, 2) AS venta_base,
        // CASE WHEN it.venta_mes >= it.meta_plan 
        //             THEN 'SI CUMPLE'
        //             ELSE 'NO CUMPLE'
        //         END AS condicion
        return response()->json($planAgencia);
    }

    function savePlanAgencia(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');

        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $datos = $request->input('datos');
        $reset = $request->boolean('reset', true);
        // Insertar o traer id de incentivo_temporal_c
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');
        if (!$incentivoId) {
            $incentivoId = DB::table('incentivo_temporal_c')->insertGetId([
                'anio' => $anio,
                'mes' => $mes
            ]);
        }

        $data = [];
        // Limpiar tabla solo si es el primer lote
        if ($reset) {
            DB::table('plan_agencias_distribucion')->where('incentivo_id', $incentivoId)->delete();
        }

        foreach ($datos as $dato) {
            $data[] = [
                'incentivo_id' => $incentivoId,
                'agencia_id' => $dato['agencia_id'],
                'tipo_producto' => $dato['tipo_producto'],
                'sistema' => $dato['sistema'],
                'venta_mes' => floatval(str_replace(',', '', $dato['venta_mes'])),
                'venta_base' => floatval(str_replace(',', '', $dato['venta_base'])),
                'excedente' => floatval(str_replace(',', '', $dato['excedente'])),
                'porcentaje_agente' => floatval(str_replace(',', '', $dato['porcentaje_agente'])),
                'porcentaje_coordinador' => floatval(str_replace(',', '', $dato['porcentaje_coordinador'])),
                'porcentaje_administrativo' => floatval(str_replace(',', '', $dato['porcentaje_administrativo'])),
                'monto_agente' => floatval(str_replace(',', '', $dato['monto_agente'])),
                'monto_coordinador' => floatval(str_replace(',', '', $dato['monto_coordinador'])),
                'monto_administrativo' => floatval(str_replace(',', '', $dato['monto_administrativo'])),
                'total_distribucion' => floatval(str_replace(',', '', $dato['total_distribucion'])),
            ];
        }

        foreach (array_chunk($data, 3000) as $chunk) {
            DB::table('plan_agencias_distribucion')->insert($chunk);
        }

        return response()->json(['message' => 'Plan Agencia guardado exitosamente.']);
    }

    function listEfectividad(Request $request)
    {
        ini_set('max_execution_time', 600); // 5 minutes
        ini_set('memory_limit', '1G');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');

        // Validar si existen datos en incentivo_temporal_c
        if ($incentivoId === null) {
            return response()->json(['message' => 'No hay datos registrados en el mes.']);
        }
        $excluidos = trim($request->input('excluidos', '')); // ejemplo: "7,8,9"

        $excluirBet = '';
        $excluirNet = '';

        if ($excluidos !== '') {
            $excluirBet = "AND FIND_IN_SET(producto_id, '$excluidos') = 0";
            $excluirNet = "AND FIND_IN_SET(n.producto_id, '$excluidos') = 0";
        }

        $data = DB::select(
            "SELECT
                it.agencia_id,
                it.sistema,
                it.tipo_producto,
                FORMAT(it.venta_mes, 2) AS venta_mes,
                -- BET
                IFNULL(bet.empleadoid, '') AS empleadoid_bet,
                IFNULL(bet.cedula, '') AS cedula_bet,
                IFNULL(FORMAT(bet.monto_cedula, 2), '') AS monto_bet_cedula,
                IFNULL(ROUND((bet.monto_cedula / it.venta_mes) * 100, 2), '') AS porc_bet,
                -- NET
                IFNULL(net.empleadoid, '') AS empleadoid_net,
                IFNULL(net.cedula, '') AS cedula_net,
                IFNULL(FORMAT(net.monto_cedula, 2), '') AS monto_net_cedula,
                IFNULL(ROUND((net.monto_cedula / it.venta_mes) * 100, 2), '') AS porc_net
            FROM incentivo_temporal it
            INNER JOIN agencias a
                ON CAST(TRIM(it.agencia_id) AS UNSIGNED) = CAST(a.terminal AS UNSIGNED)
                AND a.aplica_incentivo = 1
            LEFT JOIN (
                SELECT agencia_id, vb.cedula, e.empleadoid, SUM(monto) AS monto_cedula, tipo, 'Lotobet' AS sistema
                FROM vt_usuarios_bet vb
                INNER JOIN empleados e ON vb.cedula = e.cedula
                WHERE MONTH(vb.fecha) = $mes AND YEAR(vb.fecha) = $anio AND vb.monto > 0 $excluirBet AND e.aplica_incentivo = 'SI'
                GROUP BY vb.agencia_id, vb.cedula, e.empleadoid, vb.tipo
            ) bet ON bet.agencia_id = it.agencia_id AND bet.tipo = it.tipo_producto AND it.sistema = bet.sistema
            LEFT JOIN (
                SELECT agencia_id, n.cedula, e.empleadoid, SUM(monto) AS monto_cedula, c.tipo, 'Lotonet' AS sistema
                FROM vt_usuarios_net n
                INNER JOIN empleados e ON n.cedula = e.cedula
                LEFT JOIN catalogo_juegos c ON CAST(n.producto_id AS SIGNED) = c.producto_id
                WHERE MONTH(n.fecha) = $mes AND YEAR(n.fecha) = $anio AND n.monto > 0 $excluirNet AND e.aplica_incentivo = 'SI'
                GROUP BY n.agencia_id, n.cedula, e.empleadoid, c.tipo
            ) net ON net.agencia_id = it.agencia_id AND net.tipo = it.tipo_producto AND it.sistema = net.sistema
            WHERE it.incentivo_id = $incentivoId AND it.tipo_producto IS NOT NULL
                AND it.venta_mes > 0
            ORDER BY it.agencia_id;"
        );
        return response()->json($data);
    }

    function saveEfectividad(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');

        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $datos = $request->input('datos');
        $reset = $request->boolean('reset', true);
        // Insertar o traer id de incentivo_temporal_c
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');
        if (!$incentivoId) {
            $incentivoId = DB::table('incentivo_temporal_c')->insertGetId([
                'anio' => $anio,
                'mes' => $mes
            ]);
        }

        $data = [];
        // Limpiar tabla solo si es el primer lote
        if ($reset) {
            DB::table('efectividad_usuarios')->where('incentivo_id', $incentivoId)->delete();
        }

        foreach ($datos as $dato) {
            $data[] = [
                'incentivo_id' => $incentivoId,
                'agencia_id' => $dato['agencia_id'],
                'tipo_producto' => $dato['tipo_producto'],
                'sistema' => $dato['sistema'],
                'venta_mes' => floatval(str_replace(',', '', $dato['venta_mes'])),
                'empleadoid_bet' => $dato['empleadoid_bet'],
                'cedula_bet' => $dato['cedula_bet'],
                'monto_cedula_bet' => floatval(str_replace(',', '', $dato['monto_bet_cedula'])),
                'porcentaje_cedula_bet' => floatval(str_replace(',', '', $dato['porc_bet'])),
                'empleadoid_net' => $dato['empleadoid_net'],
                'cedula_net' => $dato['cedula_net'],
                'monto_cedula_net' => floatval(str_replace(',', '', $dato['monto_net_cedula'])),
                'porcentaje_cedula_net' => floatval(str_replace(',', '', $dato['porc_net'])),
            ];
        }

        foreach (array_chunk($data, 5000) as $chunk) {
            DB::table('efectividad_usuarios')->insert($chunk);
        }

        return response()->json(['message' => 'Efectividad guardada exitosamente.']);
    }

    function listPagoAgente(Request $request)
    {
        ini_set('max_execution_time', 600); // 5 minutes
        ini_set('memory_limit', '1G');
        $sistema = $request->input('sistema');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');

        // Validar si existen datos en incentivo_temporal_c
        if ($incentivoId === null) {
            return response()->json(['message' => 'No hay datos registrados en el mes.']);
        }

        $data = DB::select(
            "SELECT
                eu.agencia_id,
                eu.tipo_producto,
                CASE WHEN '$sistema' = 'Lotobet'
                    THEN eu.cedula_bet
                    ELSE eu.cedula_net
                END AS cedula,
                CASE WHEN '$sistema' = 'Lotobet'
                    THEN FORMAT(eu.porcentaje_cedula_bet, 2)
                    ELSE FORMAT(eu.porcentaje_cedula_net, 2)
                END AS porcentaje_cedula,
                FORMAT(pad.monto_agente, 2) AS monto_agente,
                CASE WHEN '$sistema' = 'Lotobet'
                    THEN ROUND((eu.porcentaje_cedula_bet / 100) * pad.monto_agente, 2)
                    ELSE ROUND((eu.porcentaje_cedula_net / 100) * pad.monto_agente, 2)
                END AS monto_incentivo,
                CASE WHEN '$sistema' = 'Lotobet'
                    THEN eu.empleadoid_bet
                    ELSE eu.empleadoid_net
                END AS empleadoid
            FROM efectividad_usuarios eu
            INNER JOIN plan_agencias_distribucion pad ON eu.incentivo_id = pad.incentivo_id
                AND CAST(eu.agencia_id AS UNSIGNED) = CAST(pad.agencia_id AS UNSIGNED)
                AND eu.tipo_producto = pad.tipo_producto
            INNER JOIN incentivo_temporal it on eu.incentivo_id = it.incentivo_id
                AND CAST(eu.agencia_id AS UNSIGNED) = CAST(it.agencia_id AS UNSIGNED)
                AND eu.tipo_producto = it.tipo_producto
            INNER JOIN agencias a ON CAST(it.agencia_id AS UNSIGNED) = CAST(a.terminal AS UNSIGNED)
                AND a.aplica_incentivo = 1
            WHERE eu.incentivo_id = $incentivoId AND eu.sistema = '$sistema'
                AND it.venta_mes >= it.venta_base
                AND pad.monto_agente > 0
                AND it.venta_mes > 0;"
        );
        return response()->json($data);
    }

    function savePagoAgente(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');
        $sistema = $request->input('sistema');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $datos = $request->input('datos');
        $reset = $request->boolean('reset', true);
        // Insertar o traer id de incentivo_temporal_c
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');
        if (!$incentivoId) {
            $incentivoId = DB::table('incentivo_temporal_c')->insertGetId([
                'anio' => $anio,
                'mes' => $mes
            ]);
        }

        $data = [];
        // Limpiar tabla solo si es el primer lote
        if ($reset) {
            DB::table('pago_incentivos')
                ->where('incentivo_id', $incentivoId)
                ->where('sistema', $sistema)
                ->delete();
        }

        foreach ($datos as $dato) {
            $data[] = [
                'incentivo_id' => $incentivoId,
                'agencia_id' => $dato['agencia_id'],
                'tipo_producto' => $dato['tipo_producto'],
                'sistema' => $sistema,
                'empleadoid' => $dato['empleadoid'],
                'cedula' => $dato['cedula'],
                'porcentaje_cedula' => floatval(str_replace(',', '', $dato['porcentaje_cedula'])),
                'monto_agente' => floatval(str_replace(',', '', $dato['monto_agente'])),
                'monto_incentivo' => floatval(str_replace(',', '', $dato['monto_incentivo'])),
            ];
        }

        foreach (array_chunk($data, 5000) as $chunk) {
            DB::table('pago_incentivos')->insert($chunk);
        }

        return response()->json(['message' => 'Pago Incentivos guardado exitosamente.']);
    }

    function listPagoCoordinador(Request $request)
    {
        ini_set('max_execution_time', 600); // 5 minutes
        ini_set('memory_limit', '1G');
        $sistema = $request->input('sistema');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');

        // Validar si existen datos en incentivo_temporal_c
        if ($incentivoId === null) {
            return response()->json(['message' => 'No hay datos registrados en el mes.']);
        }

        $data = DB::select(
            "SELECT 
                companyid,
                company,
                empleadoid,
                cedula,
                nombres,
                apellidos,
                FORMAT(SUM(total), 2) AS total_empleado
            FROM (
                SELECT 
                    e.companyid,
                    CASE WHEN e.companyid = 168 THEN 'Joselito' ELSE 'Negosur' END AS company,
                    c.empleado_id AS empleadoid,
                    e.cedula,
                    e.nombres,
                    e.apellidos,
                    pad_tot.total_agencia AS total,
                    pad_tot.porcentaje_coordinador AS porcentaje
                FROM coordinador c
                INNER JOIN (
                    SELECT pad.agencia_id, SUM(pad.monto_coordinador) AS total_agencia, pad.porcentaje_coordinador
                    FROM plan_agencias_distribucion pad
                    INNER JOIN agencias a ON CAST(TRIM(pad.agencia_id) AS UNSIGNED) = CAST(a.terminal AS UNSIGNED)
                        AND a.aplica_incentivo = 1
                    WHERE pad.incentivo_id = ? AND pad.excedente > 0 AND pad.sistema = ?
                    GROUP BY pad.agencia_id, pad.porcentaje_coordinador
                ) pad_tot ON pad_tot.agencia_id = c.agencia_id
                INNER JOIN empleados e ON c.empleado_id = e.empleadoid 
                    AND e.companyid IN (168, 169) AND e.fechasalida IS NULL
            ) AS t
            GROUP BY companyid, company, empleadoid, cedula, nombres, apellidos;",
            [$incentivoId, $sistema]
        );
        return response()->json($data);
    }

    function savePagoCoordinador(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');
        $sistema = $request->input('sistema');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $datos = $request->input('datos');
        $reset = $request->boolean('reset', true);
        // Insertar o traer id de incentivo_temporal_c
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');
        if (!$incentivoId) {
            $incentivoId = DB::table('incentivo_temporal_c')->insertGetId([
                'anio' => $anio,
                'mes' => $mes
            ]);
        }

        $data = [];
        // Limpiar tabla solo si es el primer lote
        if ($reset) {
            DB::table('pago_incentivos_coordinador')->where('incentivo_id', $incentivoId)->delete();
        }

        foreach ($datos as $dato) {
            $data[] = [
                'incentivo_id' => $incentivoId,
                'empleadoid' => $dato['empleadoid'],
                'companyid' => $dato['companyid'],
                'cedula' => $dato['cedula'],
                'porcentaje' => 0,
                'total' => floatval(str_replace(',', '', $dato['total_empleado'])),
            ];
        }

        foreach (array_chunk($data, 5000) as $chunk) {
            DB::table('pago_incentivos_coordinador')->insert($chunk);
        }

        return response()->json(['message' => 'Pago Incentivos guardado exitosamente.']);
    }

    function listPagoCoordinadorDetalle(Request $request)
    {
        ini_set('max_execution_time', 600); // 5 minutes
        ini_set('memory_limit', '1G');

        $cedula = $request->input('cedula');
        $tipo_producto = trim($request->input('tipo_producto', ''));
        $sistema = $request->input('sistema');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));

        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');

        if ($incentivoId === null) {
            return response()->json(['message' => 'No hay datos registrados en el mes.']);
        }

        $sql = "SELECT 
                    agencia_id, 
                    tipo_producto, 
                    pad.sistema, 
                    FORMAT(venta_mes, 2) AS venta_mes,
                    FORMAT(venta_base, 2) AS venta_base,
                    FORMAT(excedente, 2) AS excedente,
                    FORMAT(porcentaje_coordinador, 3) AS porcentaje_coordinador,
                    FORMAT(monto_coordinador, 2) AS monto_coordinador
                FROM plan_agencias_distribucion pad
                INNER JOIN agencias a
                    ON CAST(TRIM(pad.agencia_id) AS UNSIGNED) = CAST(a.terminal AS UNSIGNED)
                    AND a.aplica_incentivo = 1
                WHERE pad.incentivo_id = ?
                    AND pad.agencia_id IN (
                        SELECT agencia_id 
                        FROM coordinador c
                        INNER JOIN empleados e ON c.empleado_id = e.empleadoid
                        WHERE e.cedula = ?
                    )
                    AND excedente > 0";

        $bindings = [$incentivoId, $cedula];

        if (!empty($sistema)) {
            $sql .= " AND pad.sistema = ?";
            $bindings[] = $sistema;
        }

        if ($tipo_producto !== '') {
            $sql .= " AND tipo_producto = ?";
            $bindings[] = $tipo_producto;
        }

        $sql .= " ORDER BY agencia_id, tipo_producto, sistema";

        $data = DB::select($sql, $bindings);
        return response()->json($data);
    }

    function listPagoAdmin(Request $request)
    {
        ini_set('max_execution_time', 600); // 5 minutes
        ini_set('memory_limit', '1G');
        $sistema = $request->input('sistema');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');

        // Validar si existen datos en incentivo_temporal_c
        if ($incentivoId === null) {
            return response()->json(['message' => 'No hay datos registrados en el mes.']);
        }

        $data = DB::select(
            "WITH totales_producto AS (
                SELECT SUM(pad.monto_administrativo) AS total, pad.tipo_producto
                FROM plan_agencias_distribucion pad
                INNER JOIN agencias a ON CAST(TRIM(pad.agencia_id) AS UNSIGNED) = CAST(a.terminal AS UNSIGNED)
                    AND a.aplica_incentivo = 1
                WHERE pad.incentivo_id = $incentivoId AND pad.excedente > 0
                GROUP BY pad.tipo_producto
            )
            SELECT 
                CASE WHEN emp.companyid = '168' THEN 'Joselito' ELSE 'Negosur' END AS empresa,
                emp.cedula,
                emp.companyid,
                emp.empleadoid,
                emp.nombres,
                emp.apellidos,
                FORMAT(e.porcentaje, 2) AS porcentaje,
                -- Tradicional
                FORMAT(ROUND(
                    (e.porcentaje / 100) * 
                    IFNULL(MAX(CASE WHEN t.tipo_producto = 'Tradicional' THEN t.total END), 0),
                    2
                ), 2) AS Tradicional,
                -- No Tradicional
                FORMAT(ROUND(
                    (e.porcentaje / 100) * 
                    IFNULL(MAX(CASE WHEN t.tipo_producto = 'No Tradicional' THEN t.total END), 0),
                    2
                ), 2) AS No_Tradicional,
                -- Recargas
                FORMAT(ROUND(
                    (e.porcentaje / 100) * 
                    IFNULL(MAX(CASE WHEN t.tipo_producto = 'Recarga' THEN t.total END), 0),
                    2
                ), 2) AS Recargas,
                -- Paquetico
                FORMAT(ROUND(
                    (e.porcentaje / 100) * 
                    IFNULL(MAX(CASE WHEN t.tipo_producto = 'Paquetico' THEN t.total END), 0),
                    2
                ), 2) AS Paquetico,
                -- Total a cobrar (suma de las 4 columnas anteriores)
                FORMAT(ROUND(
                    (e.porcentaje / 100) * 
                    (
                        IFNULL(MAX(CASE WHEN t.tipo_producto = 'Tradicional'     THEN t.total END), 0) +
                        IFNULL(MAX(CASE WHEN t.tipo_producto = 'No Tradicional'  THEN t.total END), 0) +
                        IFNULL(MAX(CASE WHEN t.tipo_producto = 'Recarga'        THEN t.total END), 0) +
                        IFNULL(MAX(CASE WHEN t.tipo_producto = 'Paquetico'       THEN t.total END), 0)
                    ),
                    2
                ), 2) AS Total_a_cobrar
            FROM porcentaje_administrativo e
            INNER JOIN empleados emp ON e.empleado_id = emp.empleadoid
            CROSS JOIN totales_producto t
            WHERE emp.aplica_incentivo = 'SI' AND emp.tipo_empleado_incentivo = 3
            GROUP BY emp.companyid, emp.cedula, emp.empleadoid,
                emp.nombres, emp.apellidos, e.porcentaje
            ORDER BY Total_a_cobrar DESC;"
        );
        return response()->json($data);
    }

    function savePagoAdmin(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');
        $sistema = $request->input('sistema');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));
        $datos = $request->input('datos');
        $reset = $request->boolean('reset', true);
        // Insertar o traer id de incentivo_temporal_c
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)->where('mes', $mes)->value('incentivo_id');

        if (!$incentivoId) {
            $incentivoId = DB::table('incentivo_temporal_c')->insertGetId([
                'anio' => $anio,
                'mes' => $mes
            ]);
        }

        $data = [];
        // Limpiar tabla solo si es el primer lote
        if ($reset) {
            DB::table('pago_incentivos_admin')->where('incentivo_id', $incentivoId)->delete();
        }

        foreach ($datos as $dato) {
            $data[] = [
                'incentivo_id' => $incentivoId,
                'empleadoid' => $dato['empleadoid'],
                'companyid' => $dato['companyid'],
                'cedula' => $dato['cedula'],
                'tradicional' => floatval(str_replace(',', '', $dato['Tradicional'])),
                'no_tradicional' => floatval(str_replace(',', '', $dato['No_Tradicional'])),
                'recarga' => floatval(str_replace(',', '', $dato['Recargas'])),
                'paquetico' => floatval(str_replace(',', '', $dato['Paquetico'])),
                'total' => floatval(str_replace(',', '', $dato['Total_a_cobrar'])),
            ];
        }

        foreach (array_chunk($data, 5000) as $chunk) {
            DB::table('pago_incentivos_admin')->insert($chunk);
        }

        return response()->json(['message' => 'Pago Incentivos guardado exitosamente.']);
    }

    function listPagoAdminDetalle(Request $request)
    {
        ini_set('max_execution_time', 600); // 5 minutes
        ini_set('memory_limit', '1G');

        $cedula = $request->input('cedula');
        $companyid = $request->input('companyid');
        $mes = $request->input('mes');
        $anio = $request->input('year', date('Y'));

        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->value('incentivo_id');

        if ($incentivoId === null) {
            return response()->json(['message' => 'No hay datos registrados en el mes.']);
        }

        $data = DB::select(
            "WITH totales_producto AS (
                SELECT 
                    MAX(CASE WHEN tipo_producto = 'Tradicional' THEN total END) AS total_tradicional,
                    MAX(CASE WHEN tipo_producto = 'No Tradicional' THEN total END) AS total_no_tradicional,
                    MAX(CASE WHEN tipo_producto = 'Recarga' THEN total END) AS total_recarga,
                    MAX(CASE WHEN tipo_producto = 'Paquetico' THEN total END) AS total_paquetico
                FROM (
                    SELECT tipo_producto, SUM(monto_administrativo) AS total
                    FROM plan_agencias_distribucion pad
                    INNER JOIN agencias a
                        ON CAST(TRIM(pad.agencia_id) AS UNSIGNED) = CAST(a.terminal AS UNSIGNED)
                        AND a.aplica_incentivo = 1
                    WHERE pad.incentivo_id = $incentivoId AND pad.excedente > 0
                    GROUP BY pad.tipo_producto
                ) x
            ),
            empleado_info AS (
                SELECT 
                    e.porcentaje
                FROM porcentaje_administrativo e
                INNER JOIN empleados emp ON e.empleado_id = emp.empleadoid
                WHERE emp.cedula = '$cedula' 
                AND emp.companyid = $companyid
                LIMIT 1
            ),
            detalle AS (
                SELECT
                    'Tradicional' AS tipo_producto,
                    tp.total_tradicional AS total_tipo_producto,
                    ei.porcentaje,
                    (ei.porcentaje / 100 * tp.total_tradicional) AS total_a_pagar
                FROM totales_producto tp, empleado_info ei

                UNION ALL

                SELECT
                    'No Tradicional',
                    tp.total_no_tradicional,
                    ei.porcentaje,
                    (ei.porcentaje / 100 * tp.total_no_tradicional)
                FROM totales_producto tp, empleado_info ei

                UNION ALL

                SELECT
                    'Recarga',
                    tp.total_recarga,
                    ei.porcentaje,
                    (ei.porcentaje / 100 * tp.total_recarga)
                FROM totales_producto tp, empleado_info ei

                UNION ALL

                SELECT
                    'Paquetico',
                    tp.total_paquetico,
                    ei.porcentaje,
                    (ei.porcentaje / 100 * tp.total_paquetico)
                FROM totales_producto tp, empleado_info ei
            )

            -- 🔹 SALIDA FINAL
            SELECT
                tipo_producto,
                FORMAT(total_tipo_producto, 2) AS total_tipo_producto,
                FORMAT(porcentaje, 2) AS porcentaje,
                FORMAT(total_a_pagar, 2) AS total_a_pagar
            FROM detalle

            UNION ALL

            SELECT
                'TOTAL GENERAL',
                FORMAT(SUM(total_tipo_producto), 2),
                FORMAT(MAX(porcentaje), 2),
                FORMAT(SUM(total_a_pagar), 2)
            FROM detalle;"
        );

        return response()->json($data);
    }

    public function reportePagos()
    {
        return view('incentivos.reporte-pagos');
    }

    public function reporteNuevoIncentivoView()
    {
        return view('incentivos.reporte-nuevo-incentivo');
    }

    public function reporteNuevoIncentivo(Request $request)
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1G');

        $request->validate([
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
            'sistema' => 'nullable|in:Todos,Lotobet,Lotonet',
            'minimo_agencia' => 'nullable|numeric|min:0',
            'min_dias_venta' => 'nullable|integer|min:1',
            'filtro_cumplimiento' => 'nullable|in:todos,cumplidos,no_cumplidos',
            'pct_1' => 'nullable|numeric|min:0',
            'pct_2' => 'nullable|numeric|min:0',
            'pct_3' => 'nullable|numeric|min:0',
            'pct_4' => 'nullable|numeric|min:0',
        ]);

        $fechaIniSeleccionada = Carbon::parse($request->input('fecha_ini'))->toDateString();
        $fechaFinSeleccionada = Carbon::parse($request->input('fecha_fin'))->toDateString();

        // El "último mes" se define como el mes ANTERIOR completo según la fecha fin seleccionada.
        // Ejemplo: si fecha_fin está en febrero, se evalúa enero completo.
        $mesAnterior = Carbon::parse($fechaFinSeleccionada)->subMonthNoOverflow();
        $evalIni = $mesAnterior->copy()->startOfMonth()->toDateString();
        $evalFin = $mesAnterior->copy()->endOfMonth()->toDateString();

        $sistema = $request->input('sistema', 'Todos');
        $minimoAgencia = (float) $request->input('minimo_agencia', 80000);
        $minDiasVenta = (int) $request->input('min_dias_venta', 10);
        $filtroCumplimiento = $request->input('filtro_cumplimiento', 'todos');

        // Porcentaje real aplicado sobre ventas del mes actual (1 => 1%)
        $pct1 = (float) $request->input('pct_1', 1);
        $pct2 = (float) $request->input('pct_2', 2);
        $pct3 = (float) $request->input('pct_3', 3);
        $pct4 = (float) $request->input('pct_4', 4);

        $buildBaseQuery = function (string $desde, string $hasta) use ($sistema) {
            $betQuery = DB::table('vt_usuarios_bet')
                ->selectRaw("cedula, monto, fecha, 'Lotobet' as sistema")
                ->whereBetween('fecha', [$desde, $hasta]);

            $netQuery = DB::table('vt_usuarios_net')
                ->selectRaw("cedula, monto, fecha, 'Lotonet' as sistema")
                ->whereBetween('fecha', [$desde, $hasta]);

            if ($sistema === 'Lotobet') {
                return $betQuery;
            }

            if ($sistema === 'Lotonet') {
                return $netQuery;
            }

            return $betQuery->unionAll($netQuery);
        };

        $rowsUltimoMes = DB::query()
            ->fromSub($buildBaseQuery($evalIni, $evalFin), 'y')
            ->selectRaw('y.cedula, SUM(y.monto) AS ventas_ultimo_mes, COUNT(DISTINCT DATE(y.fecha)) AS dias_ventas_ultimo_mes')
            ->whereNotNull('y.cedula')
            ->where('y.cedula', '<>', '')
            ->groupBy('y.cedula')
            ->get();

        $rowsMesActual = DB::query()
            ->fromSub($buildBaseQuery($fechaIniSeleccionada, $fechaFinSeleccionada), 'z')
            ->selectRaw('z.cedula, SUM(z.monto) AS ventas_mes_actual, COUNT(DISTINCT DATE(z.fecha)) AS dias_ventas_mes_actual')
            ->whereNotNull('z.cedula')
            ->where('z.cedula', '<>', '')
            ->groupBy('z.cedula')
            ->get();

        $ultimoMesByCedula = $rowsUltimoMes->keyBy('cedula');
        $mesActualByCedula = $rowsMesActual->keyBy('cedula');
        $cedulas = $ultimoMesByCedula->keys()->merge($mesActualByCedula->keys())->unique()->values();

        $rawData = $cedulas->map(function ($cedula) use ($ultimoMesByCedula, $mesActualByCedula, $minimoAgencia, $minDiasVenta, $pct1, $pct2, $pct3, $pct4) {
            $rowUltimoMes = $ultimoMesByCedula->get($cedula);
            $rowMesActual = $mesActualByCedula->get($cedula);

            $ventas = $rowUltimoMes ? (float) $rowUltimoMes->ventas_ultimo_mes : 0;
            $ventasMesActual = $rowMesActual ? (float) $rowMesActual->ventas_mes_actual : 0;
            $diasMesActual = $rowMesActual ? (int) $rowMesActual->dias_ventas_mes_actual : 0;

            // Cumplimiento: mínimo vendido y mínimo de días en el mes actual (rango filtrado)
            $cumple = $ventasMesActual >= $minimoAgencia && $diasMesActual >= $minDiasVenta;

            $pct = 0.00;
            $factor = 0.00;

            if ($cumple) {
                if ($ventasMesActual < 100000) {
                    $pct = $pct1;
                } elseif ($ventasMesActual < 150000) {
                    $pct = $pct2;
                } elseif ($ventasMesActual < 200000) {
                    $pct = $pct3;
                } else {
                    $pct = $pct4;
                }

                $factor = $pct / 100;
            }

            return [
                'cedula' => $cedula,
                'ventas_num' => $ventas,
                'ventas_mes_actual_num' => $ventasMesActual,
                'dias_ventas_mes_actual' => $diasMesActual,
                'cumple_bool' => $cumple,
                'pct_num' => $pct,
                'nuevo_incentivo_num' => $ventasMesActual * $factor,
            ];
        })->sortByDesc('ventas_num')->values();

        if ($filtroCumplimiento === 'cumplidos') {
            $rawData = $rawData->where('cumple_bool', true)->values();
        } elseif ($filtroCumplimiento === 'no_cumplidos') {
            $rawData = $rawData->where('cumple_bool', false)->values();
        }

        // Total vendido debe reflejar la columna "Ventas Mes Actual"
        $totalVendido = (float) $rawData->sum('ventas_mes_actual_num');
        $totalIncentivo = (float) $rawData->sum('nuevo_incentivo_num');

        $data = $rawData->map(function ($row) use ($minimoAgencia) {
            $pctTexto = $row['pct_num'] > 0
                ? rtrim(rtrim(number_format($row['pct_num'], 2, '.', ''), '0'), '.') . '%'
                : '0%';

            return [
                'cedula' => $row['cedula'],
                'ventas_ultimo_mes' => number_format($row['ventas_num'], 2, '.', ','),
                'ventas_mes_actual' => number_format($row['ventas_mes_actual_num'], 2, '.', ','),
                'dias_ventas_mes_actual' => $row['dias_ventas_mes_actual'],
                'minimo_agencia' => number_format($minimoAgencia, 2, '.', ','),
                'cumple_minimo' => $row['cumple_bool'] ? 'SI' : 'NO',
                'pct_comision' => $pctTexto,
                'nuevo_incentivo' => number_format($row['nuevo_incentivo_num'], 2, '.', ','),
            ];
        })->values();

        return response()->json([
            'meta' => [
                'sistema' => $sistema,
                'fecha_ini' => $request->input('fecha_ini'),
                'fecha_fin' => $request->input('fecha_fin'),
                'eval_ini' => $evalIni,
                'eval_fin' => $evalFin,
                'minimo_agencia' => $minimoAgencia,
                'min_dias_venta' => $minDiasVenta,
                'filtro_cumplimiento' => $filtroCumplimiento,
                'pct_1' => $pct1,
                'pct_2' => $pct2,
                'pct_3' => $pct3,
                'pct_4' => $pct4,
                'total_vendido' => $totalVendido,
                'total_vendido_ultimo_mes' => (float) $rawData->sum('ventas_num'),
                'total_vendido_mes_actual' => (float) $rawData->sum('ventas_mes_actual_num'),
                'total_incentivo' => $totalIncentivo,
                'total_vendido_format' => number_format($totalVendido, 2, '.', ','),
                'total_vendido_ultimo_mes_format' => number_format((float) $rawData->sum('ventas_num'), 2, '.', ','),
                'total_vendido_mes_actual_format' => number_format((float) $rawData->sum('ventas_mes_actual_num'), 2, '.', ','),
                'total_incentivo_format' => number_format($totalIncentivo, 2, '.', ','),
            ],
            'data' => $data,
        ]);
    }

    public function reporteNuevoIncentivoV2View()
    {
        return view('incentivos.reporte-nuevo-incentivo-v2');
    }

    public function reporteNuevoIncentivoV2(Request $request)
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1G');

        $request->validate([
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
            'sistema' => 'nullable|in:Todos,Lotobet,Lotonet',
            'min_dias_venta' => 'nullable|integer|min:1',
            'filtro_cumplimiento' => 'nullable|in:todos,cumplidos,no_cumplidos',
            'tramo_activo' => 'nullable|in:tramo1,tramo2',
            'rangos_pago' => 'nullable|string',
        ]);

        $fechaIniSeleccionada = Carbon::parse($request->input('fecha_ini'))->toDateString();
        $fechaFinSeleccionada = Carbon::parse($request->input('fecha_fin'))->toDateString();

        $mesAnterior = Carbon::parse($fechaFinSeleccionada)->subMonthNoOverflow();
        $evalIni = $mesAnterior->copy()->startOfMonth()->toDateString();
        $evalFin = $mesAnterior->copy()->endOfMonth()->toDateString();

        $sistema = $request->input('sistema', 'Todos');
        $minDiasVenta = (int) $request->input('min_dias_venta', 10);
        $filtroCumplimiento = $request->input('filtro_cumplimiento', 'todos');
        $tramoActivo = $request->input('tramo_activo', 'tramo1');

        $rangosPagoDefault = [
            ['desde' => 100001, 'hasta' => 250000, 'pago' => 1000],
            ['desde' => 250001, 'hasta' => 400000, 'pago' => 2000],
            ['desde' => 400001, 'hasta' => 550000, 'pago' => 4000],
            ['desde' => 550001, 'hasta' => 700000, 'pago' => 6000],
            ['desde' => 700001, 'hasta' => 850000, 'pago' => 8000],
            ['desde' => 850001, 'hasta' => 1000000, 'pago' => 10000],
            ['desde' => 1000001, 'hasta' => 1150000, 'pago' => 12000],
            ['desde' => 1150001, 'hasta' => 1300000, 'pago' => 14000],
            ['desde' => 1300001, 'hasta' => 1450000, 'pago' => 16000],
            ['desde' => 1450001, 'hasta' => 1600000, 'pago' => 18000],
            ['desde' => 1600001, 'hasta' => 1750000, 'pago' => 20000],
            ['desde' => 1750001, 'hasta' => 1900000, 'pago' => 22000],
            ['desde' => 1900001, 'hasta' => 2050000, 'pago' => 24000],
            ['desde' => 2050001, 'hasta' => 2200000, 'pago' => 26000],
            ['desde' => 2200001, 'hasta' => 2350000, 'pago' => 28000],
            ['desde' => 2350001, 'hasta' => 2500000, 'pago' => 30000],
            ['desde' => 2500001, 'hasta' => 2650000, 'pago' => 32000],
            ['desde' => 2650001, 'hasta' => 2800000, 'pago' => 34000],
            ['desde' => 2800001, 'hasta' => 2950000, 'pago' => 36000],
            ['desde' => 2950001, 'hasta' => 3100000, 'pago' => 38000],
            ['desde' => 3100001, 'hasta' => 3250000, 'pago' => 40000],
            ['desde' => 3250001, 'hasta' => 3400000, 'pago' => 42000],
            ['desde' => 3400001, 'hasta' => 3550000, 'pago' => 44000],
            ['desde' => 3550001, 'hasta' => 3700000, 'pago' => 46000],
            ['desde' => 3700001, 'hasta' => 3850000, 'pago' => 48000],
            ['desde' => 3850001, 'hasta' => 5000000, 'pago' => 50000],
        ];

        $rangosPago = $rangosPagoDefault;
        $rangosPagoInput = $request->input('rangos_pago');
        if (is_string($rangosPagoInput) && trim($rangosPagoInput) !== '') {
            $decoded = json_decode($rangosPagoInput, true);
            if (is_array($decoded) && count($decoded) > 0) {
                $sanitized = collect($decoded)
                    ->map(function ($row) {
                        if (!is_array($row)) {
                            return null;
                        }

                        $desde = isset($row['desde']) ? (float) $row['desde'] : 0;
                        $hasta = isset($row['hasta']) ? (float) $row['hasta'] : 0;
                        $pago = isset($row['pago']) ? (float) $row['pago'] : 0;

                        if ($desde < 0 || $hasta < 0 || $pago < 0 || $desde > $hasta) {
                            return null;
                        }

                        return [
                            'desde' => $desde,
                            'hasta' => $hasta,
                            'pago' => $pago,
                        ];
                    })
                    ->filter()
                    ->sortBy('desde')
                    ->values()
                    ->all();

                if (!empty($sanitized)) {
                    $rangosPago = $sanitized;
                }
            }
        }

        $buildBaseQuery = function (string $desde, string $hasta) use ($sistema) {
            $betQuery = DB::table('vt_usuarios_bet')
                ->selectRaw("cedula, monto, fecha, 'Lotobet' as sistema")
                ->whereBetween('fecha', [$desde, $hasta]);

            $netQuery = DB::table('vt_usuarios_net')
                ->selectRaw("cedula, monto, fecha, 'Lotonet' as sistema")
                ->whereBetween('fecha', [$desde, $hasta]);

            if ($sistema === 'Lotobet') {
                return $betQuery;
            }

            if ($sistema === 'Lotonet') {
                return $netQuery;
            }

            return $betQuery->unionAll($netQuery);
        };

        $rowsUltimoMes = DB::query()
            ->fromSub($buildBaseQuery($evalIni, $evalFin), 'y')
            ->selectRaw('y.cedula, SUM(y.monto) AS ventas_ultimo_mes, COUNT(DISTINCT DATE(y.fecha)) AS dias_ventas_ultimo_mes')
            ->whereNotNull('y.cedula')
            ->where('y.cedula', '<>', '')
            ->groupBy('y.cedula')
            ->get();

        $rowsMesActual = DB::query()
            ->fromSub($buildBaseQuery($fechaIniSeleccionada, $fechaFinSeleccionada), 'z')
            ->selectRaw('z.cedula, SUM(z.monto) AS ventas_mes_actual, COUNT(DISTINCT DATE(z.fecha)) AS dias_ventas_mes_actual')
            ->whereNotNull('z.cedula')
            ->where('z.cedula', '<>', '')
            ->groupBy('z.cedula')
            ->get();

        $ultimoMesByCedula = $rowsUltimoMes->keyBy('cedula');
        $mesActualByCedula = $rowsMesActual->keyBy('cedula');
        $cedulas = $ultimoMesByCedula->keys()->merge($mesActualByCedula->keys())->unique()->values();
        $empresaByCedula = [];

        if ($cedulas->isNotEmpty()) {
            $buildAgencyTerminalQuery = function (string $tabla) use ($fechaIniSeleccionada, $fechaFinSeleccionada) {
                return DB::table($tabla)
                    ->selectRaw('cedula, TRIM(CAST(agencia_id AS CHAR)) AS terminal, COUNT(*) AS total')
                    ->whereBetween('fecha', [$fechaIniSeleccionada, $fechaFinSeleccionada])
                    ->whereNotNull('cedula')
                    ->where('cedula', '<>', '')
                    ->whereNotNull('agencia_id')
                    ->whereRaw("TRIM(CAST(agencia_id AS CHAR)) <> ''")
                    ->groupBy('cedula', DB::raw('TRIM(CAST(agencia_id AS CHAR))'));
            };

            if ($sistema === 'Lotobet') {
                $terminalSourceQuery = $buildAgencyTerminalQuery('vt_usuarios_bet');
            } elseif ($sistema === 'Lotonet') {
                $terminalSourceQuery = $buildAgencyTerminalQuery('vt_usuarios_net');
            } else {
                $terminalSourceQuery = $buildAgencyTerminalQuery('vt_usuarios_bet')
                    ->unionAll($buildAgencyTerminalQuery('vt_usuarios_net'));
            }

            $terminalRows = DB::query()
                ->fromSub($terminalSourceQuery, 'terminales_usuario')
                ->selectRaw('cedula, terminal, SUM(total) AS total')
                ->groupBy('cedula', 'terminal')
                ->orderBy('cedula')
                ->orderByDesc('total')
                ->get();

            $terminales = $terminalRows
                ->pluck('terminal')
                ->map(function ($terminal) {
                    return trim((string) $terminal);
                })
                ->filter(function ($terminal) {
                    return $terminal !== '';
                })
                ->unique()
                ->values();

            $empresaByTerminal = [];
            foreach ($terminales->chunk(1000) as $terminalChunk) {
                DB::table('agencias')
                    ->whereIn(DB::raw('TRIM(CAST(terminal AS CHAR))'), $terminalChunk->all())
                    ->selectRaw("TRIM(CAST(terminal AS CHAR)) AS terminal, COALESCE(NULLIF(TRIM(empresa), ''), 'Sin empresa') AS empresa")
                    ->orderBy('terminal')
                    ->get()
                    ->each(function ($row) use (&$empresaByTerminal) {
                        $empresaByTerminal[(string) $row->terminal] = (string) $row->empresa;
                    });
            }

            $empresaCounterByCedula = [];
            foreach ($terminalRows as $row) {
                $cedulaKey = (string) $row->cedula;
                $terminal = trim((string) $row->terminal);
                $empresa = $empresaByTerminal[$terminal] ?? 'Sin empresa';

                if (!isset($empresaCounterByCedula[$cedulaKey])) {
                    $empresaCounterByCedula[$cedulaKey] = [];
                }

                $empresaCounterByCedula[$cedulaKey][$empresa] = ($empresaCounterByCedula[$cedulaKey][$empresa] ?? 0) + (int) $row->total;
            }

            foreach ($empresaCounterByCedula as $cedulaKey => $empresas) {
                arsort($empresas);
                $empresaByCedula[$cedulaKey] = (string) array_key_first($empresas);
            }
        }

        $rawData = $cedulas->map(function ($cedula) use ($ultimoMesByCedula, $mesActualByCedula, $minDiasVenta, $rangosPago, $tramoActivo, $empresaByCedula) {
            $rowUltimoMes = $ultimoMesByCedula->get($cedula);
            $rowMesActual = $mesActualByCedula->get($cedula);

            $ventas = $rowUltimoMes ? (float) $rowUltimoMes->ventas_ultimo_mes : 0;
            $ventasMesActual = $rowMesActual ? (float) $rowMesActual->ventas_mes_actual : 0;
            $diasMesActual = $rowMesActual ? (int) $rowMesActual->dias_ventas_mes_actual : 0;
            $empresa = (string) ($empresaByCedula[(string) $cedula] ?? 'Sin empresa');

            $cumple = $diasMesActual >= $minDiasVenta;
            $pagoEscala = 0.00;

            if ($cumple) {
                foreach ($rangosPago as $rango) {
                    if ($ventasMesActual >= (float) $rango['desde'] && $ventasMesActual <= (float) $rango['hasta']) {
                        if ($tramoActivo === 'tramo2' && (float) $rango['desde'] >= 1000001) {
                            $pagoEscala = $ventasMesActual * ((float) $rango['pago'] / 100);
                        } else {
                            $pagoEscala = (float) $rango['pago'];
                        }
                        break;
                    }
                }

                if ($pagoEscala === 0.0 && !empty($rangosPago)) {
                    $ultimoRango = end($rangosPago);
                    if ($ventasMesActual >= (float) $ultimoRango['desde']) {
                        if ($tramoActivo === 'tramo2' && (float) $ultimoRango['desde'] >= 1000001) {
                            $pagoEscala = $ventasMesActual * ((float) $ultimoRango['pago'] / 100);
                        } else {
                            $pagoEscala = (float) $ultimoRango['pago'];
                        }
                    }
                    reset($rangosPago);
                }
            }

            return [
                'cedula' => $cedula,
                'empresa' => $empresa,
                'ventas_num' => $ventas,
                'ventas_mes_actual_num' => $ventasMesActual,
                'dias_ventas_mes_actual' => $diasMesActual,
                'cumple_bool' => $cumple,
                'pago_escala_num' => $pagoEscala,
                'nuevo_incentivo_num' => $pagoEscala,
            ];
        })->sortByDesc('ventas_num')->values();

        if ($filtroCumplimiento === 'cumplidos') {
            $rawData = $rawData->where('cumple_bool', true)->values();
        } elseif ($filtroCumplimiento === 'no_cumplidos') {
            $rawData = $rawData->where('cumple_bool', false)->values();
        }

        $totalVendido = (float) $rawData->sum('ventas_mes_actual_num');
        $totalIncentivo = (float) $rawData->sum('nuevo_incentivo_num');

        $data = $rawData->map(function ($row) {
            return [
                'cedula' => $row['cedula'],
                'empresa' => $row['empresa'] ?? 'Sin empresa',
                'ventas_ultimo_mes' => number_format($row['ventas_num'], 2, '.', ','),
                'ventas_mes_actual' => number_format($row['ventas_mes_actual_num'], 2, '.', ','),
                'dias_ventas_mes_actual' => $row['dias_ventas_mes_actual'],
                'cumple_minimo' => $row['cumple_bool'] ? 'SI' : 'NO',
                'pago_escala' => number_format($row['pago_escala_num'], 2, '.', ','),
                'nuevo_incentivo' => number_format($row['nuevo_incentivo_num'], 2, '.', ','),
            ];
        })->values();

        return response()->json([
            'meta' => [
                'sistema' => $sistema,
                'fecha_ini' => $request->input('fecha_ini'),
                'fecha_fin' => $request->input('fecha_fin'),
                'eval_ini' => $evalIni,
                'eval_fin' => $evalFin,
                'min_dias_venta' => $minDiasVenta,
                'filtro_cumplimiento' => $filtroCumplimiento,
                'tramo_activo' => $tramoActivo,
                'rangos_pago' => $rangosPago,
                'total_vendido' => $totalVendido,
                'total_vendido_ultimo_mes' => (float) $rawData->sum('ventas_num'),
                'total_vendido_mes_actual' => (float) $rawData->sum('ventas_mes_actual_num'),
                'total_incentivo' => $totalIncentivo,
                'total_vendido_format' => number_format($totalVendido, 2, '.', ','),
                'total_vendido_ultimo_mes_format' => number_format((float) $rawData->sum('ventas_num'), 2, '.', ','),
                'total_vendido_mes_actual_format' => number_format((float) $rawData->sum('ventas_mes_actual_num'), 2, '.', ','),
                'total_incentivo_format' => number_format($totalIncentivo, 2, '.', ','),
            ],
            'data' => $data,
        ]);
    }

    public function reporteNuevoIncentivoV3View()
    {
        $coordinadores = CoordinadorOperador::query()
            ->where('puesto', 'coordinador')
            ->withCount('agencias')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get(['id', 'nombre', 'apellido'])
            ->map(function ($coordinador) {
                return [
                    'id' => $coordinador->id,
                    'nombre' => trim(($coordinador->nombre ?? '') . ' ' . ($coordinador->apellido ?? '')),
                    'agencias' => (int) $coordinador->agencias_count,
                    'agencias_validas' => 0,
                    'monto_usuarios' => 0,
                    'pct' => 0.0055,
                ];
            })
            ->values();

        return view('incentivos.reporte-nuevo-incentivo-v3', compact('coordinadores'));
    }

    public function reporteNuevoIncentivoV3(Request $request)
    {
        $request->merge([
            'tramo_activo' => 'tramo2',
        ]);

        $response = $this->reporteNuevoIncentivoV2($request);
        $payload = $response->getData(true);

        if (!isset($payload['data']) || !is_array($payload['data'])) {
            return $response;
        }

        $totalIncentivo = 0;
        foreach ($payload['data'] as &$row) {
            $ventasMesActual = (float) str_replace(',', '', $row['ventas_mes_actual'] ?? 0);
            $nuevoIncentivo = (float) str_replace(',', '', $row['nuevo_incentivo'] ?? 0);

            if ($ventasMesActual >= 1000001 && $nuevoIncentivo > 50000) {
                $nuevoIncentivo = 50000;
                $row['pago_escala'] = number_format($nuevoIncentivo, 2, '.', ',');
                $row['nuevo_incentivo'] = number_format($nuevoIncentivo, 2, '.', ',');
            }

            $totalIncentivo += $nuevoIncentivo;
        }
        unset($row);

        $qualifiedCedulas = collect($payload['data'])
            ->filter(function ($row) {
                return ($row['cumple_minimo'] ?? 'NO') === 'SI'
                    && (float) str_replace(',', '', $row['nuevo_incentivo'] ?? 0) > 0;
            })
            ->pluck('cedula')
            ->filter()
            ->unique()
            ->values();

        $coordinatorValidAgencies = [];
        $coordinatorUserIncentiveAmounts = [];
        $coordinatorUserDetails = [];
        if ($qualifiedCedulas->isNotEmpty()) {
            $incentiveByCedula = collect($payload['data'])
                ->filter(function ($row) {
                    return ($row['cumple_minimo'] ?? 'NO') === 'SI'
                        && (float) str_replace(',', '', $row['nuevo_incentivo'] ?? 0) > 0;
                })
                ->mapWithKeys(function ($row) {
                    return [
                        (string) $row['cedula'] => (float) str_replace(',', '', $row['nuevo_incentivo'] ?? 0),
                    ];
                });
            $employeeNamesByCedula = DB::table('empleados')
                ->whereIn('cedula', $qualifiedCedulas->all())
                ->selectRaw("CAST(cedula AS CHAR) AS cedula, TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, ''))) AS nombre")
                ->get()
                ->mapWithKeys(function ($row) {
                    return [(string) $row->cedula => (string) $row->nombre];
                });

            $fechaIniSeleccionada = Carbon::parse($request->input('fecha_ini'))->toDateString();
            $fechaFinSeleccionada = Carbon::parse($request->input('fecha_fin'))->toDateString();
            $sistema = $request->input('sistema', 'Todos');

            $qualifiedCedulaSet = $qualifiedCedulas
                ->mapWithKeys(function ($cedula) {
                    return [(string) $cedula => true];
                });

            $buildAgencyQuery = function (string $tabla) use ($fechaIniSeleccionada, $fechaFinSeleccionada) {
                return DB::table($tabla)
                    ->selectRaw('cedula, TRIM(CAST(agencia_id AS CHAR)) AS terminal, COUNT(*) AS total')
                    ->whereBetween('fecha', [$fechaIniSeleccionada, $fechaFinSeleccionada])
                    ->whereNotNull('cedula')
                    ->where('cedula', '<>', '')
                    ->whereNotNull('agencia_id')
                    ->whereRaw("TRIM(CAST(agencia_id AS CHAR)) <> ''")
                    ->groupBy('cedula', DB::raw('TRIM(CAST(agencia_id AS CHAR))'));
            };

            if ($sistema === 'Lotobet') {
                $validTerminalQuery = $buildAgencyQuery('vt_usuarios_bet');
            } elseif ($sistema === 'Lotonet') {
                $validTerminalQuery = $buildAgencyQuery('vt_usuarios_net');
            } else {
                $validTerminalQuery = $buildAgencyQuery('vt_usuarios_bet')
                    ->unionAll($buildAgencyQuery('vt_usuarios_net'));
            }

            $validCedulaTerminals = DB::query()
                ->fromSub($validTerminalQuery, 'valid_agencies')
                ->select('cedula', 'terminal')
                ->groupBy('cedula', 'terminal')
                ->get()
                ->filter(function ($row) use ($qualifiedCedulaSet) {
                    return isset($qualifiedCedulaSet[(string) $row->cedula]);
                })
                ->values();

            $validTerminals = $validCedulaTerminals
                ->pluck('terminal')
                ->unique()
                ->values();

            if ($validTerminals->isNotEmpty()) {
                $coordinatorAgencyRows = DB::table('coordinador_operador_agencia as coa')
                    ->join('agencias as a', 'a.id', '=', 'coa.agencia_id')
                    ->join('coordinador_operador as co', 'co.id', '=', 'coa.coordinador_operador_id')
                    ->where('co.puesto', 'coordinador')
                    ->whereIn(DB::raw('TRIM(CAST(a.terminal AS CHAR))'), $validTerminals->all())
                    ->selectRaw('coa.coordinador_operador_id, coa.agencia_id, TRIM(CAST(a.terminal AS CHAR)) AS terminal')
                    ->get();

                $coordinatorValidAgencies = $coordinatorAgencyRows
                    ->groupBy('coordinador_operador_id')
                    ->map(function ($rows) {
                        return $rows->pluck('agencia_id')->unique()->count();
                    })
                    ->mapWithKeys(function ($total, $coordinadorId) {
                        return [(string) $coordinadorId => (int) $total];
                    })
                    ->all();

                $coordinatorTerminals = $coordinatorAgencyRows
                    ->groupBy('coordinador_operador_id')
                    ->map(function ($rows) {
                        return $rows->pluck('terminal')->unique()->flip();
                    })
                    ->all();

                $coordinatorCedulas = [];
                foreach ($validCedulaTerminals as $row) {
                    foreach ($coordinatorTerminals as $coordinadorId => $terminales) {
                        if (isset($terminales[$row->terminal])) {
                            $coordinatorCedulas[(string) $coordinadorId][(string) $row->cedula] = true;
                        }
                    }
                }

                foreach ($coordinatorCedulas as $coordinadorId => $cedulasMap) {
                    $cedulas = array_keys($cedulasMap);
                    $coordinatorUserIncentiveAmounts[(string) $coordinadorId] = collect($cedulas)
                        ->sum(function ($cedula) use ($incentiveByCedula) {
                            return (float) ($incentiveByCedula[(string) $cedula] ?? 0);
                        });
                    $coordinatorUserDetails[(string) $coordinadorId] = collect($cedulas)
                        ->map(function ($cedula) use ($incentiveByCedula, $employeeNamesByCedula) {
                            $cedulaString = (string) $cedula;

                            return [
                                'cedula' => $cedulaString,
                                'usuario' => $employeeNamesByCedula[$cedulaString] ?? '',
                                'incentivo' => (float) ($incentiveByCedula[$cedulaString] ?? 0),
                            ];
                        })
                        ->sortByDesc('incentivo')
                        ->values()
                        ->all();
                }
            }
        }

        $payload['meta']['tipo_pago'] = $request->input('tipo_pago', 'tramos_60');
        $payload['meta']['tramo_activo'] = 'incentivo_v3';
        $payload['meta']['coordinador_agencias_validas'] = $coordinatorValidAgencies;
        $payload['meta']['coordinador_monto_usuarios'] = $coordinatorUserIncentiveAmounts;
        $payload['meta']['coordinador_detalle_usuarios'] = $coordinatorUserDetails;
        $payload['meta']['total_incentivo'] = $totalIncentivo;
        $payload['meta']['total_incentivo_format'] = number_format($totalIncentivo, 2, '.', ',');

        return response()->json($payload);
    }

    public function reporteNuevoIncentivoV4View()
    {
        $coordinadores = CoordinadorOperador::query()
            ->where('puesto', 'coordinador')
            ->withCount('agencias')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get(['id', 'nombre', 'apellido'])
            ->map(function ($coordinador) {
                return [
                    'id' => $coordinador->id,
                    'nombre' => trim(($coordinador->nombre ?? '') . ' ' . ($coordinador->apellido ?? '')),
                    'agencias' => (int) $coordinador->agencias_count,
                    'agencias_validas' => 0,
                    'monto_usuarios' => 0,
                    'pct' => 0.0055,
                ];
            })
            ->values();

        $administrativosConfig = IncentivoAdministrativo::query()
            ->orderBy('grupo')
            ->orderBy('empresa')
            ->orderBy('nombre')
            ->get(['grupo', 'nombre', 'empresa', 'pct_total'])
            ->map(function ($row) {
                return [
                    'grupo' => (string) ($row->grupo ?? ''),
                    'nombre' => (string) ($row->nombre ?? ''),
                    'empresa' => (string) ($row->empresa ?? ''),
                    'pct' => (float) ($row->pct_total ?? 0),
                ];
            })
            ->values();

        return view('incentivos.reporte-nuevo-incentivo-v4', compact('coordinadores', 'administrativosConfig'));
    }

    public function reporteNuevoIncentivoV4(Request $request)
    {
        $response = $this->reporteNuevoIncentivoV3($request);
        $payload = $response->getData(true);

        if (isset($payload['meta']) && is_array($payload['meta'])) {
            $payload['meta']['tramo_activo'] = 'incentivo_v4';
        }

        return response()->json($payload, $response->status());
    }

    public function reportePagoIncentivos(Request $request)
    {
        ini_set('max_execution_time', 600); // 10 minutes
        ini_set('memory_limit', '1G');

        $empresaId = '%';
        $empresa = $request->input('empresa');
        if (!empty($empresa)) {
            $empresaId = $empresa;
        }

        $incentivoId = '%';
        $mes = $request->input('mes');
        if (empty($mes)) {
            return response()->json(['message' => 'Seleccione mes.'], 400);
        }
        $anio = $request->input('year', date('Y'));
        $incentivoId = DB::table('incentivo_temporal_c')
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->value('incentivo_id');

        if ($incentivoId === null) {
            return response()->json(['message' => 'No hay datos registrados en el mes.']);
        }

        $tipoId = '%';
        $tipo = $request->input('tipo');
        if (!empty($tipo)) {
            $tipoId = $tipo;
        }

        $califican = $request->input('califican', '1'); // 1=Todos, 2=Califican, 3=No Califican
        $horas = $request->input('horas', '1'); // 1=Todos, 2=> 150
        $pago = $request->input('pago', '1'); // 1=Todos, 2=< $200.00

        // Construir el filtro de faltantes según el parámetro califican
        $filtroFaltantes = '';
        if ($califican === '2') {
            // Califican: excluir los que tienen faltantes (NOT IN)
            $filtroFaltantes = "AND CAST(e.cedula AS SIGNED) NOT IN (
                    SELECT CAST(identificacion AS SIGNED) FROM faltantes_bet WHERE YEAR(fecha) = $anio AND MONTH(fecha) = $mes
                    UNION ALL
                    SELECT CAST(identificacion AS SIGNED) FROM faltantes_net WHERE YEAR(fecha) = $anio AND MONTH(fecha) = $mes
                )";
        } elseif ($califican === '3') {
            // No califican: incluir solo los que tienen faltantes (IN)
            $filtroFaltantes = "AND CAST(e.cedula AS SIGNED) IN (
                    SELECT CAST(identificacion AS SIGNED) FROM faltantes_bet WHERE YEAR(fecha) = $anio AND MONTH(fecha) = $mes
                    UNION ALL
                    SELECT CAST(identificacion AS SIGNED) FROM faltantes_net WHERE YEAR(fecha) = $anio AND MONTH(fecha) = $mes
                )";
        }
        // Si califican === '1' (Todos), no se aplica ningún filtro

        // Construir filtro de horas
        $filtroHoras = '';
        if ($horas === '2') {
            $filtroHoras = "AND EXISTS (
                    SELECT 1 FROM (
                        SELECT combined.cedula AS emp_cedula, SUM(combined.total_horas) AS horas_totales
                        FROM (
                            SELECT ab.cedula, SUM(TIMESTAMPDIFF(HOUR, ab.primer_login, ab.ultimo_login)) AS total_horas
                            FROM asistencias_bet ab
                            WHERE YEAR(ab.fecha) = $anio AND MONTH(ab.fecha) = $mes
                            GROUP BY ab.cedula
                            UNION ALL
                            SELECT an.identificacion AS cedula, SUM(TIMESTAMPDIFF(HOUR, an.entrada, an.salida)) AS total_horas
                            FROM asistencias_net an
                            WHERE YEAR(an.entrada) = $anio AND MONTH(an.entrada) = $mes
                            GROUP BY an.identificacion
                        ) combined
                        GROUP BY combined.cedula
                        HAVING SUM(combined.total_horas) > 150
                    ) a
                    WHERE CAST(a.emp_cedula AS SIGNED) = CAST(e.cedula AS SIGNED)
                )";
        }

        // Construir filtro de pago
        $filtroPago = '';
        if ($pago === '2') {
            $filtroPago = 'AND t.total_monto < 200';
        }

        $data = DB::select(
            "SELECT 
                CASE 
                    WHEN e.companyid = 168 THEN 'Joselito'
                    WHEN e.companyid = 169 THEN 'Negosur'
                    ELSE 'Otra Empresa'
                END AS company,
                CASE
                    WHEN e.tipo_empleado_incentivo = '1' THEN 'Agente de venta'
                    WHEN e.tipo_empleado_incentivo = '2' THEN 'Coordinador'
                    WHEN e.tipo_empleado_incentivo = '3' THEN 'Administrativo'
                    WHEN e.tipo_empleado_incentivo = '4' THEN 'Operador'
                END AS tipo,
                e.empleadoid AS empleado_id,
                CONCAT(e.nombres, ' ', e.apellidos) AS nombres,
                e.cedula,
                e.ctabanco AS cuenta,
                FORMAT(t.total_monto, 2) AS monto
            FROM (
                SELECT 
                    cedula,
                    empleadoid,
                    SUM(monto_a_pagar) AS total_monto,
                    incentivo_id
                FROM (
                    -- Agentes
                    SELECT 
                        cedula,
                        empleadoid,
                        monto_incentivo AS monto_a_pagar,
                        incentivo_id
                    FROM pago_incentivos
                    UNION ALL
                    -- Administrativos
                    SELECT 
                        cedula,
                        empleadoid,
                        total AS monto_a_pagar,
                        incentivo_id
                    FROM pago_incentivos_admin
                    UNION ALL
                    -- Coordinadores
                    SELECT 
                        cedula,
                        empleadoid,
                        total AS monto_a_pagar,
                        incentivo_id
                    FROM pago_incentivos_coordinador
                ) pagos
                GROUP BY cedula, empleadoid, incentivo_id
            ) t
            LEFT JOIN empleados e ON CAST(t.cedula AS SIGNED) = CAST(e.cedula AS SIGNED)
                AND e.empleadoid = t.empleadoid
            WHERE t.incentivo_id LIKE '$incentivoId'
                AND t.total_monto > 0
                AND e.companyid LIKE '$empresaId'
                AND e.tipo_empleado_incentivo LIKE '$tipoId'
                AND e.fechasalida IS NULL
                $filtroFaltantes
                $filtroHoras
                $filtroPago;"
        );

        return response()->json($data);
    }
}

