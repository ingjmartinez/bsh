<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NovedadHorarioController extends Controller
{
    public function index()
    {
        return view('recursos_humanos.novedades_de_horario.index');
    }

    public function list(Request $request)
    {
        $validated = $request->validate([
            'sistema' => ['required', 'in:todos,lotobet,lotonet'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $uniones = [];
        $bindings = [];
        $agenciasSql = "
            SELECT
                TRIM(CAST(terminal AS CHAR)) AS terminal,
                MAX(nombre_agencia) AS nombre_agencia,
                MAX(ruta) AS ruta
            FROM agencias
            WHERE terminal IS NOT NULL
              AND TRIM(CAST(terminal AS CHAR)) <> ''
            GROUP BY TRIM(CAST(terminal AS CHAR))
        ";
        $empleadosSql = "
            SELECT
                TRIM(cedula) AS cedula,
                MAX(TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, '')))) AS nombre_empleado
            FROM empleados
            WHERE cedula IS NOT NULL
              AND TRIM(cedula) <> ''
            GROUP BY TRIM(cedula)
        ";

        if (in_array($validated['sistema'], ['todos', 'lotobet'], true)) {
            $uniones[] = "
                SELECT
                    TRIM(CAST(ab.agencia_id AS CHAR)) AS terminal,
                    COALESCE(a.nombre_agencia, ab.agencia_id) AS nombre_agencia,
                    COALESCE(a.ruta, '') AS ruta,
                    TRIM(COALESCE(NULLIF(e.nombre_empleado, ''), ab.usuario, '')) AS nombre_empleado,
                    ab.cedula AS cedula,
                    DATE(ab.fecha) AS fecha,
                    MIN(ab.primer_login) AS primer_login,
                    MAX(ab.ultimo_login) AS ultimo_login,
                    ROUND(GREATEST(TIMESTAMPDIFF(SECOND, MIN(ab.primer_login), MAX(ab.ultimo_login)), 0) / 3600, 2) AS horas_acumuladas
                FROM asistencias_bet ab
                LEFT JOIN ({$agenciasSql}) a
                    ON TRIM(CAST(a.terminal AS CHAR)) = TRIM(CAST(ab.agencia_id AS CHAR))
                LEFT JOIN ({$empleadosSql}) e
                    ON TRIM(e.cedula) = TRIM(ab.cedula)
                WHERE ab.fecha >= ? AND ab.fecha < DATE_ADD(?, INTERVAL 1 DAY)
                  AND ab.primer_login IS NOT NULL
                  AND ab.ultimo_login IS NOT NULL
                GROUP BY
                    TRIM(CAST(ab.agencia_id AS CHAR)),
                    COALESCE(a.nombre_agencia, ab.agencia_id),
                    COALESCE(a.ruta, ''),
                    TRIM(COALESCE(NULLIF(e.nombre_empleado, ''), ab.usuario, '')),
                    ab.cedula,
                    DATE(ab.fecha)
            ";
            $bindings[] = $validated['fecha_inicio'];
            $bindings[] = $validated['fecha_fin'];
        }

        if (in_array($validated['sistema'], ['todos', 'lotonet'], true)) {
            $uniones[] = "
                SELECT
                    TRIM(CAST(COALESCE(NULLIF(an.terminal, ''), an.agencia) AS CHAR)) AS terminal,
                    COALESCE(a.nombre_agencia, an.banca, an.agencia) AS nombre_agencia,
                    COALESCE(a.ruta, '') AS ruta,
                    TRIM(COALESCE(NULLIF(e.nombre_empleado, ''), an.usuario, an.username, '')) AS nombre_empleado,
                    an.identificacion AS cedula,
                    DATE(an.entrada) AS fecha,
                    MIN(an.entrada) AS primer_login,
                    MAX(COALESCE(an.salida, an.salida_inactividad)) AS ultimo_login,
                    ROUND(GREATEST(TIMESTAMPDIFF(SECOND, MIN(an.entrada), MAX(COALESCE(an.salida, an.salida_inactividad))), 0) / 3600, 2) AS horas_acumuladas
                FROM asistencias_net an
                LEFT JOIN ({$agenciasSql}) a
                    ON TRIM(CAST(a.terminal AS CHAR)) = TRIM(CAST(COALESCE(NULLIF(an.terminal, ''), an.agencia) AS CHAR))
                LEFT JOIN ({$empleadosSql}) e
                    ON TRIM(e.cedula) = TRIM(an.identificacion)
                WHERE an.entrada >= ? AND an.entrada < DATE_ADD(?, INTERVAL 1 DAY)
                  AND an.entrada IS NOT NULL
                  AND COALESCE(an.salida, an.salida_inactividad) IS NOT NULL
                GROUP BY
                    TRIM(CAST(COALESCE(NULLIF(an.terminal, ''), an.agencia) AS CHAR)),
                    COALESCE(a.nombre_agencia, an.banca, an.agencia),
                    COALESCE(a.ruta, ''),
                    TRIM(COALESCE(NULLIF(e.nombre_empleado, ''), an.usuario, an.username, '')),
                    an.identificacion,
                    DATE(an.entrada)
            ";
            $bindings[] = $validated['fecha_inicio'];
            $bindings[] = $validated['fecha_fin'];
        }

        $baseSql = "
            SELECT
                terminal,
                nombre_agencia,
                ruta,
                nombre_empleado,
                cedula,
                fecha,
                primer_login,
                ultimo_login,
                horas_acumuladas
            FROM (
                " . implode(' UNION ALL ', $uniones) . "
            ) novedades
        ";
        $search = trim((string) $request->input('search.value', ''));
        $whereSql = '';
        $whereBindings = [];

        if ($search !== '') {
            $whereSql = "
                WHERE terminal LIKE ?
                   OR nombre_agencia LIKE ?
                   OR ruta LIKE ?
                   OR nombre_empleado LIKE ?
                   OR cedula LIKE ?
            ";
            $searchValue = '%' . $search . '%';
            $whereBindings = array_fill(0, 5, $searchValue);
        }

        $recordsTotal = (int) DB::selectOne("SELECT COUNT(*) AS total FROM ({$baseSql}) base", $bindings)->total;
        $recordsFiltered = (int) DB::selectOne(
            "SELECT COUNT(*) AS total FROM ({$baseSql}) base {$whereSql}",
            array_merge($bindings, $whereBindings)
        )->total;
        $resumen = DB::selectOne(
            "
                SELECT
                    COUNT(*) AS total,
                    COUNT(DISTINCT terminal) AS terminales,
                    COUNT(DISTINCT nombre_agencia) AS agencias,
                    COALESCE(SUM(horas_acumuladas), 0) AS horas_acumuladas
                FROM ({$baseSql}) base
                {$whereSql}
            ",
            array_merge($bindings, $whereBindings)
        );

        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 25);
        $length = $length > 0 ? min($length, 200) : 25;
        $dataSql = "
            SELECT *
            FROM ({$baseSql}) base
            {$whereSql}
            ORDER BY fecha DESC, terminal, nombre_empleado
            LIMIT {$length} OFFSET {$start}
        ";

        $novedades = collect(DB::select($dataSql, array_merge($bindings, $whereBindings)))
            ->map(function ($row) {
                $row->fecha = $row->fecha ? Carbon::parse($row->fecha)->format('Y-m-d') : null;
                $row->primer_login = $row->primer_login ? Carbon::parse($row->primer_login)->format('Y-m-d H:i:s') : null;
                $row->ultimo_login = $row->ultimo_login ? Carbon::parse($row->ultimo_login)->format('Y-m-d H:i:s') : null;
                $row->horas_acumuladas = round((float) $row->horas_acumuladas, 2);

                return $row;
            });

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $novedades->values(),
            'resumen' => [
                'total' => (int) ($resumen->total ?? 0),
                'terminales' => (int) ($resumen->terminales ?? 0),
                'agencias' => (int) ($resumen->agencias ?? 0),
                'horas_acumuladas' => round((float) ($resumen->horas_acumuladas ?? 0), 2),
            ],
        ]);
    }
}
