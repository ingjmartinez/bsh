<?php

namespace App\Http\Controllers;

use App\Exports\FaltantesExport;
use App\Exports\VentasUsuarioExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    private function tipoProductoSql(string $ventaAlias = 'v', string $catalogoAlias = 'c'): string
    {
        $descripcion = "UPPER(COALESCE({$ventaAlias}.descripcion, ''))";

        return "LOWER(COALESCE(NULLIF(TRIM({$catalogoAlias}.tipo), ''), CASE
            WHEN {$descripcion} LIKE '%RECARG%' THEN 'recarga'
            WHEN {$descripcion} LIKE '%PAQUET%' THEN 'paquetico'
            WHEN {$descripcion} LIKE '%LOTO%' OR {$descripcion} LIKE '%POOL%' THEN 'no tradicional'
            WHEN {$descripcion} LIKE '%QUINIELA%' OR {$descripcion} LIKE '%PALE%' OR {$descripcion} LIKE '%TRIPLETA%' THEN 'tradicional'
            ELSE 'tradicional'
        END))";
    }

    public function indexReportes()
    {
        $reportes = collect(config('reportes', []))
            ->filter(fn ($reporte) => (bool) ($reporte['activo'] ?? true))
            ->map(function ($reporte) {
                $reporte['url'] = url($reporte['url']);
                $reporte['tags'] = $reporte['tags'] ?? [];

                return $reporte;
            })
            ->sortBy('nombre')
            ->values();

        $categorias = $reportes
            ->pluck('categoria')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('reportes.index', compact('reportes', 'categorias'));
    }

    function ventasUsuarioBet(Request $request)
    {
        return view('reportes.ventas-usuario-bet');
    }

    public function compensacion()
    {
        return view('reportes.compensacion');
    }

    public function listCompensacion(Request $request)
    {
        $validated = $request->validate([
            'sistema' => 'required|in:todos,lotobet,lotonet',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = $validated['fecha_inicio'];
        $fechaFin = $validated['fecha_fin'];
        $uniones = [];
        $bindings = [];

        if (in_array($validated['sistema'], ['todos', 'lotobet'], true)) {
            $uniones[] = "
                SELECT
                    CAST(NULLIF(TRIM(pagado_consorcio_id), '') AS UNSIGNED) AS consorcio_id,
                    CAST(NULLIF(TRIM(producto_id), '') AS UNSIGNED) AS producto_id,
                    COALESCE(monto, 0) AS aotra_bet,
                    0 AS aotra_net,
                    0 AS porotra_bet,
                    0 AS porotra_net
                FROM pagos_aotra_empresa_bet
                WHERE fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY)
            ";
            $bindings[] = $fechaInicio;
            $bindings[] = $fechaFin;

            $uniones[] = "
                SELECT
                    CAST(NULLIF(TRIM(pagado_consorcio_id), '') AS UNSIGNED) AS consorcio_id,
                    CAST(NULLIF(TRIM(producto_id), '') AS UNSIGNED) AS producto_id,
                    0 AS aotra_bet,
                    0 AS aotra_net,
                    COALESCE(monto, 0) AS porotra_bet,
                    0 AS porotra_net
                FROM pagos_porotra_empresa_bet
                WHERE fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY)
            ";
            $bindings[] = $fechaInicio;
            $bindings[] = $fechaFin;
        }

        if (in_array($validated['sistema'], ['todos', 'lotonet'], true)) {
            $uniones[] = "
                SELECT
                    CAST(NULLIF(TRIM(pagado_a_consorcio_id), '') AS UNSIGNED) AS consorcio_id,
                    CAST(NULLIF(TRIM(producto_id), '') AS UNSIGNED) AS producto_id,
                    0 AS aotra_bet,
                    COALESCE(monto, 0) AS aotra_net,
                    0 AS porotra_bet,
                    0 AS porotra_net
                FROM pagos_aotra_empresa_net
                WHERE fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY)
            ";
            $bindings[] = $fechaInicio;
            $bindings[] = $fechaFin;

            $uniones[] = "
                SELECT
                    CAST(NULLIF(TRIM(pagado_consorcio_id), '') AS UNSIGNED) AS consorcio_id,
                    CAST(NULLIF(TRIM(producto_id), '') AS UNSIGNED) AS producto_id,
                    0 AS aotra_bet,
                    0 AS aotra_net,
                    0 AS porotra_bet,
                    COALESCE(monto, 0) AS porotra_net
                FROM pagos_porotra_empresa_net
                WHERE fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY)
            ";
            $bindings[] = $fechaInicio;
            $bindings[] = $fechaFin;
        }

        $movimientosSql = implode(' UNION ALL ', $uniones);
        $catalogoTradicionalSql = "
            SELECT DISTINCT
                CAST(NULLIF(TRIM(producto_id), '') AS UNSIGNED) AS producto_id,
                TRIM(UPPER(tipo)) AS tipo
            FROM catalogo_juegos
        ";
        $sql = "
            SELECT
                x.consorcios,
                x.aotra_bet,
                x.aotra_net,
                x.porotra_bet,
                x.porotra_net,
                x.total_general,
                x.orden
            FROM (
                SELECT
                    co.consorcios,
                    COALESCE(SUM(p.aotra_bet), 0) AS aotra_bet,
                    COALESCE(SUM(p.aotra_net), 0) AS aotra_net,
                    COALESCE(SUM(p.porotra_bet), 0) AS porotra_bet,
                    COALESCE(SUM(p.porotra_net), 0) AS porotra_net,
                    COALESCE(SUM(p.aotra_bet + p.aotra_net + p.porotra_bet + p.porotra_net), 0) AS total_general,
                    1 AS orden
                FROM (
                    {$movimientosSql}
                ) p
                INNER JOIN ({$catalogoTradicionalSql}) cj
                    ON p.producto_id = cj.producto_id
                    AND cj.tipo = 'TRADICIONAL'
                INNER JOIN consorcios co ON p.consorcio_id = co.id
                WHERE p.consorcio_id IS NOT NULL
                  AND p.consorcio_id <> 0
                GROUP BY co.consorcios

                UNION ALL

                SELECT
                    'TOTAL' AS consorcios,
                    COALESCE(SUM(p.aotra_bet), 0) AS aotra_bet,
                    COALESCE(SUM(p.aotra_net), 0) AS aotra_net,
                    COALESCE(SUM(p.porotra_bet), 0) AS porotra_bet,
                    COALESCE(SUM(p.porotra_net), 0) AS porotra_net,
                    COALESCE(SUM(p.aotra_bet + p.aotra_net + p.porotra_bet + p.porotra_net), 0) AS total_general,
                    2 AS orden
                FROM (
                    {$movimientosSql}
                ) p
                INNER JOIN ({$catalogoTradicionalSql}) cj
                    ON p.producto_id = cj.producto_id
                    AND cj.tipo = 'TRADICIONAL'
            ) x
            ORDER BY x.orden, x.total_general DESC, x.consorcios
        ";

        $data = DB::select($sql, array_merge($bindings, $bindings));
        $totalRow = collect($data)->firstWhere('consorcios', 'TOTAL');

        $totalAotraBet = (float) ($totalRow->aotra_bet ?? 0);
        $totalAotraNet = (float) ($totalRow->aotra_net ?? 0);
        $totalPorotraBet = (float) ($totalRow->porotra_bet ?? 0);
        $totalPorotraNet = (float) ($totalRow->porotra_net ?? 0);
        $totalGeneral = $totalAotraBet + $totalAotraNet + $totalPorotraBet + $totalPorotraNet;

        return response()->json([
            'resumen' => [
                'sistema' => match ($validated['sistema']) {
                    'lotobet' => 'Lotobet Real',
                    'lotonet' => 'Lotonet Lotedom',
                    default => 'Todas',
                },
                'aotra_bet' => round($totalAotraBet, 2),
                'aotra_net' => round($totalAotraNet, 2),
                'porotra_bet' => round($totalPorotraBet, 2),
                'porotra_net' => round($totalPorotraNet, 2),
                'total_lotobet' => round($totalAotraBet + $totalPorotraBet, 2),
                'total_lotonet' => round($totalAotraNet + $totalPorotraNet, 2),
                'total_general' => round($totalGeneral, 2),
                'registros' => count($data),
            ],
            'data' => $data,
        ]);
    }

    public function listVentasUsuarioBet(Request $request)
    {
        header('Content-Type: application/json');

        $mes = $request->input('mes');
        $page = $request->input('page', 1);

        $query = DB::table('ventas_usuarios_bet')
            ->selectRaw('NULL AS consorcio_id, agencia_id, cedula, NULL AS tipo')
            ->whereNotIn('cedula', function ($sub) {
                $sub->select('cedula')->from('empleados')->whereNotNull('cedula');
            });

        if ($mes) {
            [$year, $month] = explode('-', $mes);
            $query->whereYear('fecha', $year)->whereMonth('fecha', $month);
        }

        $registros = $query
            ->groupBy('agencia_id', 'cedula')
            ->orderBy('cedula', 'desc')
            ->paginate(50, ['*'], 'page', $page);

        return $registros->toJson();
    }

    public function excelVentasUsuarioBet(Request $request)
    {
        ini_set('memory_limit', '2G'); // Aumentar el límite de memoria
        ini_set('max_execution_time', 300); // Aumentar el tiempo máximo de entrada a 5 min

        $tipo = $request->input('tipo');
        $fecha = $request->input('fecha');
        $mes = $request->input('mes');

        $fileName = 'ventas_usuarioio_bet_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new VentasUsuarioExport($tipo, $fecha, $mes), $fileName);
    }

    function pdfVentasUsuarioBet(Request $request)
    {
        ini_set('memory_limit', '1G'); // Aumentar el límite de memoria a 512MB

        $mes = $request->input('mes');

        $query = DB::table('ventas_usuarios_bet')
            ->selectRaw('NULL AS consorcio_id, agencia_id, cedula, NULL AS tipo')
            ->whereNotIn('cedula', function ($sub) {
                $sub->select('cedula')->from('empleados')->whereNotNull('cedula');
            });

        if ($mes) {
            [$year, $month] = explode('-', $mes);
            $query->whereYear('fecha', $year)->whereMonth('fecha', $month);
        }

        $registros = $query
            ->groupBy('agencia_id', 'cedula')
            ->orderBy('cedula', 'desc')
            ->get();        

        // 🔹 Generar PDF usando una vista
        $pdf = Pdf::loadView('reportes.ventas-usuario-bet-pdf', compact('registros'))
            ->setPaper('A4', 'portrait');

        // 🔹 Descargar el archivo
        return $pdf->download('reporte_ventas_usuario.pdf');
    }

    // ========== INFORME FALTANTES BET ==========
    function faltantesBet(Request $request)
    {
        return view('reportes.faltantes-bet');
    }

    private function getFaltantesConfig(?string $tipo = 'all'): array
    {
        $tipo = strtolower($tipo ?? 'all');

        if ($tipo === 'all') {
            return [
                'tipo' => 'all',
                'tabla' => 'faltantes',
                'nombre' => 'Todos los sistemas',
            ];
        }

        if ($tipo === 'net') {
            return [
                'tipo' => 'net',
                'tabla' => 'faltantes_net',
                'nombre' => 'Lotonet Lotedom',
            ];
        }

        return [
            'tipo' => 'bet',
            'tabla' => 'faltantes_bet',
            'nombre' => 'Lotobet Real',
        ];
    }

    private function faltantesBaseQuery(string $tipo)
    {
        if ($tipo !== 'all') {
            return DB::table($this->getFaltantesConfig($tipo)['tabla']);
        }

        $faltantesBet = DB::table('faltantes_bet')
            ->select('agencia_id', 'identificacion', 'faltante_id', 'monto', 'fecha');

        $faltantesNet = DB::table('faltantes_net')
            ->select('agencia_id', 'identificacion', 'faltante_id', 'monto', 'fecha');

        return DB::query()->fromSub($faltantesBet->unionAll($faltantesNet), 'faltantes');
    }

    public function listFaltantesBet(Request $request)
    {
        header('Content-Type: application/json');

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $config = $this->getFaltantesConfig($request->input('tipo'));
        $tabla = $config['tabla'];

        $query = $this->faltantesBaseQuery($config['tipo'])
            ->leftJoin('empleados', $tabla . '.identificacion', '=', 'empleados.cedula')
            ->select(
                $tabla . '.agencia_id',
                $tabla . '.identificacion',
                DB::raw("CONCAT(COALESCE(empleados.nombres, ''), ' ', COALESCE(empleados.apellidos, '')) as nombre_empleado"),
                DB::raw("COUNT($tabla.faltante_id) as cantidad_faltantes"),
                DB::raw("SUM($tabla.monto) as total_monto"),
                DB::raw("GROUP_CONCAT(DISTINCT DATE_FORMAT($tabla.fecha, '%d/%m/%Y') ORDER BY $tabla.fecha SEPARATOR ', ') as fechas_faltantes"),
                DB::raw("GROUP_CONCAT(CONCAT(DATE_FORMAT($tabla.fecha, '%d/%m/%Y'), '|', COALESCE($tabla.monto, 0)) ORDER BY $tabla.fecha SEPARATOR ';;') as detalles_faltantes")
            )
            ->whereNotNull($tabla . '.identificacion')
            ->where($tabla . '.identificacion', '!=', '');

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween($tabla . '.fecha', [$fechaInicio, $fechaFin]);
        }

        $registros = $query
            ->groupBy($tabla . '.agencia_id', $tabla . '.identificacion', 'empleados.nombres', 'empleados.apellidos')
            ->orderBy('total_monto', 'desc')
            ->paginate(10);

        return $registros->toJson();
    }

    public function excelFaltantesBet(Request $request)
    {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 300);

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $config = $this->getFaltantesConfig($request->input('tipo'));
        $tabla = $config['tabla'];

        $query = $this->faltantesBaseQuery($config['tipo'])
            ->leftJoin('empleados', $tabla . '.identificacion', '=', 'empleados.cedula')
            ->select(
                $tabla . '.identificacion',
                DB::raw("CONCAT(COALESCE(empleados.nombres, ''), ' ', COALESCE(empleados.apellidos, '')) as nombre_empleado"),
                DB::raw("COUNT($tabla.faltante_id) as cantidad_faltantes"),
                DB::raw("SUM($tabla.monto) as total_monto"),
                DB::raw("GROUP_CONCAT(DISTINCT DATE_FORMAT($tabla.fecha, '%d/%m/%Y') ORDER BY $tabla.fecha SEPARATOR ', ') as fechas_faltantes")
            )
            ->whereNotNull($tabla . '.identificacion')
            ->where($tabla . '.identificacion', '!=', '');

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween($tabla . '.fecha', [$fechaInicio, $fechaFin]);
        }

        $registros = $query
            ->groupBy($tabla . '.identificacion', 'empleados.nombres', 'empleados.apellidos')
            ->orderBy('total_monto', 'desc')
            ->get();

        $fileName = 'faltantes_' . $config['tipo'] . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new \App\Exports\FaltantesBetExport($registros), $fileName);
    }

    public function pdfFaltantesBet(Request $request)
    {
        ini_set('memory_limit', '1G');

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $config = $this->getFaltantesConfig($request->input('tipo'));
        $tabla = $config['tabla'];

        $query = $this->faltantesBaseQuery($config['tipo'])
            ->leftJoin('empleados', $tabla . '.identificacion', '=', 'empleados.cedula')
            ->select(
                $tabla . '.identificacion',
                DB::raw("CONCAT(COALESCE(empleados.nombres, ''), ' ', COALESCE(empleados.apellidos, '')) as nombre_empleado"),
                DB::raw("COUNT($tabla.faltante_id) as cantidad_faltantes"),
                DB::raw("SUM($tabla.monto) as total_monto"),
                DB::raw("GROUP_CONCAT(DISTINCT DATE_FORMAT($tabla.fecha, '%d/%m/%Y') ORDER BY $tabla.fecha SEPARATOR ', ') as fechas_faltantes")
            )
            ->whereNotNull($tabla . '.identificacion')
            ->where($tabla . '.identificacion', '!=', '');

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween($tabla . '.fecha', [$fechaInicio, $fechaFin]);
        }

        $registros = $query
            ->groupBy($tabla . '.identificacion', 'empleados.nombres', 'empleados.apellidos')
            ->orderBy('total_monto', 'desc')
            ->get();

        $sistema = $config['nombre'];

        $pdf = Pdf::loadView('reportes.faltantes-bet-pdf', compact('registros', 'sistema'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('reporte_faltantes_' . $config['tipo'] . '.pdf');
    }

    public function cuadreVentas(Request $request)
    {
        return view('reportes.cuadre-ventas');
    }

    public function listCuadreVentas(Request $request)
    {
        $sistema = $request->input('sistema', 'Lotobet');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        if (!$fechaInicio || !$fechaFin) {
            return response()->json([
                'resultados' => [],
                'agencias_sin_cedula' => [],
            ]);
        }

        // Determinar la tabla según el sistema
        $tabla = $sistema === 'Lotobet' ? 'ventas_producto_bet' : 'ventas_producto_net';
        $tipoProductoSql = $this->tipoProductoSql('v', 'cj');

        // Consulta principal por día
        $resultados = DB::select("
            SELECT
                r.Fecha,
                FORMAT(r.Tradicional, 2, 'en_US')     AS Tradicional,
                FORMAT(r.No_Tradicional, 2, 'en_US')  AS No_Tradicional,
                FORMAT(r.Recarga, 2, 'en_US')          AS Recarga,
                FORMAT(r.Paquetico, 2, 'en_US')        AS Paquetico,
                FORMAT(
                    r.Tradicional + r.No_Tradicional + r.Recarga + r.Paquetico,
                    2, 'en_US'
                ) AS Total_Dia
            FROM (
                SELECT
                    DATE(v.fecha) AS Fecha,
                    SUM(CASE WHEN {$tipoProductoSql} = 'tradicional'     THEN v.monto ELSE 0 END) AS Tradicional,
                    SUM(CASE WHEN {$tipoProductoSql} = 'no tradicional'  THEN v.monto ELSE 0 END) AS No_Tradicional,
                    SUM(CASE WHEN {$tipoProductoSql} = 'recarga'         THEN v.monto ELSE 0 END) AS Recarga,
                    SUM(CASE WHEN {$tipoProductoSql} = 'paquetico'       THEN v.monto ELSE 0 END) AS Paquetico
                FROM {$tabla} v
                LEFT JOIN catalogo_juegos cj
                    ON v.producto_id = cj.producto_id
                WHERE v.fecha >= ?
                  AND v.fecha < DATE_ADD(?, INTERVAL 1 DAY)
                GROUP BY DATE(v.fecha)
            ) r

            UNION ALL

            SELECT
                'TOTAL' AS Fecha,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'tradicional'     THEN v.monto ELSE 0 END), 2, 'en_US') AS Tradicional,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'no tradicional'  THEN v.monto ELSE 0 END), 2, 'en_US') AS No_Tradicional,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'recarga'         THEN v.monto ELSE 0 END), 2, 'en_US') AS Recarga,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'paquetico'       THEN v.monto ELSE 0 END), 2, 'en_US') AS Paquetico,
                FORMAT(SUM(v.monto), 2, 'en_US') AS Total_Dia
            FROM {$tabla} v
            LEFT JOIN catalogo_juegos cj
                ON v.producto_id = cj.producto_id
            WHERE v.fecha >= ?
              AND v.fecha < DATE_ADD(?, INTERVAL 1 DAY)
        ", [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin]);

        return response()->json($resultados);
    }

    public function cruceUsuarios(Request $request)
    {
        return view('reportes.cruce-usuarios');
    }

    public function ventasAgenciaPeriodo(Request $request)
    {
        return view('reportes.ventas-agencia-periodo');
    }

    public function listVentasAgenciaPeriodo(Request $request)
    {
        $sistema = $request->input('sistema', 'Lotobet');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $periodo = $request->input('periodo', 'dia');

        if (!$fechaInicio || !$fechaFin) {
            return response()->json([]);
        }

        $tabla = $sistema === 'Lotobet' ? 'ventas_producto_bet' : 'ventas_producto_net';
        $tipoProductoSql = $this->tipoProductoSql('v', 'c');

        $selectPeriodo = $periodo === 'mes'
            ? "DATE_FORMAT(v.fecha, '%Y-%m')"
            : "DATE_FORMAT(v.fecha, '%Y-%m-%d')";

        $resultados = DB::select("
            SELECT
                v.agencia_id AS agencia_id,
                {$selectPeriodo} AS periodo,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'tradicional'     THEN v.monto ELSE 0 END), 2, 'en_US') AS tradicional,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'no tradicional'  THEN v.monto ELSE 0 END), 2, 'en_US') AS no_tradicional,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'recarga'         THEN v.monto ELSE 0 END), 2, 'en_US') AS recargas,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'paquetico'       THEN v.monto ELSE 0 END), 2, 'en_US') AS paquetico,
                FORMAT(SUM(v.monto), 2, 'en_US') AS total
            FROM {$tabla} v
            LEFT JOIN catalogo_juegos c
                ON v.producto_id = c.producto_id
            WHERE v.fecha BETWEEN ? AND ?
            GROUP BY
                v.agencia_id,
                {$selectPeriodo}
            ORDER BY
                v.agencia_id,
                periodo
        ", [$fechaInicio, $fechaFin]);

        return response()->json($resultados);
    }

    public function ventasPorAgencia(Request $request)
    {
        return view('reportes.ventas-por-agencia');
    }

    public function buscarAgencia(Request $request)
    {
        $codigo = $request->input('codigo');

        if (!$codigo) {
            return response()->json(null);
        }

        $agencia = DB::table('agencias')
            ->select('agencia', 'nombre_agencia', 'terminal')
            ->where('terminal', $codigo)
            ->first();

        return response()->json($agencia);
    }

    public function listVentasPorAgencia(Request $request)
    {
        $sistema = $request->input('sistema', 'Lotobet');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $periodo = $request->input('periodo', 'dia');
        $terminal = $request->input('terminal');

        if (!$fechaInicio || !$fechaFin || !$terminal) {
            return response()->json([]);
        }

        $tabla = $sistema === 'Lotobet' ? 'ventas_producto_bet' : 'ventas_producto_net';
        $tipoProductoSql = $this->tipoProductoSql('v', 'c');

        $selectPeriodo = $periodo === 'mes'
            ? "DATE_FORMAT(v.fecha, '%Y-%m')"
            : "DATE_FORMAT(v.fecha, '%Y-%m-%d')";

        $resultados = DB::select("
            SELECT
                a.terminal AS terminal,
                a.coordinador AS coordinador,
                a.nombre_agencia AS nombre_agencia,
                a.ruta AS ruta,
                {$selectPeriodo} AS periodo,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'tradicional'     THEN v.monto ELSE 0 END), 2, 'en_US') AS tradicional,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'no tradicional'  THEN v.monto ELSE 0 END), 2, 'en_US') AS no_tradicional,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'recarga'         THEN v.monto ELSE 0 END), 2, 'en_US') AS recargas,
                FORMAT(SUM(CASE WHEN {$tipoProductoSql} = 'paquetico'       THEN v.monto ELSE 0 END), 2, 'en_US') AS paquetico,
                FORMAT(SUM(v.monto), 2, 'en_US') AS total
            FROM {$tabla} v
            JOIN agencias a
                ON TRIM(CAST(v.agencia_id AS CHAR)) COLLATE utf8mb4_unicode_ci = TRIM(a.terminal) COLLATE utf8mb4_unicode_ci
            LEFT JOIN catalogo_juegos c
                ON v.producto_id = c.producto_id
            WHERE v.fecha BETWEEN ? AND ?
              AND TRIM(a.terminal) COLLATE utf8mb4_unicode_ci = TRIM(?) COLLATE utf8mb4_unicode_ci
            GROUP BY
                a.terminal,
                a.coordinador,
                a.nombre_agencia,
                a.ruta,
                {$selectPeriodo}
            ORDER BY
                a.terminal,
                a.coordinador,
                periodo
        ", [$fechaInicio, $fechaFin, $terminal]);

        return response()->json($resultados);
    }

    public function ventasPorCedula(Request $request)
    {
        return view('reportes.ventas-por-cedula');
    }

    public function listVentasPorCedula(Request $request)
    {
        $sistema = $request->input('sistema', 'todos');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $cedula = preg_replace('/\D/', '', (string) $request->input('cedula', ''));

        if (!$fechaInicio || !$fechaFin || !$cedula || $fechaInicio > $fechaFin) {
            return response()->json([]);
        }

        $buildConsultaBase = function (string $tabla) use ($fechaInicio, $fechaFin, $cedula) {
            return DB::table($tabla)
                ->selectRaw('CAST(cedula AS CHAR(11)) AS Identificacion, DATE(fecha) AS Dia, agencia_id AS Agencia, monto')
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->whereRaw("REPLACE(REPLACE(cedula, '-', ''), ' ', '') = ?", [$cedula]);
        };

        if ($sistema === 'lotonet') {
            $ventasUnificadas = $buildConsultaBase('ventas_usuarios_net');
        } elseif ($sistema === 'lotobet') {
            $ventasUnificadas = $buildConsultaBase('ventas_usuarios_bet');
        } else {
            $ventasUnificadas = $buildConsultaBase('ventas_usuarios_net')
                ->unionAll($buildConsultaBase('ventas_usuarios_bet'));
        }

        $resultados = DB::query()
            ->fromSub($ventasUnificadas, 'ventas_unificadas')
            ->selectRaw('Identificacion, Dia, Agencia, CAST(SUM(monto) AS DECIMAL(15,2)) AS Total_Dia_Agencia')
            ->groupBy('Identificacion', 'Dia', 'Agencia')
            ->orderBy('Dia', 'asc')
            ->orderByDesc('Total_Dia_Agencia')
            ->get();

        return response()->json($resultados);
    }

    public function listCruceUsuarios(Request $request)
    {
        $sistema = $request->input('sistema', 'Lotobet');
        $estatus = $request->input('estatus');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        if (!$fechaInicio || !$fechaFin) {
            return response()->json([]);
        }

        // Determinar la tabla según el sistema
        $tabla = $sistema === 'Lotobet' ? 'ventas_usuarios_bet' : 'ventas_usuarios_net';

        // Deshabilitar temporalmente strict mode para esta consulta
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'STRICT_TRANS_TABLES',''))");
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'NO_ZERO_DATE',''))");

        $resultados = DB::select("
            SELECT
                CAST(
                    REPLACE(REPLACE(v.cedula,'-',''),' ','')
                    AS CHAR(11)
                ) AS Identificacion,

                MAX(e.empleadoid) AS Empleado_ID,

                CASE
                    WHEN MAX(e.empleadoid) IS NULL
                        THEN 'ACTUALIZAR EN MAESTRA DE EMPLEADOS'
                    ELSE CONCAT(MAX(e.nombres), ' ', MAX(e.apellidos))
                END AS NombreCompleto,

                CASE
                    WHEN MAX(e.empleadoid) IS NULL
                      OR (MAX(e.fechasalida) IS NOT NULL AND MAX(e.fechasalida) <> '0000-00-00')
                    THEN CONCAT(
                        'Agencia(s): ',
                        GROUP_CONCAT(
                            DISTINCT v.agencia_id
                            ORDER BY v.agencia_id
                            SEPARATOR ', '
                        )
                    )
                    ELSE ''
                END AS Detalle,

                CASE
                    WHEN MAX(e.empleadoid) IS NULL THEN 'No registrado'
                    WHEN MAX(e.fechasalida) IS NULL OR MAX(e.fechasalida) = '0000-00-00'
                        THEN 'Activo'
                    ELSE CONCAT('No Activo - ', MAX(e.fechasalida))
                END AS Estatus,

                DATE(MAX(v.fecha)) AS Ultima_Fecha_Venta

            FROM {$tabla} v
            LEFT JOIN empleados e
                ON REPLACE(REPLACE(v.cedula,'-',''),' ','')
                 = REPLACE(REPLACE(e.cedula,'-',''),' ','')

            WHERE v.fecha >= ?
              AND v.fecha < DATE_ADD(?, INTERVAL 1 DAY)
                            AND NULLIF(REPLACE(REPLACE(v.cedula,'-',''),' ',''), '') IS NOT NULL
                            AND REPLACE(REPLACE(v.cedula,'-',''),' ','') <> '00000000000'

            GROUP BY
                CAST(REPLACE(REPLACE(v.cedula,'-',''),' ','') AS CHAR(11))

            ORDER BY
                Ultima_Fecha_Venta DESC,
                Identificacion
        ", [$fechaInicio, $fechaFin]);

        $agenciasSinCedula = DB::select("
            SELECT
                v.agencia_id AS Agencia,
                COUNT(DISTINCT DATE(v.fecha)) AS Dias_Sin_Cedula_Con_Ventas
            FROM {$tabla} v
            WHERE v.fecha >= ?
              AND v.fecha < DATE_ADD(?, INTERVAL 1 DAY)
              AND (
                    NULLIF(REPLACE(REPLACE(COALESCE(v.cedula, ''),'-',''),' ',''), '') IS NULL
                    OR REPLACE(REPLACE(COALESCE(v.cedula, ''),'-',''),' ','') = '00000000000'
                  )
            GROUP BY v.agencia_id
            ORDER BY Dias_Sin_Cedula_Con_Ventas DESC, v.agencia_id
        ", [$fechaInicio, $fechaFin]);
        
        // Restaurar el strict mode
        DB::statement("SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        // Filtrar resultados
        $resultados = array_filter($resultados, function($item) use ($estatus) {
            // Si se seleccionó un estatus específico, filtrar por ese
            if ($estatus) {
                if ($estatus === 'No activo') {
                    return strpos($item->Estatus, 'No Activo') === 0;
                }
                return $item->Estatus === $estatus;
            }
            // Si no se seleccionó estatus (Todos), excluir los Activos
            return $item->Estatus !== 'Activo';
        });
        
        $resultados = array_values($resultados); // Reindexar el array

        return response()->json([
            'resultados' => $resultados,
            'agencias_sin_cedula' => $agenciasSinCedula,
        ]);
    }

    public function listCruceUsuariosSinCedulaFechas(Request $request)
    {
        $sistema = $request->input('sistema', 'Lotobet');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $agenciaId = $request->input('agencia_id');

        if (!$fechaInicio || !$fechaFin || !$agenciaId) {
            return response()->json([
                'agencia' => $agenciaId,
                'fechas' => [],
            ]);
        }

        $tabla = $sistema === 'Lotobet' ? 'ventas_usuarios_bet' : 'ventas_usuarios_net';

        $fechas = DB::select(" 
            SELECT
                DATE(v.fecha) AS Fecha,
                COUNT(*) AS Cantidad_Ventas
            FROM {$tabla} v
            WHERE v.fecha >= ?
              AND v.fecha < DATE_ADD(?, INTERVAL 1 DAY)
              AND v.agencia_id = ?
              AND (
                    NULLIF(REPLACE(REPLACE(COALESCE(v.cedula, ''),'-',''),' ',''), '') IS NULL
                    OR REPLACE(REPLACE(COALESCE(v.cedula, ''),'-',''),' ','') = '00000000000'
                  )
            GROUP BY DATE(v.fecha)
            ORDER BY Fecha DESC
        ", [$fechaInicio, $fechaFin, $agenciaId]);

        return response()->json([
            'agencia' => $agenciaId,
            'fechas' => $fechas,
        ]);
    }

    // ========== VERIFICADOR DE USUARIOS ==========
    public function verificadorUsuarios(Request $request)
    {
        return view('reportes.verificador-usuarios');
    }

    public function listVerificadorUsuarios(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $sistema = $request->input('sistema', 'todos'); // todos, lotobet, lotonet

        if (!$fechaInicio || !$fechaFin) {
            return response()->json([]);
        }

        // Deshabilitar temporalmente strict mode para esta consulta
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'STRICT_TRANS_TABLES',''))");
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'NO_ZERO_DATE',''))");

        $query = "
            SELECT
                e.empleadoid,
                e.nombres,
                e.apellidos,
                c.cedula,

                -- Horas separadas
                ROUND(COALESCE(an.horas_net, 0), 2) AS horas_net,
                ROUND(COALESCE(ab.horas_bet, 0), 2) AS horas_bet,
                ROUND(COALESCE(an.horas_net, 0) + COALESCE(ab.horas_bet, 0), 2) AS horas_total,

                -- Cantidad de faltantes
                COALESCE(fn.cant_faltantes_net, 0) AS cant_faltantes_net,
                COALESCE(fb.cant_faltantes_bet, 0) AS cant_faltantes_bet,
                COALESCE(fn.cant_faltantes_net, 0) + COALESCE(fb.cant_faltantes_bet, 0) AS cant_faltantes_total,

                -- Monto de faltantes
                ROUND(COALESCE(fn.monto_faltantes_net, 0), 2) AS monto_faltantes_net,
                ROUND(COALESCE(fb.monto_faltantes_bet, 0), 2) AS monto_faltantes_bet,
                ROUND(
                    COALESCE(fn.monto_faltantes_net, 0) +
                    COALESCE(fb.monto_faltantes_bet, 0),
                    2
                ) AS monto_faltantes_total,

                -- Comentario si la cédula no existe en empleados
                CASE
                    WHEN e.empleadoid IS NULL
                         AND (
                             COALESCE(an.horas_net, 0) > 0
                             OR COALESCE(ab.horas_bet, 0) > 0
                             OR COALESCE(fn.cant_faltantes_net, 0) > 0
                             OR COALESCE(fb.cant_faltantes_bet, 0) > 0
                         )
                    THEN 'cedula sin nombre'
                    ELSE ''
                END AS comentario

            FROM (
                -- Cédulas con actividad (normalizadas)
                SELECT DISTINCT REPLACE(identificacion, '-', '') AS cedula
                FROM asistencias_net
                WHERE entrada >= ? AND entrada < DATE_ADD(?, INTERVAL 1 DAY)

                UNION
                SELECT DISTINCT REPLACE(cedula, '-', '')
                FROM asistencias_bet
                WHERE fecha BETWEEN ? AND ?

                UNION
                SELECT DISTINCT REPLACE(identificacion, '-', '')
                FROM faltantes_net
                WHERE fecha BETWEEN ? AND ?

                UNION
                SELECT DISTINCT REPLACE(identificacion, '-', '')
                FROM faltantes_bet
                WHERE fecha BETWEEN ? AND ?
            ) c

            LEFT JOIN empleados e
                ON REPLACE(e.cedula, '-', '') = c.cedula

            LEFT JOIN (
                -- Horas NET
                SELECT
                    REPLACE(identificacion, '-', '') AS cedula,
                    SUM(GREATEST(TIMESTAMPDIFF(SECOND, entrada, salida), 0)) / 3600 AS horas_net
                FROM asistencias_net
                WHERE entrada >= ? AND entrada < DATE_ADD(?, INTERVAL 1 DAY)
                  AND salida IS NOT NULL
                GROUP BY REPLACE(identificacion, '-', '')
            ) an ON an.cedula = c.cedula

            LEFT JOIN (
                -- Horas BET
                SELECT
                    REPLACE(cedula, '-', '') AS cedula,
                    SUM(GREATEST(TIMESTAMPDIFF(SECOND, primer_login, ultimo_login), 0)) / 3600 AS horas_bet
                FROM asistencias_bet
                WHERE fecha BETWEEN ? AND ?
                  AND primer_login IS NOT NULL
                  AND ultimo_login IS NOT NULL
                GROUP BY REPLACE(cedula, '-', '')
            ) ab ON ab.cedula = c.cedula

            LEFT JOIN (
                -- Faltantes NET
                SELECT
                    REPLACE(identificacion, '-', '') AS cedula,
                    COUNT(*) AS cant_faltantes_net,
                    SUM(COALESCE(monto, 0)) AS monto_faltantes_net
                FROM faltantes_net
                WHERE fecha BETWEEN ? AND ?
                GROUP BY REPLACE(identificacion, '-', '')
            ) fn ON fn.cedula = c.cedula

            LEFT JOIN (
                -- Faltantes BET
                SELECT
                    REPLACE(identificacion, '-', '') AS cedula,
                    COUNT(*) AS cant_faltantes_bet,
                    SUM(COALESCE(monto, 0)) AS monto_faltantes_bet
                FROM faltantes_bet
                WHERE fecha BETWEEN ? AND ?
                GROUP BY REPLACE(identificacion, '-', '')
            ) fb ON fb.cedula = c.cedula

            ORDER BY
                (e.empleadoid IS NULL) DESC,
                e.nombres,
                e.apellidos,
                c.cedula
        ";

        $resultados = DB::select($query, [
            $fechaInicio, $fechaFin,  // asistencias_net
            $fechaInicio, $fechaFin,  // asistencias_bet
            $fechaInicio, $fechaFin,  // faltantes_net
            $fechaInicio, $fechaFin,  // faltantes_bet
            $fechaInicio, $fechaFin,  // an (horas net)
            $fechaInicio, $fechaFin,  // ab (horas bet)
            $fechaInicio, $fechaFin,  // fn (faltantes net)
            $fechaInicio, $fechaFin   // fb (faltantes bet)
        ]);

        // Restaurar el strict mode
        DB::statement("SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        // Filtrar por sistema si se especifica
        if ($sistema !== 'todos') {
            $resultados = array_filter($resultados, function($item) use ($sistema) {
                if ($sistema === 'lotobet') {
                    return $item->horas_bet > 0 || $item->cant_faltantes_bet > 0;
                } elseif ($sistema === 'lotonet') {
                    return $item->horas_net > 0 || $item->cant_faltantes_net > 0;
                }
                return true;
            });
            $resultados = array_values($resultados);
        }

        return response()->json($resultados);
    }

    public function excelVerificadorUsuarios(Request $request)
    {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 300);

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $sistema = $request->input('sistema', 'todos');

        if (!$fechaInicio || !$fechaFin) {
            return response()->json(['error' => 'Fechas requeridas'], 400);
        }

        // Deshabilitar temporalmente strict mode
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'STRICT_TRANS_TABLES',''))");
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'NO_ZERO_DATE',''))");

        $query = "
            SELECT
                e.empleadoid,
                e.nombres,
                e.apellidos,
                c.cedula,
                ROUND(COALESCE(an.horas_net, 0), 2) AS horas_net,
                ROUND(COALESCE(ab.horas_bet, 0), 2) AS horas_bet,
                ROUND(COALESCE(an.horas_net, 0) + COALESCE(ab.horas_bet, 0), 2) AS horas_total,
                COALESCE(fn.cant_faltantes_net, 0) AS cant_faltantes_net,
                COALESCE(fb.cant_faltantes_bet, 0) AS cant_faltantes_bet,
                COALESCE(fn.cant_faltantes_net, 0) + COALESCE(fb.cant_faltantes_bet, 0) AS cant_faltantes_total,
                ROUND(COALESCE(fn.monto_faltantes_net, 0), 2) AS monto_faltantes_net,
                ROUND(COALESCE(fb.monto_faltantes_bet, 0), 2) AS monto_faltantes_bet,
                ROUND(
                    COALESCE(fn.monto_faltantes_net, 0) +
                    COALESCE(fb.monto_faltantes_bet, 0),
                    2
                ) AS monto_faltantes_total,
                CASE
                    WHEN e.empleadoid IS NULL
                         AND (
                             COALESCE(an.horas_net, 0) > 0
                             OR COALESCE(ab.horas_bet, 0) > 0
                             OR COALESCE(fn.cant_faltantes_net, 0) > 0
                             OR COALESCE(fb.cant_faltantes_bet, 0) > 0
                         )
                    THEN 'cedula sin nombre'
                    ELSE ''
                END AS comentario
            FROM (
                SELECT DISTINCT REPLACE(identificacion, '-', '') AS cedula
                FROM asistencias_net
                WHERE entrada >= ? AND entrada < DATE_ADD(?, INTERVAL 1 DAY)
                UNION
                SELECT DISTINCT REPLACE(cedula, '-', '')
                FROM asistencias_bet
                WHERE fecha BETWEEN ? AND ?
                UNION
                SELECT DISTINCT REPLACE(identificacion, '-', '')
                FROM faltantes_net
                WHERE fecha BETWEEN ? AND ?
                UNION
                SELECT DISTINCT REPLACE(identificacion, '-', '')
                FROM faltantes_bet
                WHERE fecha BETWEEN ? AND ?
            ) c
            LEFT JOIN empleados e
                ON REPLACE(e.cedula, '-', '') = c.cedula
            LEFT JOIN (
                SELECT
                    REPLACE(identificacion, '-', '') AS cedula,
                    SUM(GREATEST(TIMESTAMPDIFF(SECOND, entrada, salida), 0)) / 3600 AS horas_net
                FROM asistencias_net
                WHERE entrada >= ? AND entrada < DATE_ADD(?, INTERVAL 1 DAY)
                  AND salida IS NOT NULL
                GROUP BY REPLACE(identificacion, '-', '')
            ) an ON an.cedula = c.cedula
            LEFT JOIN (
                SELECT
                    REPLACE(cedula, '-', '') AS cedula,
                    SUM(GREATEST(TIMESTAMPDIFF(SECOND, primer_login, ultimo_login), 0)) / 3600 AS horas_bet
                FROM asistencias_bet
                WHERE fecha BETWEEN ? AND ?
                  AND primer_login IS NOT NULL
                  AND ultimo_login IS NOT NULL
                GROUP BY REPLACE(cedula, '-', '')
            ) ab ON ab.cedula = c.cedula
            LEFT JOIN (
                SELECT
                    REPLACE(identificacion, '-', '') AS cedula,
                    COUNT(*) AS cant_faltantes_net,
                    SUM(COALESCE(monto, 0)) AS monto_faltantes_net
                FROM faltantes_net
                WHERE fecha BETWEEN ? AND ?
                GROUP BY REPLACE(identificacion, '-', '')
            ) fn ON fn.cedula = c.cedula
            LEFT JOIN (
                SELECT
                    REPLACE(identificacion, '-', '') AS cedula,
                    COUNT(*) AS cant_faltantes_bet,
                    SUM(COALESCE(monto, 0)) AS monto_faltantes_bet
                FROM faltantes_bet
                WHERE fecha BETWEEN ? AND ?
                GROUP BY REPLACE(identificacion, '-', '')
            ) fb ON fb.cedula = c.cedula
            ORDER BY
                (e.empleadoid IS NULL) DESC,
                e.nombres,
                e.apellidos,
                c.cedula
        ";

        $resultados = DB::select($query, [
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin
        ]);

        DB::statement("SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        // Filtrar por sistema
        if ($sistema !== 'todos') {
            $resultados = array_filter($resultados, function($item) use ($sistema) {
                if ($sistema === 'lotobet') {
                    return $item->horas_bet > 0 || $item->cant_faltantes_bet > 0;
                } elseif ($sistema === 'lotonet') {
                    return $item->horas_net > 0 || $item->cant_faltantes_net > 0;
                }
                return true;
            });
            $resultados = array_values($resultados);
        }

        $fileName = 'verificador_usuarios_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new \App\Exports\VerificadorUsuariosExport($resultados), $fileName);
    }
}
