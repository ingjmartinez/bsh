<?php

namespace App\Http\Controllers;

use App\Exports\NovedadesHorarioExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NovedadHorarioController extends Controller
{
    public function index()
    {
        return view('recursos_humanos.novedades_de_horario.index');
    }

    public function list(Request $request)
    {
        $validated = $request->validate([
            'sistema' => ['required', 'in:todos,lotobet,lotedom'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'horas_requeridas' => ['nullable', 'integer', 'min:1'],
            'detalle' => ['nullable', 'in:todos,cumple,tiene_falta'],
        ]);

        $uniones = [];
        $bindings = [];
        $agenciasBetSql = "
            SELECT
                TRIM(CAST(terminal AS CHAR)) AS terminal,
                MAX(nombre) AS nombre_agencia,
                '' AS ruta
            FROM agencias
            WHERE terminal IS NOT NULL
              AND TRIM(CAST(terminal AS CHAR)) <> ''
            GROUP BY TRIM(CAST(terminal AS CHAR))
        ";
        $agenciasLotedomSql = "
            SELECT
                TRIM(CAST(terminal AS CHAR)) AS terminal,
                MAX(COALESCE(NULLIF(nombre_agencia, ''), nombre)) AS nombre_agencia,
                MAX(COALESCE(ruta, '')) AS ruta
            FROM agencias_lotedom
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
                LEFT JOIN ({$agenciasBetSql}) a
                    ON TRIM(CAST(a.terminal AS CHAR)) = TRIM(CAST(ab.agencia_id AS CHAR))
                LEFT JOIN ({$empleadosSql}) e
                    ON TRIM(e.cedula) = TRIM(ab.cedula)
                WHERE ab.fecha >= ? AND ab.fecha < DATE_ADD(?, INTERVAL 1 DAY)
                  AND ab.primer_login IS NOT NULL
                  AND ab.ultimo_login IS NOT NULL
                  AND CHAR_LENGTH(REPLACE(REPLACE(TRIM(COALESCE(ab.cedula, '')), '-', ''), ' ', '')) >= 9
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

        if (in_array($validated['sistema'], ['todos', 'lotedom'], true)) {
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
                LEFT JOIN ({$agenciasLotedomSql}) a
                    ON TRIM(CAST(a.terminal AS CHAR)) = TRIM(CAST(COALESCE(NULLIF(an.terminal, ''), an.agencia) AS CHAR))
                LEFT JOIN ({$empleadosSql}) e
                    ON TRIM(e.cedula) = TRIM(an.identificacion)
                WHERE an.entrada >= ? AND an.entrada < DATE_ADD(?, INTERVAL 1 DAY)
                  AND an.entrada IS NOT NULL
                  AND COALESCE(an.salida, an.salida_inactividad) IS NOT NULL
                  AND CHAR_LENGTH(REPLACE(REPLACE(TRIM(COALESCE(an.identificacion, '')), '-', ''), ' ', '')) >= 9
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

        $baseSql = '
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
                '.implode(' UNION ALL ', $uniones).'
            ) novedades
        ';
        $search = trim((string) $request->input('search.value', ''));
        $whereSql = '';
        $whereBindings = [];

        if ($search !== '') {
            $whereSql = '
                WHERE terminal LIKE ?
                   OR nombre_agencia LIKE ?
                   OR ruta LIKE ?
                   OR nombre_empleado LIKE ?
                   OR cedula LIKE ?
            ';
            $searchValue = '%'.$search.'%';
            $whereBindings = array_fill(0, 5, $searchValue);
        }

        $horasRequeridas = (int) ($validated['horas_requeridas'] ?? 8);
        $detalle = $validated['detalle'] ?? 'todos';

        if ($detalle === 'cumple') {
            $whereSql .= ($whereSql === '' ? ' WHERE ' : ' AND ').'horas_acumuladas >= ?';
            $whereBindings[] = $horasRequeridas;
        } elseif ($detalle === 'tiene_falta') {
            $whereSql .= ($whereSql === '' ? ' WHERE ' : ' AND ').'horas_acumuladas < ?';
            $whereBindings[] = $horasRequeridas;
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
        $length = $length > 0 ? min($length, $request->boolean('export') ? 100000 : 200) : 25;
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

    public function export(Request $request): BinaryFileResponse
    {
        $validated = $this->validateCalculationRequest($request);
        $rows = $this->collectRows($request);

        return Excel::download(
            new NovedadesHorarioExport($rows, (float) $validated['horas_requeridas'], (float) $validated['valor_hora']),
            'novedades_horario_'.$validated['fecha_inicio'].'_'.$validated['fecha_fin'].'.xlsx'
        );
    }

    public function exportPago(Request $request): BinaryFileResponse
    {
        $validated = $this->validateCalculationRequest($request);
        $horasRequeridas = (float) $validated['horas_requeridas'];
        $valorHora = (float) $validated['valor_hora'];
        $rows = $this->collectRows($request)
            ->map(function ($row) use ($horasRequeridas, $valorHora): object {
                $horasFaltantes = round(max($horasRequeridas - (float) $row->horas_acumuladas, 0), 2);
                $row->horas_acumuladas = $horasRequeridas - $horasFaltantes;
                $row->monto_falta = round($horasFaltantes * $valorHora, 2);

                return $row;
            })
            ->filter(fn (object $row): bool => $row->monto_falta > 0)
            ->values();

        return Excel::download(
            new NovedadesHorarioExport($rows, $horasRequeridas, $valorHora),
            'novedad_pago_'.$validated['fecha_inicio'].'_'.$validated['fecha_fin'].'.xlsx'
        );
    }

    public function detalle(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $this->validateCalculationRequest($request, true);
        $horasRequeridas = (float) $validated['horas_requeridas'];
        $valorHora = (float) $validated['valor_hora'];
        $queries = [];
        $bindings = [];

        if (in_array($validated['sistema'], ['todos', 'lotobet'], true)) {
            $queries[] = "
                SELECT
                    DATE(ab.fecha) AS fecha,
                    MIN(ab.primer_login) AS primer_login,
                    MAX(ab.ultimo_login) AS ultimo_login,
                    MAX(COALESCE(ab.usuario, '')) AS nombre_origen,
                    ROUND(GREATEST(TIMESTAMPDIFF(SECOND, MIN(ab.primer_login), MAX(ab.ultimo_login)), 0) / 3600, 2) AS horas_acumuladas
                FROM asistencias_bet ab
                WHERE ab.fecha >= ? AND ab.fecha < DATE_ADD(?, INTERVAL 1 DAY)
                  AND TRIM(COALESCE(ab.cedula, '')) = ?
                  AND TRIM(CAST(ab.agencia_id AS CHAR)) = ?
                  AND ab.primer_login IS NOT NULL
                  AND ab.ultimo_login IS NOT NULL
                GROUP BY DATE(ab.fecha)
            ";
            array_push($bindings, $validated['fecha_inicio'], $validated['fecha_fin'], trim($validated['cedula']), trim($validated['terminal']));
        }

        if (in_array($validated['sistema'], ['todos', 'lotedom'], true)) {
            $queries[] = "
                SELECT
                    DATE(an.entrada) AS fecha,
                    MIN(an.entrada) AS primer_login,
                    MAX(COALESCE(an.salida, an.salida_inactividad)) AS ultimo_login,
                    MAX(COALESCE(NULLIF(an.usuario, ''), an.username, '')) AS nombre_origen,
                    ROUND(GREATEST(TIMESTAMPDIFF(SECOND, MIN(an.entrada), MAX(COALESCE(an.salida, an.salida_inactividad))), 0) / 3600, 2) AS horas_acumuladas
                FROM asistencias_net an
                WHERE an.entrada >= ? AND an.entrada < DATE_ADD(?, INTERVAL 1 DAY)
                  AND TRIM(COALESCE(an.identificacion, '')) = ?
                  AND TRIM(CAST(COALESCE(NULLIF(an.terminal, ''), an.agencia) AS CHAR)) = ?
                  AND COALESCE(an.salida, an.salida_inactividad) IS NOT NULL
                GROUP BY DATE(an.entrada)
            ";
            array_push($bindings, $validated['fecha_inicio'], $validated['fecha_fin'], trim($validated['cedula']), trim($validated['terminal']));
        }

        $rows = collect(DB::select(
            'SELECT * FROM ('.implode(' UNION ALL ', $queries).') detalle ORDER BY fecha',
            $bindings
        ));
        $detalle = $rows->map(function ($row) use ($horasRequeridas, $valorHora): array {
            $horasFaltantes = round(max($horasRequeridas - (float) $row->horas_acumuladas, 0), 2);

            return [
                'fecha' => $row->fecha,
                'horas_acumuladas' => (float) $row->horas_acumuladas,
                'horas_faltantes' => $horasFaltantes,
                'monto_dia' => round($horasFaltantes * $valorHora, 2),
            ];
        })->filter(fn (array $row): bool => $row['horas_faltantes'] > 0)->values();
        $first = $rows->first();
        $nombreEmpleado = DB::table('empleados')
            ->whereRaw('TRIM(cedula) = ?', [trim($validated['cedula'])])
            ->selectRaw("TRIM(CONCAT(COALESCE(nombres, ''), ' ', COALESCE(apellidos, ''))) AS nombre")
            ->value('nombre');

        return response()->json([
            'nombre' => $nombreEmpleado ?: ($first->nombre_origen ?? 'Sin especificar'),
            'cedula' => $validated['cedula'],
            'agencia' => $validated['terminal'],
            'terminal' => $validated['terminal'],
            'total_faltantes' => round($detalle->sum('horas_faltantes'), 2),
            'monto_total' => round($detalle->sum('monto_dia'), 2),
            'detalle' => $detalle,
        ]);
    }

    /** @return array<string, mixed> */
    private function validateCalculationRequest(Request $request, bool $detail = false): array
    {
        return $request->validate([
            'sistema' => ['required', 'in:todos,lotobet,lotedom'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'horas_requeridas' => ['required', 'integer', 'min:1'],
            'valor_hora' => ['required', 'numeric', 'min:0.01'],
            'detalle' => ['nullable', 'in:todos,cumple,tiene_falta'],
            'cedula' => [$detail ? 'required' : 'nullable', 'string'],
            'terminal' => [$detail ? 'required' : 'nullable', 'string'],
        ]);
    }

    private function collectRows(Request $request): Collection
    {
        $query = array_merge($request->query(), ['draw' => 1, 'start' => 0, 'length' => 100000, 'export' => 1]);
        $reportRequest = Request::create('/recursos-humanos/novedades-horario/list', 'GET', $query);
        $payload = $this->list($reportRequest)->getData(true);

        return collect($payload['data'])->map(fn (array $row): object => (object) $row);
    }
}
