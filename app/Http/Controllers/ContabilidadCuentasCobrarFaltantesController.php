<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContabilidadCuentasCobrarFaltantesController extends Controller
{
    private const CUENTA_FALTANTES = '11020102';

    public function index()
    {
        return view('contabilidad.cuentas-cobrar-faltantes', [
            'cuentaFaltantes' => self::CUENTA_FALTANTES,
        ]);
    }

    public function data(Request $request)
    {
        $validated = $request->validate([
            'fecha_inicio' => ['nullable', 'date_format:Y-m-d'],
            'fecha_fin' => ['nullable', 'date_format:Y-m-d'],
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'in:todos,pendientes,saldados,sobregiro'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:2000'],
        ]);

        [$fechaInicio, $fechaFin] = $this->normalizarFechas(
            $validated['fecha_inicio'] ?? null,
            $validated['fecha_fin'] ?? null
        );

        $buscar = trim((string) ($validated['buscar'] ?? ''));
        $estado = $validated['estado'] ?? 'pendientes';
        $limit = (int) ($validated['limit'] ?? 500);

        $faltantes = $this->faltantesPorCentroCostoQuery($fechaInicio, $fechaFin);
        $abonos = $this->abonosPorCentroCostoQuery($fechaInicio, $fechaFin);

        $rows = DB::query()
            ->fromSub($faltantes, 'faltantes')
            ->leftJoinSub($abonos, 'abonos', function ($join) {
                $join->on('faltantes.id_cc_empleado', '=', 'abonos.id_cc_empleado');
            })
            ->selectRaw("
                faltantes.id_cc_empleado,
                faltantes.companyid,
                faltantes.empleadoid,
                faltantes.nombre_empleado,
                faltantes.cantidad_faltantes,
                faltantes.total_faltantes,
                faltantes.agencias_faltantes,
                faltantes.cantidad_agencias,
                faltantes.primera_fecha_faltante,
                faltantes.ultima_fecha_faltante,
                COALESCE(abonos.cantidad_abonos, 0) AS cantidad_abonos,
                COALESCE(abonos.total_credito, 0) AS total_credito,
                COALESCE(abonos.total_debito, 0) AS total_debito,
                COALESCE(abonos.total_abonos, 0) AS total_abonos,
                COALESCE(abonos.agencias_abonos, '') AS agencias_abonos,
                abonos.ultima_fecha_abono,
                (faltantes.total_faltantes - COALESCE(abonos.total_credito, 0)) AS balance_pendiente
            ");

        if ($buscar !== '') {
            $like = '%' . $buscar . '%';
            $rows->where(function ($query) use ($like) {
                $query
                    ->where('faltantes.id_cc_empleado', 'like', $like)
                    ->orWhere('faltantes.empleadoid', 'like', $like)
                    ->orWhere('faltantes.nombre_empleado', 'like', $like)
                    ->orWhere('faltantes.agencias_faltantes', 'like', $like);
            });
        }

        if ($estado === 'pendientes') {
            $rows->having('balance_pendiente', '>', 0.009);
        } elseif ($estado === 'saldados') {
            $rows->havingRaw('ABS(balance_pendiente) <= 0.009');
        } elseif ($estado === 'sobregiro') {
            $rows->having('balance_pendiente', '<', -0.009);
        }

        $allItems = $rows
            ->orderByDesc('balance_pendiente')
            ->orderByDesc('total_faltantes')
            ->get()
            ->map(fn ($row) => $this->mapRow($row));

        $summary = [
            'centros' => $allItems->count(),
            'centros_pendientes' => $allItems->where('balance_pendiente', '>', 0.009)->count(),
            'total_faltantes' => round($allItems->sum('total_faltantes'), 2),
            'total_abonos' => round($allItems->sum('total_abonos'), 2),
            'balance_pendiente' => round($allItems->sum('balance_pendiente'), 2),
            'total_credito' => round($allItems->sum('total_credito'), 2),
            'total_debito' => round($allItems->sum('total_debito'), 2),
        ];

        return response()->json([
            'data' => $allItems->take($limit)->values(),
            'summary' => $summary,
            'filters' => [
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_fin' => $fechaFin->toDateString(),
                'cuenta_abonos' => self::CUENTA_FALTANTES,
                'estado' => $estado,
                'displayed' => min($limit, $allItems->count()),
                'total' => $allItems->count(),
            ],
        ]);
    }

    public function detalle(Request $request)
    {
        $validated = $request->validate([
            'id_cc_empleado' => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['nullable', 'date_format:Y-m-d'],
            'fecha_fin' => ['nullable', 'date_format:Y-m-d'],
        ]);

        [$fechaInicio, $fechaFin] = $this->normalizarFechas(
            $validated['fecha_inicio'] ?? null,
            $validated['fecha_fin'] ?? null
        );

        $idCcEmpleado = trim((string) $validated['id_cc_empleado']);
        $faltantes = $this->faltantesDetallePorAgenciaQuery($idCcEmpleado, $fechaInicio, $fechaFin);
        $abonos = $this->abonosDetallePorAgenciaQuery($idCcEmpleado, $fechaInicio, $fechaFin);

        $items = DB::query()
            ->fromSub($faltantes, 'faltantes')
            ->leftJoinSub($abonos, 'abonos', function ($join) {
                $join->on('faltantes.agencia_id', '=', 'abonos.agencia_id');
            })
            ->selectRaw("
                faltantes.agencia_id,
                faltantes.id_cc_empleado,
                faltantes.companyid,
                faltantes.empleadoid,
                faltantes.nombre_empleado,
                faltantes.cantidad_faltantes,
                faltantes.total_faltantes,
                faltantes.primera_fecha_faltante,
                faltantes.ultima_fecha_faltante,
                COALESCE(abonos.cantidad_abonos, 0) AS cantidad_abonos,
                COALESCE(abonos.total_credito, 0) AS total_credito,
                COALESCE(abonos.total_debito, 0) AS total_debito,
                COALESCE(abonos.total_abonos, 0) AS total_abonos,
                abonos.ultima_fecha_abono,
                (faltantes.total_faltantes - COALESCE(abonos.total_credito, 0)) AS balance_pendiente
            ")
            ->orderByDesc('balance_pendiente')
            ->orderBy('faltantes.agencia_id')
            ->get()
            ->map(fn ($row) => $this->mapDetalleRow($row));

        return response()->json([
            'data' => $items,
            'summary' => [
                'agencias' => $items->count(),
                'total_faltantes' => round($items->sum('total_faltantes'), 2),
                'total_credito' => round($items->sum('total_credito'), 2),
                'total_abonos' => round($items->sum('total_abonos'), 2),
                'balance_pendiente' => round($items->sum('balance_pendiente'), 2),
            ],
            'filters' => [
                'id_cc_empleado' => $idCcEmpleado,
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_fin' => $fechaFin->toDateString(),
            ],
        ]);
    }

    public function abonos(Request $request)
    {
        $validated = $request->validate([
            'id_cc_empleado' => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['nullable', 'date_format:Y-m-d'],
            'fecha_fin' => ['nullable', 'date_format:Y-m-d'],
        ]);

        [$fechaInicio, $fechaFin] = $this->normalizarFechas(
            $validated['fecha_inicio'] ?? null,
            $validated['fecha_fin'] ?? null
        );

        $idCcEmpleado = trim((string) $validated['id_cc_empleado']);

        $items = DB::table('entradas_diario')
            ->where('cuenta', self::CUENTA_FALTANTES)
            ->where('id_centro_costo', $idCcEmpleado)
            ->whereBetween('fecha', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->orderBy('fecha')
            ->orderBy('no_asiento')
            ->orderBy('id')
            ->get([
                'id',
                'fecha',
                'no_asiento',
                'ref',
                'no_ref',
                'id_viejo',
                'id_centro_costo',
                'debito',
                'credito',
                'descripcion',
                'modulo',
                'creado_por',
                'fecha_grabado',
            ])
            ->map(fn ($row) => $this->mapAbonoRow($row));

        return response()->json([
            'data' => $items,
            'summary' => [
                'movimientos' => $items->count(),
                'total_debito' => round($items->sum('debito'), 2),
                'total_credito' => round($items->sum('credito'), 2),
                'total_abonos' => round($items->sum('abono_neto'), 2),
            ],
            'filters' => [
                'id_cc_empleado' => $idCcEmpleado,
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_fin' => $fechaFin->toDateString(),
                'cuenta_abonos' => self::CUENTA_FALTANTES,
            ],
        ]);
    }

    private function normalizarFechas(?string $fechaInicio, ?string $fechaFin): array
    {
        $inicio = $fechaInicio
            ? Carbon::createFromFormat('Y-m-d', $fechaInicio)->startOfDay()
            : now()->startOfMonth();
        $fin = $fechaFin
            ? Carbon::createFromFormat('Y-m-d', $fechaFin)->startOfDay()
            : now()->startOfDay();

        if ($inicio->gt($fin)) {
            [$inicio, $fin] = [$fin, $inicio];
        }

        return [$inicio, $fin];
    }

    private function faltantesPorCentroCostoQuery(Carbon $fechaInicio, Carbon $fechaFin)
    {
        $base = $this->faltantesSourceQuery()
            ->whereBetween('f.fecha', [$fechaInicio->toDateString(), $fechaFin->toDateString()]);

        $empresaExpr = "COALESCE(emp_cc.companyid, emp_ced.companyid)";
        $empleadoExpr = "COALESCE(emp_cc.empleadoid, emp_ced.empleadoid)";
        $idCcEmpleadoExpr = "COALESCE(emp_cc.idcentrocosto, emp_ced.idcentrocosto)";
        $nombreExpr = "TRIM(CONCAT(COALESCE(emp_cc.nombres, emp_ced.nombres, ''), ' ', COALESCE(emp_cc.apellidos, emp_ced.apellidos, '')))";

        return DB::query()
            ->fromSub($base, 'faltantes')
            ->leftJoinSub($this->centrosCostoPorTerminalQuery(), 'ccosto', function ($join) {
                $join->on('faltantes.agencia_id', '=', 'ccosto.agencia_id');
            })
            ->leftJoinSub($this->empleadosPreferidosPorCedulaCostoQuery(), 'emp_cc', function ($join) {
                $join->on('faltantes.identificacion', '=', 'emp_cc.cedula_normalizada')
                    ->on('ccosto.id_centro_costo', '=', 'emp_cc.idcentrocosto_clave');
            })
            ->leftJoinSub($this->empleadosPreferidosPorCedulaQuery(), 'emp_ced', function ($join) {
                $join->on('faltantes.identificacion', '=', 'emp_ced.cedula_normalizada');
            })
            ->whereNotNull(DB::raw($idCcEmpleadoExpr))
            ->whereRaw("{$idCcEmpleadoExpr} <> ''")
            ->selectRaw("
                {$idCcEmpleadoExpr} AS id_cc_empleado,
                GROUP_CONCAT(DISTINCT {$empresaExpr} ORDER BY {$empresaExpr} SEPARATOR ', ') AS companyid,
                GROUP_CONCAT(DISTINCT {$empleadoExpr} ORDER BY {$empleadoExpr} SEPARATOR ', ') AS empleadoid,
                GROUP_CONCAT(DISTINCT NULLIF({$nombreExpr}, '') ORDER BY {$nombreExpr} SEPARATOR ', ') AS nombre_empleado,
                COUNT(faltantes.fila_id) AS cantidad_faltantes,
                SUM(faltantes.monto) AS total_faltantes,
                GROUP_CONCAT(DISTINCT faltantes.agencia_id ORDER BY faltantes.agencia_id SEPARATOR ', ') AS agencias_faltantes,
                COUNT(DISTINCT faltantes.agencia_id) AS cantidad_agencias,
                MIN(faltantes.fecha) AS primera_fecha_faltante,
                MAX(faltantes.fecha) AS ultima_fecha_faltante
            ")
            ->groupBy(DB::raw($idCcEmpleadoExpr));
    }

    private function faltantesDetallePorAgenciaQuery(string $idCcEmpleado, Carbon $fechaInicio, Carbon $fechaFin)
    {
        $base = $this->faltantesSourceQuery()
            ->whereBetween('f.fecha', [$fechaInicio->toDateString(), $fechaFin->toDateString()]);

        $empresaExpr = "COALESCE(emp_cc.companyid, emp_ced.companyid)";
        $empleadoExpr = "COALESCE(emp_cc.empleadoid, emp_ced.empleadoid)";
        $idCcEmpleadoExpr = "COALESCE(emp_cc.idcentrocosto, emp_ced.idcentrocosto)";
        $nombreExpr = "TRIM(CONCAT(COALESCE(emp_cc.nombres, emp_ced.nombres, ''), ' ', COALESCE(emp_cc.apellidos, emp_ced.apellidos, '')))";

        return DB::query()
            ->fromSub($base, 'faltantes')
            ->leftJoinSub($this->centrosCostoPorTerminalQuery(), 'ccosto', function ($join) {
                $join->on('faltantes.agencia_id', '=', 'ccosto.agencia_id');
            })
            ->leftJoinSub($this->empleadosPreferidosPorCedulaCostoQuery(), 'emp_cc', function ($join) {
                $join->on('faltantes.identificacion', '=', 'emp_cc.cedula_normalizada')
                    ->on('ccosto.id_centro_costo', '=', 'emp_cc.idcentrocosto_clave');
            })
            ->leftJoinSub($this->empleadosPreferidosPorCedulaQuery(), 'emp_ced', function ($join) {
                $join->on('faltantes.identificacion', '=', 'emp_ced.cedula_normalizada');
            })
            ->whereRaw("{$idCcEmpleadoExpr} = ?", [$idCcEmpleado])
            ->selectRaw("
                faltantes.agencia_id,
                {$idCcEmpleadoExpr} AS id_cc_empleado,
                {$empresaExpr} AS companyid,
                {$empleadoExpr} AS empleadoid,
                {$nombreExpr} AS nombre_empleado,
                COUNT(faltantes.fila_id) AS cantidad_faltantes,
                SUM(faltantes.monto) AS total_faltantes,
                MIN(faltantes.fecha) AS primera_fecha_faltante,
                MAX(faltantes.fecha) AS ultima_fecha_faltante
            ")
            ->groupBy(
                'faltantes.agencia_id',
                DB::raw($idCcEmpleadoExpr),
                DB::raw($empresaExpr),
                DB::raw($empleadoExpr),
                DB::raw($nombreExpr)
            );
    }

    private function faltantesSourceQuery()
    {
        $cedulaSql = $this->faltantesCedulaSql('f');
        $idColumn = Schema::hasColumn('faltantes_bet', 'faltante_id') ? 'f.faltante_id' : 'f.id';

        return DB::table('faltantes_bet as f')->selectRaw("
            f.agencia_id,
            {$cedulaSql} AS identificacion,
            {$idColumn} AS fila_id,
            f.monto,
            f.fecha
        ");
    }

    private function faltantesCedulaSql(string $tableAlias): string
    {
        $candidatos = [];

        if (Schema::hasColumn('faltantes_bet', 'identificacion')) {
            $candidatos[] = "REPLACE(REPLACE(TRIM({$tableAlias}.identificacion), '-', ''), ' ', '')";
        }

        if (Schema::hasColumn('faltantes_bet', 'observacion')) {
            $candidatos[] = "REPLACE(REPLACE(TRIM({$tableAlias}.observacion), '-', ''), ' ', '')";
        }

        if ($candidatos === []) {
            return 'NULL';
        }

        $cases = collect($candidatos)
            ->map(fn ($col) => "WHEN {$col} REGEXP '^[0-9]{11}$' THEN {$col}")
            ->implode(' ');

        return "CASE {$cases} ELSE NULL END";
    }

    private function centrosCostoPorTerminalQuery()
    {
        $ordenPreferencia = 'COALESCE(cc.ocultar, 0) ASC, COALESCE(cc.inactivo, 0) ASC, cc.id_centro_costo ASC';

        return DB::table('centros_de_costo as cc')
            ->selectRaw("
                TRIM(cc.id_viejo) AS agencia_id,
                CAST(SUBSTRING_INDEX(GROUP_CONCAT(cc.id_centro_costo ORDER BY {$ordenPreferencia} SEPARATOR ','), ',', 1) AS UNSIGNED) AS id_centro_costo
            ")
            ->whereNotNull('cc.id_viejo')
            ->where('cc.id_viejo', '!=', '')
            ->groupBy(DB::raw('TRIM(cc.id_viejo)'));
    }

    private function empleadosCedulaNormalizadaSql(string $table = 'empleados'): string
    {
        return "REPLACE(REPLACE(TRIM({$table}.cedula), '-', ''), ' ', '')";
    }

    private function empleadosOrdenPreferenciaSql(string $table = 'e'): string
    {
        return "COALESCE({$table}.estatus, 0) DESC, CASE WHEN {$table}.fecha_egreso IS NULL THEN 1 ELSE 0 END DESC, {$table}.companyid ASC, {$table}.empleadoid ASC";
    }

    private function empleadosPreferidosPorCedulaCostoQuery()
    {
        $cedulaNormalizada = $this->empleadosCedulaNormalizadaSql('e');
        $ordenPreferencia = $this->empleadosOrdenPreferenciaSql('e');

        return DB::table('empleados as e')
            ->selectRaw("
                {$cedulaNormalizada} AS cedula_normalizada,
                e.idcentrocosto AS idcentrocosto_clave,
                CAST(SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(CAST(e.companyid AS CHAR), '') ORDER BY {$ordenPreferencia} SEPARATOR ','), ',', 1) AS UNSIGNED) AS companyid,
                CAST(SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(CAST(e.empleadoid AS CHAR), '') ORDER BY {$ordenPreferencia} SEPARATOR ','), ',', 1) AS UNSIGNED) AS empleadoid,
                CAST(SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(CAST(e.idcentrocosto AS CHAR), '') ORDER BY {$ordenPreferencia} SEPARATOR ','), ',', 1) AS UNSIGNED) AS idcentrocosto,
                SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(e.nombres, '') ORDER BY {$ordenPreferencia} SEPARATOR '||'), '||', 1) AS nombres,
                SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(e.apellidos, '') ORDER BY {$ordenPreferencia} SEPARATOR '||'), '||', 1) AS apellidos
            ")
            ->whereNotNull('e.cedula')
            ->where('e.cedula', '!=', '')
            ->whereNotNull('e.idcentrocosto')
            ->where('e.estatus', 1)
            ->whereNull('e.fecha_egreso')
            ->groupBy(DB::raw($cedulaNormalizada), 'e.idcentrocosto');
    }

    private function empleadosPreferidosPorCedulaQuery()
    {
        $cedulaNormalizada = $this->empleadosCedulaNormalizadaSql('e');
        $ordenPreferencia = $this->empleadosOrdenPreferenciaSql('e');

        return DB::table('empleados as e')
            ->selectRaw("
                {$cedulaNormalizada} AS cedula_normalizada,
                CAST(SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(CAST(e.companyid AS CHAR), '') ORDER BY {$ordenPreferencia} SEPARATOR ','), ',', 1) AS UNSIGNED) AS companyid,
                CAST(SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(CAST(e.empleadoid AS CHAR), '') ORDER BY {$ordenPreferencia} SEPARATOR ','), ',', 1) AS UNSIGNED) AS empleadoid,
                CAST(SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(CAST(e.idcentrocosto AS CHAR), '') ORDER BY {$ordenPreferencia} SEPARATOR ','), ',', 1) AS UNSIGNED) AS idcentrocosto,
                SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(e.nombres, '') ORDER BY {$ordenPreferencia} SEPARATOR '||'), '||', 1) AS nombres,
                SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(e.apellidos, '') ORDER BY {$ordenPreferencia} SEPARATOR '||'), '||', 1) AS apellidos
            ")
            ->whereNotNull('e.cedula')
            ->where('e.cedula', '!=', '')
            ->where('e.estatus', 1)
            ->whereNull('e.fecha_egreso')
            ->groupBy(DB::raw($cedulaNormalizada));
    }

    private function abonosPorCentroCostoQuery(Carbon $fechaInicio, Carbon $fechaFin)
    {
        return DB::table('entradas_diario')
            ->where('cuenta', self::CUENTA_FALTANTES)
            ->whereBetween('fecha', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->whereNotNull('id_centro_costo')
            ->where('id_centro_costo', '!=', '')
            ->selectRaw("
                id_centro_costo AS id_cc_empleado,
                COUNT(*) AS cantidad_abonos,
                SUM(credito) AS total_credito,
                SUM(debito) AS total_debito,
                SUM(credito - debito) AS total_abonos,
                GROUP_CONCAT(DISTINCT id_viejo ORDER BY id_viejo SEPARATOR ', ') AS agencias_abonos,
                MAX(fecha) AS ultima_fecha_abono
            ")
            ->groupBy('id_centro_costo');
    }

    private function abonosDetallePorAgenciaQuery(string $idCcEmpleado, Carbon $fechaInicio, Carbon $fechaFin)
    {
        return DB::table('entradas_diario')
            ->where('cuenta', self::CUENTA_FALTANTES)
            ->where('id_centro_costo', $idCcEmpleado)
            ->whereBetween('fecha', [$fechaInicio->toDateString(), $fechaFin->toDateString()])
            ->selectRaw("
                id_viejo AS agencia_id,
                COUNT(*) AS cantidad_abonos,
                SUM(credito) AS total_credito,
                SUM(debito) AS total_debito,
                SUM(credito - debito) AS total_abonos,
                MAX(fecha) AS ultima_fecha_abono
            ")
            ->whereNotNull('id_viejo')
            ->where('id_viejo', '!=', '')
            ->groupBy('id_viejo');
    }

    private function mapRow($row): array
    {
        $totalFaltantes = round((float) $row->total_faltantes, 2);
        $totalAbonos = round((float) $row->total_abonos, 2);
        $balance = round((float) $row->balance_pendiente, 2);

        return [
            'id_cc_empleado' => (string) $row->id_cc_empleado,
            'companyid' => $row->companyid,
            'empleadoid' => $row->empleadoid,
            'nombre_empleado' => trim((string) $row->nombre_empleado) ?: 'Sin especificar',
            'cantidad_faltantes' => (int) $row->cantidad_faltantes,
            'total_faltantes' => $totalFaltantes,
            'cantidad_abonos' => (int) $row->cantidad_abonos,
            'total_credito' => round((float) $row->total_credito, 2),
            'total_debito' => round((float) $row->total_debito, 2),
            'total_abonos' => $totalAbonos,
            'balance_pendiente' => $balance,
            'porcentaje_abonado' => $totalFaltantes > 0 ? round(($totalAbonos / $totalFaltantes) * 100, 2) : 0,
            'agencias_faltantes' => (string) $row->agencias_faltantes,
            'agencias_abonos' => (string) $row->agencias_abonos,
            'cantidad_agencias' => (int) $row->cantidad_agencias,
            'primera_fecha_faltante' => $row->primera_fecha_faltante,
            'ultima_fecha_faltante' => $row->ultima_fecha_faltante,
            'ultima_fecha_abono' => $row->ultima_fecha_abono,
            'estado' => $balance > 0.009 ? 'Pendiente' : ($balance < -0.009 ? 'Sobregiro' : 'Saldado'),
        ];
    }

    private function mapDetalleRow($row): array
    {
        $totalFaltantes = round((float) $row->total_faltantes, 2);
        $totalAbonos = round((float) $row->total_abonos, 2);
        $balance = round((float) $row->balance_pendiente, 2);

        return [
            'agencia_id' => (string) $row->agencia_id,
            'id_cc_empleado' => (string) $row->id_cc_empleado,
            'companyid' => $row->companyid,
            'empleadoid' => $row->empleadoid,
            'nombre_empleado' => trim((string) $row->nombre_empleado) ?: 'Sin especificar',
            'cantidad_faltantes' => (int) $row->cantidad_faltantes,
            'total_faltantes' => $totalFaltantes,
            'cantidad_abonos' => (int) $row->cantidad_abonos,
            'total_credito' => round((float) $row->total_credito, 2),
            'total_debito' => round((float) $row->total_debito, 2),
            'total_abonos' => $totalAbonos,
            'balance_pendiente' => $balance,
            'porcentaje_abonado' => $totalFaltantes > 0 ? round(($totalAbonos / $totalFaltantes) * 100, 2) : 0,
            'primera_fecha_faltante' => $row->primera_fecha_faltante,
            'ultima_fecha_faltante' => $row->ultima_fecha_faltante,
            'ultima_fecha_abono' => $row->ultima_fecha_abono,
            'estado' => $balance > 0.009 ? 'Pendiente' : ($balance < -0.009 ? 'Sobregiro' : 'Saldado'),
        ];
    }

    private function mapAbonoRow($row): array
    {
        $debito = round((float) $row->debito, 2);
        $credito = round((float) $row->credito, 2);

        return [
            'id' => (int) $row->id,
            'fecha' => $row->fecha,
            'no_asiento' => (string) $row->no_asiento,
            'ref' => (string) $row->ref,
            'no_ref' => (string) $row->no_ref,
            'agencia_id' => (string) $row->id_viejo,
            'id_cc_empleado' => (string) $row->id_centro_costo,
            'debito' => $debito,
            'credito' => $credito,
            'abono_neto' => round($credito - $debito, 2),
            'descripcion' => (string) $row->descripcion,
            'modulo' => (string) $row->modulo,
            'creado_por' => (string) $row->creado_por,
            'fecha_grabado' => $row->fecha_grabado,
        ];
    }
}
