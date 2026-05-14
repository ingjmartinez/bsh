<?php

namespace App\Http\Controllers;

use App\Models\Agencia;
use App\Models\OperadorRuta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperadorRutaController extends Controller
{
    public function index()
    {
        $registros = OperadorRuta::with('agencias:id,agencia,nombre_agencia,terminal')
            ->withCount('agencias')
            ->orderByDesc('id')
            ->paginate(15);

        $agencias = Agencia::select('id', 'agencia', 'nombre_agencia')
            ->addSelect('terminal')
            ->orderBy('agencia')
            ->get();

        $asignacionesAgencia = DB::table('operador_ruta_agencia as ora')
            ->join('operador_ruta as oru', 'oru.id', '=', 'ora.operador_ruta_id')
            ->select(
                'ora.agencia_id',
                'oru.id as operador_ruta_id',
                'oru.nombre',
                'oru.apellido'
            )
            ->get()
            ->groupBy('agencia_id')
            ->map(function ($rows) {
                return $rows->map(function ($row) {
                    return [
                        'id' => (int) $row->operador_ruta_id,
                        'nombre' => trim(($row->nombre ?? '') . ' ' . ($row->apellido ?? '')),
                    ];
                })->values();
            });

        return view('operaciones.operador_ruta.index', compact('registros', 'agencias', 'asignacionesAgencia'));
    }

    public function create()
    {
        return view('operaciones.operador_ruta.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150'],
            'cedula' => ['required', 'regex:/^\d{11}$/', 'unique:operador_ruta,cedula'],
            'telefono' => ['required', 'regex:/^\d{10}$/'],
            'puesto' => ['required', 'in:coordinador,operador'],
        ], [
            'cedula.regex' => 'La cédula debe contener exactamente 11 dígitos numéricos.',
            'telefono.required' => 'Campo de 10 Digitos obligatorios',
            'telefono.regex' => 'Campo de 10 Digitos obligatorios',
            'puesto.in' => 'El puesto debe ser coordinador u operador.',
        ]);

        OperadorRuta::create($validated);

        return redirect()->route('operador-ruta.index')
            ->with('success', 'Registro creado correctamente.');
    }

    public function edit(OperadorRuta $operador_ruta)
    {
        return view('operaciones.operador_ruta.edit', [
            'registro' => $operador_ruta,
        ]);
    }

    public function update(Request $request, OperadorRuta $operador_ruta)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150'],
            'cedula' => ['required', 'regex:/^\d{11}$/', 'unique:operador_ruta,cedula,' . $operador_ruta->id],
            'telefono' => ['required', 'regex:/^\d{10}$/'],
            'puesto' => ['required', 'in:coordinador,operador'],
        ], [
            'cedula.regex' => 'La cédula debe contener exactamente 11 dígitos numéricos.',
            'telefono.required' => 'Campo de 10 Digitos obligatorios',
            'telefono.regex' => 'Campo de 10 Digitos obligatorios',
            'puesto.in' => 'El puesto debe ser coordinador u operador.',
        ]);

        $operador_ruta->update($validated);

        return redirect()->route('operador-ruta.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(OperadorRuta $operador_ruta)
    {
        $operador_ruta->delete();

        return redirect()->route('operador-ruta.index')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function asignarAgencias(Request $request, OperadorRuta $operador_ruta)
    {
        $validated = $request->validate([
            'agencias' => ['nullable', 'array'],
            'agencias.*' => ['integer', 'exists:agencias,id'],
            'confirmar_reasignacion' => ['nullable', 'boolean'],
        ]);

        $agenciasSeleccionadas = collect($validated['agencias'] ?? [])->map(fn($id) => (int) $id)->values();

        $conflictos = DB::table('operador_ruta_agencia as ora')
            ->join('operador_ruta as oru', 'oru.id', '=', 'ora.operador_ruta_id')
            ->join('agencias as a', 'a.id', '=', 'ora.agencia_id')
            ->whereIn('ora.agencia_id', $agenciasSeleccionadas)
            ->where('ora.operador_ruta_id', '!=', $operador_ruta->id)
            ->select(
                'ora.agencia_id',
                'a.terminal',
                'oru.nombre',
                'oru.apellido'
            )
            ->get();

        $confirmarReasignacion = (bool) ($validated['confirmar_reasignacion'] ?? false);

        if ($conflictos->isNotEmpty() && !$confirmarReasignacion) {
            return redirect()->route('operador-ruta.index')
                ->with('error', 'Algunas agencias ya están asignadas a otro coordinador. Confirma la reasignación para moverlas.');
        }

        if ($conflictos->isNotEmpty() && $confirmarReasignacion) {
            DB::table('operador_ruta_agencia')
                ->whereIn('agencia_id', $conflictos->pluck('agencia_id')->unique()->values())
                ->where('operador_ruta_id', '!=', $operador_ruta->id)
                ->delete();
        }

        $operador_ruta->agencias()->sync($agenciasSeleccionadas->all());

        return redirect()->route('operador-ruta.index')
            ->with('success', 'Agencias asignadas correctamente.');
    }
}
