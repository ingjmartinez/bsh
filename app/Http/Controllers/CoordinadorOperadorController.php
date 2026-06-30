<?php

namespace App\Http\Controllers;

use App\Models\Agencia;
use App\Models\CoordinadorOperador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CoordinadorOperadorController extends Controller
{
    private const CARGOS_DISPONIBLES = [
        'Gerente De Servicio',
        'Lider De Zona',
        'Recolector',
        'Socio',
    ];

    private function coordinadorOperadorTable(): string
    {
        return CoordinadorOperador::resolveTableName();
    }

    private function agenciaCodeColumn(): string
    {
        return Schema::hasColumn('agencias', 'codigo') ? 'codigo' : 'agencia';
    }

    private function agenciaNameColumn(): string
    {
        return Schema::hasColumn('agencias', 'nombre') ? 'nombre' : 'nombre_agencia';
    }

    private function agenciaCodeSelect(): string
    {
        return $this->agenciaCodeColumn() . ' as codigo';
    }

    private function agenciaNameSelect(): string
    {
        return $this->agenciaNameColumn() . ' as nombre';
    }

    private function coordinadorNombreSql(string $alias = 'co'): string
    {
        if (CoordinadorOperador::hasResolvedColumn('apellido')) {
            return "TRIM(CONCAT(COALESCE({$alias}.nombre, ''), ' ', COALESCE({$alias}.apellido, '')))";
        }

        return "TRIM(COALESCE({$alias}.nombre, ''))";
    }

    private function clearIndexCache(): void
    {
        Cache::forget('coordinador_operador.agencias_asignacion_data');
    }

    private function coordinadorOperadorPayload(array $validated): array
    {
        $payload = [
            'cedula' => $validated['cedula'] ?? null,
            'telefono' => $validated['telefono'],
        ];

        if (CoordinadorOperador::hasResolvedColumn('apellido')) {
            $payload['nombre'] = trim((string) $validated['nombre']);
            $payload['apellido'] = trim((string) ($validated['apellido'] ?? ''));
        } else {
            $payload['nombre'] = trim($validated['nombre'] . ' ' . ($validated['apellido'] ?? ''));
        }

        if (CoordinadorOperador::hasResolvedColumn('email')) {
            $payload['email'] = $validated['correo'];
        }

        if (CoordinadorOperador::hasResolvedColumn('correo')) {
            $payload['correo'] = $validated['correo'];
        }

        if (CoordinadorOperador::hasResolvedColumn('puesto')) {
            $payload['puesto'] = $validated['puesto'] ?? 'coordinador';
        }

        if (CoordinadorOperador::hasResolvedColumn('cargo')) {
            $payload['cargo'] = $validated['cargo'];
        }

        if (CoordinadorOperador::hasResolvedColumn('activo')) {
            $payload['activo'] = true;
        }

        return $payload;
    }

    public function index()
    {
        $registros = CoordinadorOperador::with([
                'agencias' => function ($query) {
                    $query->selectRaw('agencias.id, agencias.terminal, agencias.' . $this->agenciaCodeSelect() . ', agencias.' . $this->agenciaNameSelect());
                }
            ])
            ->withCount('agencias')
            ->orderByDesc('id')
            ->paginate(15);

        $agencias = collect();
        $asignacionesAgencia = collect();

        return view('coordinador_operador.index', compact('registros', 'agencias', 'asignacionesAgencia'));
    }

    public function asignacionData()
    {
        $tablaCoordinador = $this->coordinadorOperadorTable();

        $data = Cache::remember('coordinador_operador.agencias_asignacion_data', now()->addMinutes(10), function () use ($tablaCoordinador) {
            $agencias = Agencia::query()
                ->selectRaw('id, terminal, ' . $this->agenciaCodeSelect() . ', ' . $this->agenciaNameSelect())
                ->orderBy($this->agenciaCodeColumn())
                ->get()
                ->map(fn ($agencia) => [
                    'id' => (int) $agencia->id,
                    'terminal' => (string) ($agencia->terminal ?? ''),
                    'codigo' => (string) ($agencia->codigo ?? ''),
                    'nombre' => (string) ($agencia->nombre ?? ''),
                ])
                ->values();

            $asignacionesAgencia = DB::table('coordinador_operador_agencia as coa')
                ->join($tablaCoordinador . ' as co', 'co.id', '=', 'coa.coordinador_operador_id')
                ->select(
                    'coa.agencia_id',
                    'co.id as coordinador_id',
                    DB::raw($this->coordinadorNombreSql('co') . ' as nombre')
                )
                ->get()
                ->groupBy('agencia_id')
                ->map(function ($rows) {
                    return $rows->map(function ($row) {
                        return [
                            'id' => (int) $row->coordinador_id,
                            'nombre' => trim((string) ($row->nombre ?? '')),
                        ];
                    })->values();
                });

            return [
                'agencias' => $agencias,
                'asignacionesAgencia' => $asignacionesAgencia,
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        return view('coordinador_operador.create', [
            'cargosDisponibles' => self::CARGOS_DISPONIBLES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150'],
            'cargo' => ['required', 'in:' . implode(',', self::CARGOS_DISPONIBLES)],
            'cedula' => ['nullable', 'regex:/^\d{11}$/', 'unique:' . $this->coordinadorOperadorTable() . ',cedula'],
            'telefono' => ['required', 'regex:/^\d{11}$/'],
            'puesto' => ['nullable', 'in:coordinador,operador'],
        ], [
            'cedula.regex' => 'La cédula debe contener exactamente 11 dígitos numéricos.',
            'cargo.required' => 'Debe seleccionar un cargo.',
            'cargo.in' => 'El cargo seleccionado no es valido.',
            'cedula.regex' => 'La cedula debe contener exactamente 11 digitos numericos.',
            'telefono.required' => 'Campo de 11 Digitos obligatorios',
            'telefono.regex' => 'Campo de 11 Digitos obligatorios',
            'puesto.in' => 'El puesto debe ser coordinador u operador.',
        ]);

        CoordinadorOperador::create($this->coordinadorOperadorPayload($validated));
        $this->clearIndexCache();

        return redirect()->route('coordinador-operador.index')
            ->with('success', 'Registro creado correctamente.');
    }

    public function edit(CoordinadorOperador $coordinador_operador)
    {
        return view('coordinador_operador.edit', [
            'registro' => $coordinador_operador,
            'cargosDisponibles' => self::CARGOS_DISPONIBLES,
        ]);
    }

    public function update(Request $request, CoordinadorOperador $coordinador_operador)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150'],
            'cargo' => ['required', 'in:' . implode(',', self::CARGOS_DISPONIBLES)],
            'cedula' => ['nullable', 'regex:/^\d{11}$/', 'unique:' . $this->coordinadorOperadorTable() . ',cedula,' . $coordinador_operador->id],
            'telefono' => ['required', 'regex:/^\d{11}$/'],
            'puesto' => ['nullable', 'in:coordinador,operador'],
        ], [
            'cedula.regex' => 'La cédula debe contener exactamente 11 dígitos numéricos.',
            'telefono.required' => 'Campo de 11 Digitos obligatorios',
            'telefono.regex' => 'Campo de 11 Digitos obligatorios',
            'puesto.in' => 'El puesto debe ser coordinador u operador.',
        ]);

        $coordinador_operador->update($this->coordinadorOperadorPayload($validated));
        $this->clearIndexCache();

        return redirect()->route('coordinador-operador.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(CoordinadorOperador $coordinador_operador)
    {
        $coordinador_operador->delete();
        $this->clearIndexCache();

        return redirect()->route('coordinador-operador.index')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function asignarAgencias(Request $request, CoordinadorOperador $coordinador_operador)
    {
        $tablaCoordinador = $this->coordinadorOperadorTable();

        $validated = $request->validate([
            'agencias' => ['nullable', 'array'],
            'agencias.*' => ['integer', 'exists:agencias,id'],
            'confirmar_reasignacion' => ['nullable', 'boolean'],
        ]);

        $agenciasSeleccionadas = collect($validated['agencias'] ?? [])->map(fn($id) => (int) $id)->values();

        $conflictos = DB::table('coordinador_operador_agencia as coa')
            ->join($tablaCoordinador . ' as co', 'co.id', '=', 'coa.coordinador_operador_id')
            ->join('agencias as a', 'a.id', '=', 'coa.agencia_id')
            ->whereIn('coa.agencia_id', $agenciasSeleccionadas)
            ->where('coa.coordinador_operador_id', '!=', $coordinador_operador->id)
            ->select(
                'coa.agencia_id',
                'a.terminal',
                DB::raw($this->coordinadorNombreSql('co') . ' as nombre')
            )
            ->get();

        $confirmarReasignacion = (bool) ($validated['confirmar_reasignacion'] ?? false);

        if ($conflictos->isNotEmpty() && !$confirmarReasignacion) {
            return redirect()->route('coordinador-operador.index')
                ->with('error', 'Algunas agencias ya están asignadas a otro coordinador. Confirma la reasignación para moverlas.');
        }

        if ($conflictos->isNotEmpty() && $confirmarReasignacion) {
            DB::table('coordinador_operador_agencia')
                ->whereIn('agencia_id', $conflictos->pluck('agencia_id')->unique()->values())
                ->where('coordinador_operador_id', '!=', $coordinador_operador->id)
                ->delete();
        }

        $coordinador_operador->agencias()->sync($agenciasSeleccionadas->all());
        $this->clearIndexCache();

        return redirect()->route('coordinador-operador.index')
            ->with('success', 'Agencias asignadas correctamente.');
    }
}
