<?php

namespace App\Http\Controllers;

use App\Mail\IncumplimientoHorarioReportMail;
use App\Models\Agencia;
use App\Models\CoordinadorOperador;
use App\Models\OperadorRuta;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AgenciasExport;
use App\Imports\AgenciasActualizacionMasivaImport;
use App\Imports\AgenciasImport;

class AgenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('agencias.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        [$operadores, $coordinadores] = $this->obtenerOpcionesCoordinadorOperador();

        return view('agencias.create', compact('operadores', 'coordinadores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        [$operadores, $coordinadores] = $this->obtenerOpcionesCoordinadorOperador();

        $validated = $request->validate([
            'agencia' => 'required|string|max:25',
            'terminal' => [
                'nullable',
                'string',
                'max:25',
                function ($attribute, $value, $fail) {
                    if ($this->terminalExisteEnOtraAgencia((string) $value)) {
                        $fail('El codigo de terminal ya existe. Use la actualizacion masiva para modificar esa agencia.');
                    }
                },
            ],
            'nombre_agencia' => 'nullable|string|max:55',
            'horario_am' => ['nullable', 'string', 'max:35', 'regex:/^([1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)\s*\/\s*([1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)$/i'],
            'horario_pm' => ['nullable', 'string', 'max:35', 'regex:/^([1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)\s*\/\s*([1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)$/i'],
            'ciudad' => 'nullable|string|max:55',
            'ruta' => 'nullable|string|max:55',
            'empresa' => 'nullable|string|max:60',
            'operador' => ['nullable', 'string', 'max:55', Rule::in($operadores)],
            'coordinador' => ['nullable', 'string', 'max:55', Rule::in($coordinadores)],
            'estatus' => 'required|integer|in:0,1',
            'aplica_incentivo' => 'required|boolean',
        ], [
            'operador.in' => 'Seleccione un operador válido de la lista.',
            'coordinador.in' => 'Seleccione un coordinador válido de la lista.',
        ]);

        $agencia = Agencia::create($validated);
        $this->sincronizarAsignacionesCoordinadorOperador(
            $agencia->id,
            $validated['coordinador'] ?? '',
            $validated['operador'] ?? ''
        );

        return redirect()->route('agencias.index')
            ->with('success', 'Agencia creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Agencia $agencia)
    {
        return view('agencias.show', compact('agencia'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agencia $agencia)
    {
        [$operadores, $coordinadores] = $this->obtenerOpcionesCoordinadorOperador();

        return view('agencias.edit', compact('agencia', 'operadores', 'coordinadores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agencia $agencia)
    {
        [$operadores, $coordinadores] = $this->obtenerOpcionesCoordinadorOperador();

        $validated = $request->validate([
            'agencia' => 'required|string|max:255',
            'nombre_agencia' => 'nullable|string|max:255',
            'terminal' => [
                'nullable',
                'string',
                'max:25',
                function ($attribute, $value, $fail) use ($agencia) {
                    if ($this->terminalExisteEnOtraAgencia((string) $value, (int) $agencia->id)) {
                        $fail('El codigo de terminal ya pertenece a otra agencia.');
                    }
                },
            ],
            'horario_am' => ['nullable', 'string', 'max:35', 'regex:/^([1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)\s*\/\s*([1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)$/i'],
            'horario_pm' => ['nullable', 'string', 'max:35', 'regex:/^([1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)\s*\/\s*([1-9]|1[0-2]):[0-5][0-9]\s?(AM|PM)$/i'],
            'sistema' => 'nullable|string|max:255',
            'empresa' => 'nullable|string|max:60',
            'ciudad' => 'nullable|string|max:255',
            'ruta' => 'nullable|string|max:255',
            'operador' => ['nullable', 'string', 'max:255', Rule::in($operadores)],
            'coordinador' => ['nullable', 'string', 'max:255', Rule::in($coordinadores)],
            'estatus' => 'required|integer|in:0,1',
            'aplica_incentivo' => 'required|boolean',
        ], [
            'operador.in' => 'Seleccione un operador válido de la lista.',
            'coordinador.in' => 'Seleccione un coordinador válido de la lista.',
        ]);

        $agencia->update($validated);
        $this->sincronizarAsignacionesCoordinadorOperador(
            $agencia->id,
            $validated['coordinador'] ?? '',
            $validated['operador'] ?? ''
        );

        return redirect()->route('agencias.index')
            ->with('success', 'Agencia actualizada exitosamente.');
    }

    private function sincronizarAsignacionesCoordinadorOperador(int $agenciaId, string $coordinadorNombre = '', string $operadorNombre = ''): void
    {
        $this->sincronizarAsignacionCoordinador($agenciaId, $coordinadorNombre);
        $this->sincronizarAsignacionOperadorRuta($agenciaId, $operadorNombre);
    }

    private function sincronizarAsignacionCoordinador(int $agenciaId, string $nombreCompleto): void
    {
        $idsPuesto = CoordinadorOperador::query()
            ->where('puesto', 'coordinador')
            ->pluck('id');

        if ($idsPuesto->isNotEmpty()) {
            DB::table('coordinador_operador_agencia')
                ->where('agencia_id', $agenciaId)
                ->whereIn('coordinador_operador_id', $idsPuesto)
                ->delete();
        }

        $nombreCompleto = trim($nombreCompleto);
        if ($nombreCompleto === '') {
            return;
        }

        $coordinadorOperadorId = CoordinadorOperador::query()
            ->where('puesto', 'coordinador')
            ->whereRaw("TRIM(CONCAT(COALESCE(nombre, ''), ' ', COALESCE(apellido, ''))) = ?", [$nombreCompleto])
            ->value('id');

        if (!$coordinadorOperadorId) {
            return;
        }

        DB::table('coordinador_operador_agencia')->insertOrIgnore([
            'coordinador_operador_id' => $coordinadorOperadorId,
            'agencia_id' => $agenciaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function sincronizarAsignacionOperadorRuta(int $agenciaId, string $nombreCompleto): void
    {
        $idsPuesto = OperadorRuta::query()
            ->where('puesto', 'operador')
            ->pluck('id');

        if ($idsPuesto->isNotEmpty()) {
            DB::table('operador_ruta_agencia')
                ->where('agencia_id', $agenciaId)
                ->whereIn('operador_ruta_id', $idsPuesto)
                ->delete();
        }

        $nombreCompleto = trim($nombreCompleto);
        if ($nombreCompleto === '') {
            return;
        }

        $operadorRutaId = OperadorRuta::query()
            ->where('puesto', 'operador')
            ->whereRaw("TRIM(CONCAT(COALESCE(nombre, ''), ' ', COALESCE(apellido, ''))) = ?", [$nombreCompleto])
            ->value('id');

        if (!$operadorRutaId) {
            return;
        }

        DB::table('operador_ruta_agencia')->insertOrIgnore([
            'operador_ruta_id' => $operadorRutaId,
            'agencia_id' => $agenciaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function obtenerOpcionesCoordinadorOperador(): array
    {
        $registrosOperadorRuta = OperadorRuta::select('nombre', 'apellido', 'puesto')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        $registrosCoordinador = CoordinadorOperador::select('nombre', 'apellido', 'puesto')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        $operadores = $registrosOperadorRuta
            ->where('puesto', 'operador')
            ->map(fn($item) => trim($item->nombre . ' ' . $item->apellido))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $coordinadores = $registrosCoordinador
            ->where('puesto', 'coordinador')
            ->map(fn($item) => trim($item->nombre . ' ' . $item->apellido))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [$operadores, $coordinadores];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agencia $agencia)
    {
        $agencia->delete();

        return redirect()->route('agencias.index')
            ->with('success', 'Agencia eliminada exitosamente.');
    }

    /**
     * Get list of agencias for DataTable.
     */
    public function list(Request $request)
    {
        $query = Agencia::query();
        $estatusFilter = $request->input('estatus_filter', 'todos');
        $empresaFilter = $request->input('empresa_filter', 'todas');

        if ($estatusFilter === 'activo') {
            $query->where('estatus', 1);
        } elseif ($estatusFilter === 'inactivo') {
            $query->where('estatus', 0);
        }

        if ($empresaFilter === 'joselito') {
            $query->whereRaw('LOWER(COALESCE(empresa, "")) LIKE ?', ['%joselito%']);
        } elseif ($empresaFilter === 'negosur') {
            $query->whereRaw('LOWER(COALESCE(empresa, "")) LIKE ?', ['%negosur%']);
        }

        // Si hay búsqueda
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('agencia', 'like', "%{$search}%")
                  ->orWhere('nombre_agencia', 'like', "%{$search}%")
                  ->orWhere('terminal', 'like', "%{$search}%")
                                    ->orWhere('horario_am', 'like', "%{$search}%")
                                    ->orWhere('horario_pm', 'like', "%{$search}%")
                  ->orWhere('sistema', 'like', "%{$search}%")
                                      ->orWhere('empresa', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%")
                  ->orWhere('ruta', 'like', "%{$search}%")
                  ->orWhere('operador', 'like', "%{$search}%")
                                    ->orWhere('coordinador', 'like', "%{$search}%")
                                    ->orWhere('estatus', 'like', "%{$search}%")
                                    ->orWhere('aplica_incentivo', 'like', "%{$search}%");
            });
        }

        // Total de registros
        $totalRecords = Agencia::count();
        $filteredRecords = $query->count();

        // Paginación
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $agencias = $query->orderBy('created_at', 'desc')
                          ->skip($start)
                          ->take($length)
                          ->get();

        $totalActivas = Agencia::query()->where('estatus', 1)->count();
        $totalInactivas = Agencia::query()->where('estatus', 0)->count();
        $totalJoselito = Agencia::query()->whereRaw('LOWER(COALESCE(empresa, "")) LIKE ?', ['%joselito%'])->count();
        $totalNegosur = Agencia::query()->whereRaw('LOWER(COALESCE(empresa, "")) LIKE ?', ['%negosur%'])->count();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $agencias,
            'total_activas' => $totalActivas,
            'total_inactivas' => $totalInactivas,
            'total_joselito' => $totalJoselito,
            'total_negosur' => $totalNegosur,
        ]);
    }

    /**
     * API: Terminales con venta fija de la ultima semana no registradas en agencias.
     */
    public function noRegistradasVentaFijaSemana(Request $request)
    {
        $fechaCorteInput = trim((string) $request->query('fecha_corte', now()->toDateString()));

        try {
            $fechaCorte = Carbon::createFromFormat('Y-m-d', $fechaCorteInput)->endOfDay();
        } catch (\Throwable $e) {
            $fechaCorte = now()->endOfDay();
        }

        $resultado = $this->obtenerTerminalesNoRegistradasVentaFija($fechaCorte);

        return response()->json([
            'ok' => true,
            'desde' => $resultado['desde'],
            'hasta' => $resultado['hasta'],
            'total' => count($resultado['terminales']),
            'terminales' => $resultado['terminales'],
        ]);
    }

    /**
     * API: Agencias que tienen al menos un campo visible incompleto.
     */
    public function agenciasParaActualizar(Request $request)
    {
        $campos = $this->camposRevisionAgencia();
        $agencias = Agencia::query()
            ->select(array_merge(['id'], array_keys($campos)))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Agencia $agencia) use ($campos) {
                $camposFaltantes = [];

                foreach ($campos as $campo => $etiqueta) {
                    if ($this->valorAgenciaIncompleto($agencia->{$campo} ?? null)) {
                        $camposFaltantes[] = $etiqueta;
                    }
                }

                if (empty($camposFaltantes)) {
                    return null;
                }

                return [
                    'id' => $agencia->id,
                    'agencia' => $agencia->agencia,
                    'terminal' => $agencia->terminal,
                    'nombre_agencia' => $agencia->nombre_agencia,
                    'campos_faltantes' => $camposFaltantes,
                    'total_campos_faltantes' => count($camposFaltantes),
                    'edit_url' => route('agencias.edit', $agencia),
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'ok' => true,
            'total' => $agencias->count(),
            'agencias' => $agencias,
        ]);
    }

    private function camposRevisionAgencia(): array
    {
        return [
            'agencia' => 'Agencia',
            'terminal' => 'Terminal',
            'horario_am' => 'Horario AM',
            'horario_pm' => 'Horario PM',
            'nombre_agencia' => 'Nombre',
            'sistema' => 'Sistema',
            'empresa' => 'Empresa',
            'ciudad' => 'Ciudad',
            'ruta' => 'Ruta',
            'operador' => 'Operador',
            'coordinador' => 'Coordinador',
            'estatus' => 'Estatus',
            'aplica_incentivo' => 'Incentivo',
        ];
    }

    private function valorAgenciaIncompleto($value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return false;
        }

        $normalizado = trim((string) $value);

        return $normalizado === '' || in_array(strtolower($normalizado), ['-', 'n/a', 'na', 'null'], true);
    }

    /**
     * Registra masivamente las terminales no registradas de venta fija.
     */
    public function registrarNoRegistradasVentaFija(Request $request)
    {
        $fechaCorteInput = trim((string) $request->input('fecha_corte', now()->toDateString()));

        try {
            $fechaCorte = Carbon::createFromFormat('Y-m-d', $fechaCorteInput)->endOfDay();
        } catch (\Throwable $e) {
            $fechaCorte = now()->endOfDay();
        }

        $resultado = $this->obtenerTerminalesNoRegistradasVentaFija($fechaCorte);
        $terminales = collect($resultado['terminales'])
            ->pluck('terminal')
            ->filter()
            ->values()
            ->all();

        try {
            $registro = $this->registrarTerminalesBase($terminales);
        } catch (\Throwable $e) {
            Log::error('Error al registrar terminales no registradas de forma masiva.', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudieron registrar las terminales por un error de datos. Verifique la estructura de la tabla agencias e intente de nuevo.',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'registradas' => $registro['registradas'],
            'omitidas' => $registro['omitidas'],
            'total_solicitadas' => count($terminales),
            'agencias' => $registro['agencias'],
        ]);
    }

    /**
     * Registra una terminal no registrada sin asignar codigo de agencia.
     */
    public function registrarTerminalNoRegistrada(Request $request)
    {
        $validated = $request->validate([
            'terminal' => ['required', 'string', 'max:25'],
        ]);

        try {
            $registro = $this->registrarTerminalesBase([$validated['terminal']]);
        } catch (\Throwable $e) {
            Log::error('Error al registrar terminal no registrada.', [
                'terminal' => $validated['terminal'] ?? null,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo registrar la terminal por un error de datos. Verifique la estructura de la tabla agencias e intente de nuevo.',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'registradas' => $registro['registradas'],
            'omitidas' => $registro['omitidas'],
            'agencia' => $registro['agencias'][0] ?? null,
        ]);
    }

    /**
     * API: Agencias actualmente desactivadas.
     */
    public function agenciasInactivas(Request $request)
    {
        $agencias = $this->obtenerAgenciasInactivas();

        return response()->json([
            'ok' => true,
            'total' => count($agencias),
            'activas' => 0,
            'inactivas' => count($agencias),
            'agencias' => $agencias,
        ]);
    }

    /**
     * API: Agencias sin venta positiva en los ultimos 30 dias.
     */
    public function agenciasSinVentaTreintaDias(Request $request)
    {
        $resultado = $this->obtenerAgenciasSinVentaTreintaDias();

        return response()->json([
            'ok' => true,
            'desde' => $resultado['desde'],
            'hasta' => $resultado['hasta'],
            'total' => count($resultado['agencias']),
            'activas' => collect($resultado['agencias'])->where('estatus', 1)->count(),
            'inactivas' => collect($resultado['agencias'])->where('estatus', 0)->count(),
            'agencias' => $resultado['agencias'],
        ]);
    }

    /**
     * API: Agencias desactivadas que registraron ventas positivas en 30 dias.
     */
    public function agenciasInactivasConVentaTreintaDias(Request $request)
    {
        $resultado = $this->obtenerAgenciasInactivasConVentaTreintaDias();

        return response()->json([
            'ok' => true,
            'desde' => $resultado['desde'],
            'hasta' => $resultado['hasta'],
            'total' => count($resultado['agencias']),
            'activas' => 0,
            'inactivas' => count($resultado['agencias']),
            'agencias' => $resultado['agencias'],
        ]);
    }

    /**
     * API: Terminales con ventas positivas en 30 dias que no existen en agencias.
     */
    public function agenciasNoRegistradasConVentaTreintaDias(Request $request)
    {
        $resultado = $this->obtenerAgenciasNoRegistradasConVentaTreintaDias();

        return response()->json([
            'ok' => true,
            'desde' => $resultado['desde'],
            'hasta' => $resultado['hasta'],
            'total' => count($resultado['agencias']),
            'activas' => 0,
            'inactivas' => 0,
            'agencias' => $resultado['agencias'],
        ]);
    }

    /**
     * Desactiva masivamente agencias que no tienen venta positiva en 30 dias.
     */
    public function desactivarAgenciasSinVentaTreintaDias(Request $request)
    {
        $resultado = $this->obtenerAgenciasSinVentaTreintaDias();
        $idsActivas = collect($resultado['agencias'])
            ->where('estatus', 1)
            ->pluck('id')
            ->values();

        if ($idsActivas->isNotEmpty()) {
            Agencia::query()
                ->whereIn('id', $idsActivas)
                ->update(['estatus' => 0]);
        }

        return response()->json([
            'ok' => true,
            'desactivadas' => $idsActivas->count(),
            'omitidas' => count($resultado['agencias']) - $idsActivas->count(),
        ]);
    }

    /**
     * Activa o desactiva una agencia desde el modal de inactividad.
     */
    public function actualizarEstatusAgencia(Request $request)
    {
        $validated = $request->validate([
            'agencia_id' => ['required', 'integer', 'exists:agencias,id'],
            'estatus' => ['required', 'integer', 'in:0,1'],
        ]);

        $agencia = Agencia::findOrFail((int) $validated['agencia_id']);
        $agencia->update(['estatus' => (int) $validated['estatus']]);

        return response()->json([
            'ok' => true,
            'agencia' => [
                'id' => $agencia->id,
                'agencia' => $agencia->agencia,
                'terminal' => $agencia->terminal,
                'estatus' => (int) $agencia->estatus,
            ],
        ]);
    }

    private function registrarTerminalesBase(array $terminales): array
    {
        $terminalesPorClave = collect($terminales)
            ->mapWithKeys(function ($terminal) {
                $terminalOriginal = trim((string) $terminal);
                $terminalKey = $this->normalizarTerminal($terminalOriginal);

                return $terminalKey !== '0' ? [$terminalKey => $terminalOriginal] : [];
            });

        if ($terminalesPorClave->isEmpty()) {
            return [
                'registradas' => 0,
                'omitidas' => 0,
                'agencias' => [],
            ];
        }

        $terminalesExistentes = Agencia::query()
            ->whereNotNull('terminal')
            ->pluck('terminal')
            ->map(fn($terminal) => $this->normalizarTerminal((string) $terminal))
            ->filter(fn($terminal) => $terminal !== '0')
            ->unique()
            ->flip();

        $registradas = [];
        $omitidas = 0;

        foreach ($terminalesPorClave as $terminalKey => $terminalOriginal) {
            if ($terminalesExistentes->has($terminalKey)) {
                $omitidas++;
                continue;
            }

            $agencia = $this->crearAgenciaNoRegistrada($terminalOriginal);

            $terminalesExistentes->put($terminalKey, true);
            $registradas[] = [
                'id' => $agencia->id,
                'agencia' => $agencia->agencia,
                'terminal' => $agencia->terminal,
                'edit_url' => route('agencias.edit', $agencia),
            ];
        }

        return [
            'registradas' => count($registradas),
            'omitidas' => $omitidas,
            'agencias' => $registradas,
        ];
    }

    private function crearAgenciaNoRegistrada(string $terminalOriginal): Agencia
    {
        $payloadBase = [
            'agencia' => null,
            'terminal' => substr(trim($terminalOriginal), 0, 25),
            'nombre_agencia' => null,
            'estatus' => 1,
            'aplica_incentivo' => 1,
        ];

        try {
            return Agencia::create($payloadBase);
        } catch (QueryException $e) {
            if (!$this->esErrorPorNuloNoPermitido($e)) {
                throw $e;
            }
        }

        // Compatibilidad con esquemas legacy donde algunos campos string son NOT NULL.
        return Agencia::create([
            'agencia' => '',
            'terminal' => $payloadBase['terminal'],
            'nombre_agencia' => 'Terminal no registrada',
            'horario_am' => '',
            'horario_pm' => '',
            'sistema' => '',
            'empresa' => '',
            'ciudad' => '',
            'ruta' => '',
            'operador' => '',
            'coordinador' => '',
            'estatus' => 1,
            'aplica_incentivo' => 1,
        ]);
    }

    private function esErrorPorNuloNoPermitido(QueryException $e): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());
        $driverCode = (string) ($e->errorInfo[1] ?? '');
        $mensaje = strtolower($e->getMessage());

        return ($sqlState === '23000' || $driverCode === '1048')
            && str_contains($mensaje, 'cannot be null');
    }

    private function obtenerAgenciasSinVentaTreintaDias(): array
    {
        $fechaInicio = now()->subDays(29)->startOfDay()->toDateString();
        $fechaFin = now()->endOfDay()->toDateString();

        $ventasPorTerminal = $this->obtenerVentasPorTerminal($fechaInicio, $fechaFin);

        $agencias = Agencia::query()
            ->select('id', 'agencia', 'terminal', 'nombre_agencia', 'empresa', 'ciudad', 'ruta', 'estatus')
            ->whereNotNull('terminal')
            ->where('estatus', 1)
            ->orderBy('terminal')
            ->get()
            ->filter(function (Agencia $agencia) use ($ventasPorTerminal) {
                $terminalKey = $this->normalizarTerminal((string) $agencia->terminal);

                return $terminalKey !== '0' && !$ventasPorTerminal->has($terminalKey);
            })
            ->map(fn(Agencia $agencia) => $this->formatearAgenciaModal($agencia))
            ->values()
            ->all();

        return [
            'desde' => $fechaInicio,
            'hasta' => $fechaFin,
            'agencias' => $agencias,
        ];
    }

    private function obtenerAgenciasInactivas(): array
    {
        return Agencia::query()
            ->select('id', 'agencia', 'terminal', 'nombre_agencia', 'empresa', 'ciudad', 'ruta', 'estatus')
            ->where('estatus', 0)
            ->orderBy('terminal')
            ->get()
            ->map(fn(Agencia $agencia) => $this->formatearAgenciaModal($agencia))
            ->values()
            ->all();
    }

    private function obtenerAgenciasInactivasConVentaTreintaDias(): array
    {
        $fechaInicio = now()->subDays(29)->startOfDay()->toDateString();
        $fechaFin = now()->endOfDay()->toDateString();
        $ventasPorTerminal = $this->obtenerVentasPorTerminal($fechaInicio, $fechaFin);

        $agencias = Agencia::query()
            ->select('id', 'agencia', 'terminal', 'nombre_agencia', 'empresa', 'ciudad', 'ruta', 'estatus')
            ->where('estatus', 0)
            ->whereNotNull('terminal')
            ->orderBy('terminal')
            ->get()
            ->filter(function (Agencia $agencia) use ($ventasPorTerminal) {
                $terminalKey = $this->normalizarTerminal((string) $agencia->terminal);

                return $terminalKey !== '0' && $ventasPorTerminal->has($terminalKey);
            })
            ->map(fn(Agencia $agencia) => $this->formatearAgenciaModal($agencia))
            ->values()
            ->all();

        return [
            'desde' => $fechaInicio,
            'hasta' => $fechaFin,
            'agencias' => $agencias,
        ];
    }

    private function obtenerAgenciasNoRegistradasConVentaTreintaDias(): array
    {
        $fechaInicio = now()->subDays(29)->startOfDay()->toDateString();
        $fechaFin = now()->endOfDay()->toDateString();

        $terminalesRegistradas = Agencia::query()
            ->whereNotNull('terminal')
            ->pluck('terminal')
            ->map(fn($terminal) => $this->normalizarTerminal((string) $terminal))
            ->filter(fn($terminal) => $terminal !== '0')
            ->unique()
            ->flip();

        $ventasBet = DB::table('vt_usuarios_bet')
            ->selectRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(agencia_id AS CHAR))), ''), '0') AS terminal_key")
            ->selectRaw("TRIM(CAST(agencia_id AS CHAR)) AS terminal_original")
            ->selectRaw('COALESCE(monto, 0) AS monto')
            ->selectRaw('fecha AS fecha')
            ->whereNotNull('agencia_id')
            ->whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin)
            ->whereRaw('COALESCE(monto, 0) > 0');

        $ventasNet = DB::table('vt_usuarios_net')
            ->selectRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(agencia_id AS CHAR))), ''), '0') AS terminal_key")
            ->selectRaw("TRIM(CAST(agencia_id AS CHAR)) AS terminal_original")
            ->selectRaw('COALESCE(monto, 0) AS monto')
            ->selectRaw('fecha AS fecha')
            ->whereNotNull('agencia_id')
            ->whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin)
            ->whereRaw('COALESCE(monto, 0) > 0');

        $agencias = DB::query()
            ->fromSub($ventasBet->unionAll($ventasNet), 'v')
            ->selectRaw('terminal_key')
            ->selectRaw('MIN(NULLIF(terminal_original, "")) AS terminal_original')
            ->selectRaw('COUNT(DISTINCT DATE(fecha)) AS dias_con_venta')
            ->selectRaw('MAX(fecha) AS ultima_fecha')
            ->selectRaw('SUM(COALESCE(monto, 0)) AS total_venta')
            ->whereRaw('terminal_key <> ?', ['0'])
            ->groupBy('terminal_key')
            ->orderByDesc('total_venta')
            ->get()
            ->filter(function ($row) use ($terminalesRegistradas) {
                return !$terminalesRegistradas->has((string) ($row->terminal_key ?? '0'));
            })
            ->map(function ($row) {
                $terminalOriginal = trim((string) ($row->terminal_original ?? ''));
                $terminal = $terminalOriginal !== '' ? $terminalOriginal : (string) ($row->terminal_key ?? '');

                return [
                    'id' => null,
                    'agencia' => '-',
                    'terminal' => $terminal,
                    'nombre_agencia' => 'Terminal no registrada',
                    'empresa' => '-',
                    'ciudad' => '-',
                    'ruta' => '-',
                    'estatus' => null,
                    'estatus_texto' => 'No registrada',
                    'edit_url' => null,
                    'no_registrada' => true,
                    'dias_con_venta' => (int) ($row->dias_con_venta ?? 0),
                    'ultima_fecha' => $row->ultima_fecha ? Carbon::parse((string) $row->ultima_fecha)->toDateString() : null,
                    'total_venta' => round((float) ($row->total_venta ?? 0), 2),
                ];
            })
            ->values()
            ->all();

        return [
            'desde' => $fechaInicio,
            'hasta' => $fechaFin,
            'agencias' => $agencias,
        ];
    }

    private function obtenerVentasPorTerminal(string $fechaInicio, string $fechaFin)
    {
        $ventasBet = DB::table('vt_usuarios_bet')
            ->selectRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(agencia_id AS CHAR))), ''), '0') AS terminal_key")
            ->whereNotNull('agencia_id')
            ->whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin)
            ->whereRaw('COALESCE(monto, 0) > 0');

        $ventasNet = DB::table('vt_usuarios_net')
            ->selectRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(agencia_id AS CHAR))), ''), '0') AS terminal_key")
            ->whereNotNull('agencia_id')
            ->whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin)
            ->whereRaw('COALESCE(monto, 0) > 0');

        return DB::query()
            ->fromSub($ventasBet->unionAll($ventasNet), 'v')
            ->whereRaw('terminal_key <> ?', ['0'])
            ->distinct()
            ->pluck('terminal_key')
            ->map(fn($terminal) => (string) $terminal)
            ->flip();
    }

    private function formatearAgenciaModal(Agencia $agencia): array
    {
        return [
            'id' => $agencia->id,
            'agencia' => $agencia->agencia,
            'terminal' => $agencia->terminal,
            'nombre_agencia' => $agencia->nombre_agencia,
            'empresa' => $agencia->empresa,
            'ciudad' => $agencia->ciudad,
            'ruta' => $agencia->ruta,
            'estatus' => (int) $agencia->estatus,
            'estatus_texto' => (int) $agencia->estatus === 1 ? 'Activa' : 'Inactiva',
            'edit_url' => route('agencias.edit', $agencia),
        ];
    }

    /**
     * Vista: Agencias con incumplimiento de horario.
     */
    public function incumplimientosHorario()
    {
        return view('agencias.incumplimientos');
    }

    /**
     * API: Listado de incumplimiento (entrada/salida) por agencia.
     */
    public function listIncumplimientosHorario(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());
        $soloIncumplidas = $request->input('solo_incumplidas', '1') === '1';

        $agencias = Agencia::query()
            ->select('id', 'agencia', 'nombre_agencia', 'terminal', 'horario_am', 'horario_pm')
            ->whereNotNull('terminal')
            ->where(function ($q) {
                $q->whereNotNull('horario_am')
                  ->orWhereNotNull('horario_pm');
            })
            ->get();

        $mapAsistencia = $this->consolidarAsistenciasPorTerminal($fecha);

        $rows = [];

        foreach ($agencias as $agencia) {
            $terminalKey = $this->normalizarTerminal($agencia->terminal);
            $asistencia = $mapAsistencia[$terminalKey] ?? null;

            $entradaAmProgramada = $this->extraerHoraInicio($agencia->horario_am);
            $salidaAmProgramada = $this->extraerHoraFin($agencia->horario_am);
            $entradaPmProgramada = $this->extraerHoraInicio($agencia->horario_pm);
            $salidaPmProgramada = $this->extraerHoraFin($agencia->horario_pm);

            // Para validar tardanza/salida anticipada se mantiene:
            // entrada del primer bloque disponible y salida del último bloque disponible.
            $entradaProgramada = $entradaAmProgramada ?: $entradaPmProgramada;
            $salidaProgramada = $salidaPmProgramada ?: $salidaAmProgramada;

            $entradaAmProgramadaDateTime = $this->parseFechaHora($fecha, $entradaAmProgramada);
            $salidaAmProgramadaDateTime = $this->parseFechaHora($fecha, $salidaAmProgramada);
            $entradaPmProgramadaDateTime = $this->parseFechaHora($fecha, $entradaPmProgramada);
            $salidaPmProgramadaDateTime = $this->parseFechaHora($fecha, $salidaPmProgramada);

            $entradaProgramadaDateTime = $this->parseFechaHora($fecha, $entradaProgramada);
            $salidaProgramadaDateTime = $this->parseFechaHora($fecha, $salidaProgramada);

            $entradasReales = $this->parsearHorasReales($asistencia['entradas'] ?? []);
            $salidasReales = $this->parsearHorasReales($asistencia['salidas'] ?? []);

            // Compatibilidad: se mantiene entrada_real como primera entrada y salida_real como última salida.
            $entradaReal = $entradasReales[0] ?? null;
            $salidaReal = !empty($salidasReales) ? $salidasReales[array_key_last($salidasReales)] : null;

            // Nuevas columnas: salida AM real y entrada PM real.
            $salidaAmReal = $this->seleccionarHoraCercana(
                $salidasReales,
                $salidaAmProgramadaDateTime,
                $entradaAmProgramadaDateTime,
                $entradaPmProgramadaDateTime
            );

            $entradaPmReal = $this->seleccionarHoraCercana(
                $entradasReales,
                $entradaPmProgramadaDateTime,
                $salidaAmProgramadaDateTime,
                $salidaPmProgramadaDateTime
            );

            $incumpleEntrada = false;
            $incumpleSalida = false;
            $minutosTarde = 0;
            $minutosSalidaAntes = 0;
            $observaciones = [];

            if ($entradaProgramadaDateTime && $entradaReal) {
                if ($entradaReal->greaterThan($entradaProgramadaDateTime)) {
                    $incumpleEntrada = true;
                    $minutosTarde = $entradaProgramadaDateTime->diffInMinutes($entradaReal);
                    $observaciones[] = 'Entrada tardía';
                }
            } elseif ($entradaProgramadaDateTime && !$entradaReal) {
                $incumpleEntrada = true;
                $observaciones[] = 'Sin registro de entrada';
            }

            if ($salidaProgramadaDateTime && $salidaReal) {
                if ($salidaReal->lessThan($salidaProgramadaDateTime)) {
                    $incumpleSalida = true;
                    $minutosSalidaAntes = $salidaReal->diffInMinutes($salidaProgramadaDateTime);
                    $observaciones[] = 'Salida anticipada';
                }
            } elseif ($salidaProgramadaDateTime && !$salidaReal) {
                $incumpleSalida = true;
                $observaciones[] = 'Sin registro de salida';
            }

            $incumplida = $incumpleEntrada || $incumpleSalida;

            if ($soloIncumplidas && !$incumplida) {
                continue;
            }

            $rows[] = [
                'agencia_id' => $agencia->id,
                'agencia' => $agencia->agencia,
                'nombre_agencia' => $agencia->nombre_agencia,
                'terminal' => $agencia->terminal,
                'horario_am' => $agencia->horario_am,
                'horario_pm' => $agencia->horario_pm,
                'entrada_am_programada' => $entradaAmProgramada,
                'salida_am_programada' => $salidaAmProgramada,
                'entrada_pm_programada' => $entradaPmProgramada,
                'salida_pm_programada' => $salidaPmProgramada,
                'entrada_programada' => $entradaProgramada,
                'salida_programada' => $salidaProgramada,
                'entrada_real' => $entradaReal ? $entradaReal->format('h:i A') : '-',
                'salida_am_real' => $salidaAmReal ? $salidaAmReal->format('h:i A') : '-',
                'entrada_pm_real' => $entradaPmReal ? $entradaPmReal->format('h:i A') : '-',
                'salida_real' => $salidaReal ? $salidaReal->format('h:i A') : '-',
                'minutos_tarde' => $minutosTarde,
                'minutos_salida_antes' => $minutosSalidaAntes,
                'incumple_entrada' => $incumpleEntrada,
                'incumple_salida' => $incumpleSalida,
                'incumplida' => $incumplida,
                'estado' => $incumplida ? 'INCUMPLE' : 'CUMPLE',
                'observaciones' => empty($observaciones) ? 'Cumple horario' : implode(' | ', $observaciones),
                'fuente' => $asistencia['fuente'] ?? '-',
            ];
        }

        return response()->json([
            'fecha' => $fecha,
            'total' => count($rows),
            'incumplidas' => collect($rows)->where('incumplida', true)->count(),
            'data' => array_values($rows),
        ]);
    }

    /**
     * Enviar mini reporte por correo de una fila de incumplimiento.
     */
    public function enviarMiniReporteIncumplimiento(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'fecha' => ['required', 'date'],
            'registro' => ['required', 'array'],
            'registro.agencia' => ['nullable', 'string', 'max:25'],
            'registro.nombre_agencia' => ['nullable', 'string', 'max:150'],
            'registro.terminal' => ['nullable', 'string', 'max:50'],
            'registro.horario_am' => ['nullable', 'string', 'max:35'],
            'registro.horario_pm' => ['nullable', 'string', 'max:35'],
            'registro.entrada_am_programada' => ['nullable', 'string', 'max:20'],
            'registro.salida_am_programada' => ['nullable', 'string', 'max:20'],
            'registro.entrada_pm_programada' => ['nullable', 'string', 'max:20'],
            'registro.salida_pm_programada' => ['nullable', 'string', 'max:20'],
            'registro.entrada_real' => ['nullable', 'string', 'max:20'],
            'registro.salida_am_real' => ['nullable', 'string', 'max:20'],
            'registro.entrada_pm_real' => ['nullable', 'string', 'max:20'],
            'registro.salida_real' => ['nullable', 'string', 'max:20'],
            'registro.minutos_tarde' => ['nullable', 'numeric', 'min:0'],
            'registro.minutos_salida_antes' => ['nullable', 'numeric', 'min:0'],
            'registro.fuente' => ['nullable', 'string', 'max:30'],
            'registro.estado' => ['nullable', 'string', 'max:20'],
            'registro.observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $registro = $validated['registro'];

        $payload = [
            'fecha' => Carbon::parse($validated['fecha'])->format('d/m/Y'),
            'agencia' => $registro['agencia'] ?? '-',
            'nombre_agencia' => $registro['nombre_agencia'] ?? '-',
            'terminal' => $registro['terminal'] ?? '-',
            'horario_am' => $registro['horario_am'] ?? '-',
            'horario_pm' => $registro['horario_pm'] ?? '-',
            'entrada_am_programada' => $registro['entrada_am_programada'] ?? '-',
            'salida_am_programada' => $registro['salida_am_programada'] ?? '-',
            'entrada_pm_programada' => $registro['entrada_pm_programada'] ?? '-',
            'salida_pm_programada' => $registro['salida_pm_programada'] ?? '-',
            'entrada_real' => $registro['entrada_real'] ?? '-',
            'salida_am_real' => $registro['salida_am_real'] ?? '-',
            'entrada_pm_real' => $registro['entrada_pm_real'] ?? '-',
            'salida_real' => $registro['salida_real'] ?? '-',
            'minutos_tarde' => (int) round((float) ($registro['minutos_tarde'] ?? 0)),
            'minutos_salida_antes' => (int) round((float) ($registro['minutos_salida_antes'] ?? 0)),
            'fuente' => $registro['fuente'] ?? '-',
            'estado' => strtoupper((string) ($registro['estado'] ?? 'CUMPLE')),
            'observaciones' => $registro['observaciones'] ?? 'Sin observaciones',
        ];

        Mail::to($validated['email'])->send(new IncumplimientoHorarioReportMail($payload));

        return response()->json([
            'ok' => true,
            'message' => 'Mini reporte enviado correctamente.',
        ]);
    }

    private function consolidarAsistenciasPorTerminal(string $fecha): array
    {
        $bet = DB::table('asistencias_bet')
            ->selectRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM agencia_id), ''), '0') as terminal_key")
            ->selectRaw('primer_login as entrada')
            ->selectRaw('ultimo_login as salida')
            ->whereDate('fecha', $fecha)
            ->get();

        $net = DB::table('asistencias_net')
            ->selectRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM agencia), ''), NULLIF(TRIM(LEADING '0' FROM terminal), ''), '0') as terminal_key")
            ->selectRaw('entrada')
            ->selectRaw('salida')
            ->where(function ($q) use ($fecha) {
                $q->whereDate('entrada', $fecha)
                  ->orWhereDate('salida', $fecha);
            })
            ->get();

        $map = [];

        foreach ($bet as $row) {
            if (!isset($map[$row->terminal_key])) {
                $map[$row->terminal_key] = [
                    'entrada' => null,
                    'salida' => null,
                    'entradas' => [],
                    'salidas' => [],
                    'has_bet' => false,
                    'has_net' => false,
                    'fuente' => '-',
                ];
            }

            if ($row->entrada) {
                $map[$row->terminal_key]['entradas'][] = $row->entrada;
                if (!$map[$row->terminal_key]['entrada'] || Carbon::parse($row->entrada)->lessThan(Carbon::parse($map[$row->terminal_key]['entrada']))) {
                    $map[$row->terminal_key]['entrada'] = $row->entrada;
                }
            }

            if ($row->salida) {
                $map[$row->terminal_key]['salidas'][] = $row->salida;
                if (!$map[$row->terminal_key]['salida'] || Carbon::parse($row->salida)->greaterThan(Carbon::parse($map[$row->terminal_key]['salida']))) {
                    $map[$row->terminal_key]['salida'] = $row->salida;
                }
            }

            $map[$row->terminal_key]['has_bet'] = true;
        }

        foreach ($net as $row) {
            if (!isset($map[$row->terminal_key])) {
                $map[$row->terminal_key] = [
                    'entrada' => null,
                    'salida' => null,
                    'entradas' => [],
                    'salidas' => [],
                    'has_bet' => false,
                    'has_net' => false,
                    'fuente' => '-',
                ];
            }

            if ($row->entrada) {
                $map[$row->terminal_key]['entradas'][] = $row->entrada;
                if (!$map[$row->terminal_key]['entrada'] || Carbon::parse($row->entrada)->lessThan(Carbon::parse($map[$row->terminal_key]['entrada']))) {
                    $map[$row->terminal_key]['entrada'] = $row->entrada;
                }
            }

            if ($row->salida) {
                $map[$row->terminal_key]['salidas'][] = $row->salida;
                if (!$map[$row->terminal_key]['salida'] || Carbon::parse($row->salida)->greaterThan(Carbon::parse($map[$row->terminal_key]['salida']))) {
                    $map[$row->terminal_key]['salida'] = $row->salida;
                }
            }

            $map[$row->terminal_key]['has_net'] = true;
        }

        foreach ($map as $terminalKey => $row) {
            $map[$terminalKey]['fuente'] = $row['has_bet'] && $row['has_net']
                ? 'BET/NET'
                : ($row['has_bet'] ? 'BET' : 'NET');
        }

        return $map;
    }

    private function normalizarTerminal(?string $terminal): string
    {
        if (!$terminal) {
            return '0';
        }

        $valor = ltrim(trim($terminal), '0');
        return $valor === '' ? '0' : $valor;
    }

    private function extraerHoraInicio(?string $horario): ?string
    {
        if (!$horario || !str_contains($horario, '/')) {
            return null;
        }

        $partes = explode('/', $horario);
        return isset($partes[0]) ? trim($partes[0]) : null;
    }

    private function extraerHoraFin(?string $horario): ?string
    {
        if (!$horario || !str_contains($horario, '/')) {
            return null;
        }

        $partes = explode('/', $horario);
        return isset($partes[1]) ? trim($partes[1]) : null;
    }

    private function parseFechaHora(string $fecha, ?string $hora): ?Carbon
    {
        if (!$hora) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d g:i A', $fecha . ' ' . strtoupper($hora));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parsearHorasReales(array $horas): array
    {
        $parsed = [];

        foreach ($horas as $hora) {
            if (!$hora) {
                continue;
            }

            try {
                $parsed[] = Carbon::parse($hora);
            } catch (\Throwable $e) {
                // Ignorar valores no parseables
            }
        }

        usort($parsed, fn (Carbon $a, Carbon $b) => $a->getTimestamp() <=> $b->getTimestamp());

        return $parsed;
    }

    private function seleccionarHoraCercana(array $horas, ?Carbon $objetivo, ?Carbon $desde = null, ?Carbon $hasta = null): ?Carbon
    {
        $filtradas = array_values(array_filter($horas, function (Carbon $hora) use ($desde, $hasta) {
            if ($desde && $hora->lessThan($desde)) {
                return false;
            }

            if ($hasta && $hora->greaterThan($hasta)) {
                return false;
            }

            return true;
        }));

        if (empty($filtradas)) {
            return null;
        }

        if (!$objetivo) {
            return $filtradas[0];
        }

        usort($filtradas, fn (Carbon $a, Carbon $b) => abs($a->diffInSeconds($objetivo, false)) <=> abs($b->diffInSeconds($objetivo, false)));

        return $filtradas[0] ?? null;
    }

    /**
     * Devuelve terminales con venta fija en 7 dias que no existen en tabla agencias.
     */
    private function obtenerTerminalesNoRegistradasVentaFija(Carbon $fechaCorte): array
    {
        $fechaInicio = $fechaCorte->copy()->subDays(6)->startOfDay()->toDateString();
        $fechaFin = $fechaCorte->copy()->toDateString();

        $terminalesRegistradas = Agencia::query()
            ->whereNotNull('terminal')
            ->pluck('terminal')
            ->map(fn($terminal) => $this->normalizarTerminal((string) $terminal))
            ->filter(fn($terminal) => $terminal !== '0')
            ->unique()
            ->values()
            ->flip();

        $tiposVentaFija = ['tradicional', 'fija', 'venta fija', 'venta_fija'];

        $ventasBet = DB::table('vt_usuarios_bet')
            ->selectRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(agencia_id AS CHAR))), ''), '0') AS terminal_key")
            ->selectRaw("TRIM(CAST(agencia_id AS CHAR)) AS terminal_original")
            ->selectRaw('COALESCE(monto, 0) AS monto')
            ->selectRaw('fecha AS fecha')
            ->whereNotNull('agencia_id')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->whereRaw('COALESCE(monto, 0) > 0')
            ->where(function ($query) use ($tiposVentaFija) {
                foreach ($tiposVentaFija as $index => $tipo) {
                    if ($index === 0) {
                        $query->whereRaw('LOWER(TRIM(COALESCE(tipo, ""))) = ?', [$tipo]);
                        continue;
                    }

                    $query->orWhereRaw('LOWER(TRIM(COALESCE(tipo, ""))) = ?', [$tipo]);
                }
            });

        $ventasNet = DB::table('vt_usuarios_net')
            ->selectRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(agencia_id AS CHAR))), ''), '0') AS terminal_key")
            ->selectRaw("TRIM(CAST(agencia_id AS CHAR)) AS terminal_original")
            ->selectRaw('COALESCE(monto, 0) AS monto')
            ->selectRaw('fecha AS fecha')
            ->whereNotNull('agencia_id')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->whereRaw('COALESCE(monto, 0) > 0')
            ->where(function ($query) use ($tiposVentaFija) {
                foreach ($tiposVentaFija as $index => $tipo) {
                    if ($index === 0) {
                        $query->whereRaw('LOWER(TRIM(COALESCE(tipo, ""))) = ?', [$tipo]);
                        continue;
                    }

                    $query->orWhereRaw('LOWER(TRIM(COALESCE(tipo, ""))) = ?', [$tipo]);
                }
            });

        $ventasConsolidadas = DB::query()
            ->fromSub($ventasBet->unionAll($ventasNet), 'v')
            ->selectRaw('terminal_key')
            ->selectRaw('MIN(NULLIF(terminal_original, "")) AS terminal_original')
            ->selectRaw('COUNT(DISTINCT DATE(fecha)) AS dias_con_venta')
            ->selectRaw('MAX(fecha) AS ultima_fecha')
            ->whereRaw('terminal_key <> ?', ['0'])
            ->groupBy('terminal_key')
            ->orderBy('terminal_key')
            ->get();

        $terminalesNoRegistradas = $ventasConsolidadas
            ->filter(function ($row) use ($terminalesRegistradas) {
                $terminal = (string) ($row->terminal_key ?? '0');
                return !$terminalesRegistradas->has($terminal);
            })
            ->map(function ($row) {
                $terminalOriginal = trim((string) ($row->terminal_original ?? ''));

                return [
                    'terminal' => $terminalOriginal !== '' ? $terminalOriginal : (string) ($row->terminal_key ?? ''),
                    'terminal_key' => (string) ($row->terminal_key ?? ''),
                    'dias_con_venta' => (int) ($row->dias_con_venta ?? 0),
                    'ultima_fecha' => $row->ultima_fecha ? Carbon::parse((string) $row->ultima_fecha)->toDateString() : null,
                ];
            })
            ->values()
            ->all();

        return [
            'desde' => $fechaInicio,
            'hasta' => $fechaFin,
            'terminales' => $terminalesNoRegistradas,
        ];
    }

    /**
     * Export agencias to Excel
     */
    public function export()
    {
        return Excel::download(new AgenciasExport, 'agencias_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Import agencias from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $import = new AgenciasImport();
            Excel::import($import, $request->file('file'));

            $resultado = [
                'importadas' => $import->importadas,
                'omitidas' => $import->totalOmitidas(),
                'omitidas_existentes' => $import->omitidasExistentes,
                'omitidas_duplicadas_archivo' => $import->omitidasDuplicadasArchivo,
                'omitidas_sin_terminal' => $import->omitidasSinTerminal,
            ];

            $mensaje = "Importacion completada. Creadas: {$import->importadas}. Omitidas: {$resultado['omitidas']}.";

            return redirect()->route('agencias.index')
                ->with('success', $mensaje)
                ->with('import_result', $resultado);
        } catch (\Exception $e) {
            return redirect()->route('agencias.index')
                ->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }

    /**
     * Actualizacion masiva selectiva desde Excel.
     * Solo actualiza los campos con valor en cada fila.
     */
    public function massUpdate(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:4096',
        ]);

        try {
            $import = new AgenciasActualizacionMasivaImport();
            Excel::import($import, $request->file('file'));

            $rows = $import->rows ?? collect();
            if ($rows->isEmpty()) {
                return redirect()->route('agencias.index')
                    ->with('error', 'El archivo no contiene filas para procesar.');
            }

            [$operadores, $coordinadores] = $this->obtenerOpcionesCoordinadorOperador();
            $operadoresSet = collect($operadores)->flip();
            $coordinadoresSet = collect($coordinadores)->flip();

            $procesadas = 0;
            $actualizadas = 0;
            $sinCambios = 0;
            $noEncontradas = 0;
            $filasInvalidas = 0;

            foreach ($rows as $rowCollection) {
                $procesadas++;
                $row = collect($rowCollection)->toArray();

                $agencia = $this->buscarAgenciaParaActualizacion($row);
                if (!$agencia) {
                    $noEncontradas++;
                    continue;
                }

                $updates = $this->extraerCamposParaActualizacionMasiva($row);

                if (array_key_exists('operador', $updates) && $updates['operador'] !== '' && !$operadoresSet->has($updates['operador'])) {
                    $filasInvalidas++;
                    continue;
                }

                if (array_key_exists('coordinador', $updates) && $updates['coordinador'] !== '' && !$coordinadoresSet->has($updates['coordinador'])) {
                    $filasInvalidas++;
                    continue;
                }

                if (empty($updates)) {
                    $sinCambios++;
                    continue;
                }

                if (
                    array_key_exists('terminal', $updates)
                    && $this->terminalExisteEnOtraAgencia((string) $updates['terminal'], (int) $agencia->id)
                ) {
                    $filasInvalidas++;
                    continue;
                }

                $agencia->update($updates);
                $actualizadas++;

                if (array_key_exists('coordinador', $updates) || array_key_exists('operador', $updates)) {
                    $this->sincronizarAsignacionesCoordinadorOperador(
                        $agencia->id,
                        (string) ($agencia->coordinador ?? ''),
                        (string) ($agencia->operador ?? '')
                    );
                }
            }

            $resultado = [
                'procesadas' => $procesadas,
                'actualizadas' => $actualizadas,
                'sin_cambios' => $sinCambios,
                'no_encontradas' => $noEncontradas,
                'invalidas' => $filasInvalidas,
            ];

            $mensaje = "Actualizacion masiva completada. Actualizadas: {$actualizadas}.";

            return redirect()->route('agencias.index')
                ->with('success', $mensaje)
                ->with('mass_update_result', $resultado);
        } catch (\Exception $e) {
            return redirect()->route('agencias.index')
                ->with('error', 'Error en actualizacion masiva: ' . $e->getMessage());
        }
    }

    /**
     * Previsualiza coincidencias de terminales antes de actualizar masivamente.
     */
    public function massUpdatePreview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:4096',
        ]);

        try {
            $import = new AgenciasActualizacionMasivaImport();
            Excel::import($import, $request->file('file'));

            $rows = $import->rows ?? collect();
            if ($rows->isEmpty()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El archivo no contiene filas para procesar.',
                ], 422);
            }

            $terminales = $rows
                ->map(function ($rowCollection) {
                    $row = collect($rowCollection)->toArray();
                    $terminal = $this->valorColumna($row, ['terminal']);
                    return trim((string) ($terminal ?? ''));
                })
                ->filter(fn($terminal) => $terminal !== '')
                ->values();

            $terminalesUnicos = $terminales->unique()->values();

            $terminalesEncontradas = Agencia::query()
                ->whereIn('terminal', $terminalesUnicos)
                ->pluck('terminal')
                ->map(fn($terminal) => trim((string) $terminal))
                ->filter(fn($terminal) => $terminal !== '')
                ->unique()
                ->values();

            $terminalesNoEncontradas = $terminalesUnicos
                ->diff($terminalesEncontradas)
                ->values();

            return response()->json([
                'ok' => true,
                'total_filas' => $rows->count(),
                'terminales_leidas' => $terminales->count(),
                'terminales_unicas' => $terminalesUnicos->count(),
                'encontradas' => $terminalesEncontradas->count(),
                'no_encontradas' => $terminalesNoEncontradas->count(),
                'terminales_no_encontradas' => $terminalesNoEncontradas->all(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error al reconocer terminales: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function buscarAgenciaParaActualizacion(array $row): ?Agencia
    {
        $id = $this->valorColumna($row, ['id']);
        if ($id !== null && $id !== '') {
            return Agencia::query()->find((int) $id);
        }

        $terminal = $this->valorColumna($row, ['terminal']);
        if ($terminal !== null && trim((string) $terminal) !== '') {
            return Agencia::query()->where('terminal', trim((string) $terminal))->first();
        }

        $codigoAgencia = $this->valorColumna($row, ['agencia']);
        if ($codigoAgencia !== null && trim((string) $codigoAgencia) !== '') {
            return Agencia::query()->where('agencia', trim((string) $codigoAgencia))->first();
        }

        return null;
    }

    private function extraerCamposParaActualizacionMasiva(array $row): array
    {
        $updates = [];

        $mapeo = [
            'agencia' => ['agencia'],
            'terminal' => ['terminal'],
            'horario_am' => ['horario_am', 'horario am'],
            'horario_pm' => ['horario_pm', 'horario pm'],
            'nombre_agencia' => ['nombre_agencia', 'nombre agencia'],
            'sistema' => ['sistema'],
            'empresa' => ['empresa'],
            'ciudad' => ['ciudad'],
            'ruta' => ['ruta'],
            'operador' => ['operador'],
            'coordinador' => ['coordinador'],
            'estatus' => ['estatus'],
            'aplica_incentivo' => ['aplica_incentivo', 'aplica incentivo'],
        ];

        foreach ($mapeo as $campo => $aliases) {
            $valor = $this->valorColumna($row, $aliases);

            if ($valor === null || trim((string) $valor) === '') {
                continue;
            }

            if ($campo === 'estatus') {
                $updates[$campo] = $this->parseEstatus((string) $valor);
                continue;
            }

            if ($campo === 'aplica_incentivo') {
                $updates[$campo] = $this->parseAplicaIncentivo((string) $valor);
                continue;
            }

            $updates[$campo] = trim((string) $valor);
        }

        return $updates;
    }

    private function valorColumna(array $row, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            $clave = strtolower(trim((string) $alias));
            $claveConGuionBajo = str_replace(' ', '_', $clave);

            if (array_key_exists($clave, $row)) {
                return $row[$clave];
            }

            if (array_key_exists($claveConGuionBajo, $row)) {
                return $row[$claveConGuionBajo];
            }
        }

        return null;
    }

    private function terminalExisteEnOtraAgencia(?string $terminal, ?int $exceptoId = null): bool
    {
        $terminalKey = $this->normalizarTerminal($terminal);
        if ($terminalKey === '0') {
            return false;
        }

        $query = Agencia::query()
            ->whereNotNull('terminal')
            ->whereRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM TRIM(CAST(terminal AS CHAR))), ''), '0') = ?", [$terminalKey]);

        if ($exceptoId !== null) {
            $query->where('id', '<>', $exceptoId);
        }

        return $query->exists();
    }

    private function parseEstatus(string $value): int
    {
        $normalized = strtoupper(trim($value));
        if ($normalized === '1' || $normalized === 'ACTIVO' || $normalized === 'ACTIVE' || $normalized === 'SI' || $normalized === 'S') {
            return 1;
        }

        return 0;
    }

    private function parseAplicaIncentivo(string $value): int
    {
        $normalized = strtoupper(trim($value));
        if ($normalized === 'SI' || $normalized === 'S' || $normalized === 'YES' || $normalized === 'Y' || $normalized === '1') {
            return 1;
        }

        return 0;
    }

    /**
     * Download import template
     */
    public function template()
    {
        $headers = [
            'Agencia',
            'Terminal',
            'Horario AM',
            'Horario PM',
            'Nombre Agencia',
            'Sistema',
            'Empresa',
            'Ciudad',
            'Ruta',
            'Operador',
            'Coordinador',
            'Estatus',
            'Aplica Incentivo',
        ];

        $data = [
            $headers,
            ['20907', '5546', '7:00 AM / 2:00 PM', '2:00 PM / 9:00 PM', 'Agencia Ejemplo', 'Lotobet', 'Grupo A', 'San Pedro', 'Ruta 0501', 'Jose Ruby', 'Aramis', '1', 'SI'],
        ];

        $filename = 'plantilla_agencias.xlsx';

        return Excel::download(new class($data) implements 
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithStyles,
            \Maatwebsite\Excel\Concerns\ShouldAutoSize
        {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, $filename);
    }

    /**
     * Download template for selective mass update.
     */
    public function massUpdateTemplate()
    {
        $headers = [
            'ID',
            'Terminal',
            'Agencia',
            'Nombre Agencia',
            'Empresa',
            'Ciudad',
            'Ruta',
            'Operador',
            'Coordinador',
            'Horario AM',
            'Horario PM',
            'Sistema',
            'Estatus',
            'Aplica Incentivo',
        ];

        $data = [
            $headers,
        ];

        $filename = 'plantilla_actualizacion_masiva_agencias.xlsx';

        return Excel::download(new class($data) implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithStyles,
            \Maatwebsite\Excel\Concerns\ShouldAutoSize
        {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, $filename);
    }
}
