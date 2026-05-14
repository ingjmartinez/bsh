<?php

namespace App\Http\Controllers;

use App\Models\Agencia;
use App\Models\CoordinadorOperador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoordinadorOperadorController extends Controller
{
    public function index()
    {
        $registros = CoordinadorOperador::with('agencias:id,codigo,nombre,terminal')
            ->withCount('agencias')
            ->orderByDesc('id')
            ->paginate(15);

        $agencias = Agencia::select('id', 'codigo', 'nombre', 'terminal')
            ->orderBy('codigo')
            ->get();

        $asignacionesAgencia = DB::table('coordinador_operador_agencia as coa')
            ->join('coordinadores_operador as co', 'co.id', '=', 'coa.coordinador_operador_id')
            ->select(
                'coa.agencia_id',
                'co.id as coordinador_id',
                'co.nombre'
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

        return view('coordinador_operador.index', compact('registros', 'agencias', 'asignacionesAgencia'));
    }

    public function create()
    {
        return view('coordinador_operador.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150'],
            'cedula' => ['required', 'regex:/^\d{11}$/', 'unique:coordinadores_operador,cedula'],
            'telefono' => ['required', 'regex:/^\d{10}$/'],
            'puesto' => ['nullable', 'in:coordinador,operador'],
        ], [
            'cedula.regex' => 'La cédula debe contener exactamente 11 dígitos numéricos.',
            'telefono.required' => 'Campo de 10 Digitos obligatorios',
            'telefono.regex' => 'Campo de 10 Digitos obligatorios',
            'puesto.in' => 'El puesto debe ser coordinador u operador.',
        ]);

        CoordinadorOperador::create([
            'nombre' => trim($validated['nombre'] . ' ' . ($validated['apellido'] ?? '')),
            'email' => $validated['correo'],
            'cedula' => $validated['cedula'],
            'telefono' => $validated['telefono'],
            'activo' => true,
        ]);

        return redirect()->route('coordinador-operador.index')
            ->with('success', 'Registro creado correctamente.');
    }

    public function edit(CoordinadorOperador $coordinador_operador)
    {
        return view('coordinador_operador.edit', [
            'registro' => $coordinador_operador,
        ]);
    }

    public function update(Request $request, CoordinadorOperador $coordinador_operador)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150'],
            'cedula' => ['required', 'regex:/^\d{11}$/', 'unique:coordinadores_operador,cedula,' . $coordinador_operador->id],
            'telefono' => ['required', 'regex:/^\d{10}$/'],
            'puesto' => ['nullable', 'in:coordinador,operador'],
        ], [
            'cedula.regex' => 'La cédula debe contener exactamente 11 dígitos numéricos.',
            'telefono.required' => 'Campo de 10 Digitos obligatorios',
            'telefono.regex' => 'Campo de 10 Digitos obligatorios',
            'puesto.in' => 'El puesto debe ser coordinador u operador.',
        ]);

        $coordinador_operador->update([
            'nombre' => trim($validated['nombre'] . ' ' . ($validated['apellido'] ?? '')),
            'email' => $validated['correo'],
            'cedula' => $validated['cedula'],
            'telefono' => $validated['telefono'],
            'activo' => true,
        ]);

        return redirect()->route('coordinador-operador.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(CoordinadorOperador $coordinador_operador)
    {
        $coordinador_operador->delete();

        return redirect()->route('coordinador-operador.index')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function asignarAgencias(Request $request, CoordinadorOperador $coordinador_operador)
    {
        $validated = $request->validate([
            'agencias' => ['nullable', 'array'],
            'agencias.*' => ['integer', 'exists:agencias,id'],
            'confirmar_reasignacion' => ['nullable', 'boolean'],
        ]);

        $agenciasSeleccionadas = collect($validated['agencias'] ?? [])->map(fn($id) => (int) $id)->values();

        $conflictos = DB::table('coordinador_operador_agencia as coa')
            ->join('coordinadores_operador as co', 'co.id', '=', 'coa.coordinador_operador_id')
            ->join('agencias as a', 'a.id', '=', 'coa.agencia_id')
            ->whereIn('coa.agencia_id', $agenciasSeleccionadas)
            ->where('coa.coordinador_operador_id', '!=', $coordinador_operador->id)
            ->select(
                'coa.agencia_id',
                'a.terminal',
                'co.nombre'
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

        return redirect()->route('coordinador-operador.index')
            ->with('success', 'Agencias asignadas correctamente.');
    }
}
