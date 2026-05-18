<?php

namespace App\Http\Controllers;

use App\Models\Agencia;
use App\Models\OperadorRuta;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RutaController extends Controller
{
    public function index()
    {
        $registros = Ruta::with([
                'operadorAsignado:id,nombre,apellido',
                'agencias:id,agencia,nombre_agencia,terminal',
            ])
            ->withCount('agencias')
            ->orderByDesc('id')
            ->paginate(15);

        $operadores = OperadorRuta::query()
            ->where('puesto', 'operador')
            ->select('id', 'nombre', 'apellido')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        $agencias = Agencia::select('id', 'agencia', 'nombre_agencia')
            ->addSelect('terminal')
            ->orderBy('agencia')
            ->get();

        $asignacionesAgencia = DB::table('ruta_agencia as ra')
            ->join('rutas as r', 'r.id', '=', 'ra.ruta_id')
            ->select(
                'ra.agencia_id',
                'r.id as ruta_id',
                'r.nombre_ruta'
            )
            ->get()
            ->groupBy('agencia_id')
            ->map(function ($rows) {
                return $rows->map(function ($row) {
                    return [
                        'id' => (int) $row->ruta_id,
                        'nombre' => (string) ($row->nombre_ruta ?? ''),
                    ];
                })->values();
            });

        return view('operaciones.ruta.index', compact('registros', 'operadores', 'agencias', 'asignacionesAgencia'));
    }

    public function create()
    {
        $operadores = OperadorRuta::query()
            ->where('puesto', 'operador')
            ->select('id', 'nombre', 'apellido')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        $empresas = $this->getOpcionesEmpresa();

        return view('operaciones.ruta.create', compact('operadores', 'empresas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_ruta' => ['required', 'string', 'max:100', 'unique:rutas,nombre_ruta'],
            'empresa' => ['required', 'in:Negosur,BSH Support'],
            'operador_ruta_id' => ['required', 'integer', 'exists:operador_ruta,id'],
        ], [
            'nombre_ruta.required' => 'El nombre de ruta es obligatorio.',
            'nombre_ruta.unique' => 'Ya existe una ruta con ese nombre.',
            'empresa.required' => 'Debe seleccionar una empresa.',
            'empresa.in' => 'La empresa seleccionada no es valida.',
            'operador_ruta_id.required' => 'Debe seleccionar un operador.',
        ]);

        Ruta::create($validated);

        return redirect()->route('ruta.index')
            ->with('success', 'Registro creado correctamente.');
    }

    public function edit(Ruta $ruta)
    {
        $operadores = OperadorRuta::query()
            ->where('puesto', 'operador')
            ->select('id', 'nombre', 'apellido')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();
        $empresas = $this->getOpcionesEmpresa();

        return view('operaciones.ruta.edit', [
            'registro' => $ruta,
            'operadores' => $operadores,
            'empresas' => $empresas,
        ]);
    }

    public function update(Request $request, Ruta $ruta)
    {
        $validated = $request->validate([
            'nombre_ruta' => ['required', 'string', 'max:100', 'unique:rutas,nombre_ruta,' . $ruta->id],
            'empresa' => ['required', 'in:Negosur,BSH Support'],
            'operador_ruta_id' => ['required', 'integer', 'exists:operador_ruta,id'],
        ], [
            'nombre_ruta.required' => 'El nombre de ruta es obligatorio.',
            'nombre_ruta.unique' => 'Ya existe una ruta con ese nombre.',
            'empresa.required' => 'Debe seleccionar una empresa.',
            'empresa.in' => 'La empresa seleccionada no es valida.',
            'operador_ruta_id.required' => 'Debe seleccionar un operador.',
        ]);

        $ruta->update($validated);

        return redirect()->route('ruta.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Ruta $ruta)
    {
        $ruta->delete();

        return redirect()->route('ruta.index')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function asignarAgencias(Request $request, Ruta $ruta)
    {
        $validated = $request->validate([
            'agencias' => ['nullable', 'array'],
            'agencias.*' => ['integer', 'exists:agencias,id'],
            'confirmar_reasignacion' => ['nullable', 'boolean'],
        ]);

        $agenciasSeleccionadas = collect($validated['agencias'] ?? [])->map(fn($id) => (int) $id)->values();

        $conflictos = DB::table('ruta_agencia as ra')
            ->join('rutas as r', 'r.id', '=', 'ra.ruta_id')
            ->join('agencias as a', 'a.id', '=', 'ra.agencia_id')
            ->whereIn('ra.agencia_id', $agenciasSeleccionadas)
            ->where('ra.ruta_id', '!=', $ruta->id)
            ->select(
                'ra.agencia_id',
                'a.terminal',
                'r.nombre_ruta'
            )
            ->get();

        $confirmarReasignacion = (bool) ($validated['confirmar_reasignacion'] ?? false);

        if ($conflictos->isNotEmpty() && !$confirmarReasignacion) {
            return redirect()->route('ruta.index')
                ->with('error', 'Algunas agencias ya están asignadas a otra ruta. Confirma la reasignación para moverlas.');
        }

        if ($conflictos->isNotEmpty() && $confirmarReasignacion) {
            DB::table('ruta_agencia')
                ->whereIn('agencia_id', $conflictos->pluck('agencia_id')->unique()->values())
                ->where('ruta_id', '!=', $ruta->id)
                ->delete();
        }

        $ruta->agencias()->sync($agenciasSeleccionadas->all());

        return redirect()->route('ruta.index')
            ->with('success', 'Agencias asignadas correctamente.');
    }

    public function detalle(Ruta $ruta)
    {
        $ruta->loadMissing('operadorAsignado:id,nombre,apellido,correo');

        $operador = $ruta->operadorAsignado;
        $nombreOperador = trim((($operador->nombre ?? '') . ' ' . ($operador->apellido ?? '')));

        return response()->json([
            'ruta_id' => (int) $ruta->id,
            'empresa' => (string) ($ruta->empresa ?? ''),
            'operador_id' => (string) ($ruta->operador_ruta_id ?? ''),
            'operador_nombre' => $nombreOperador,
            'operador_correo' => (string) ($operador->correo ?? ''),
        ]);
    }

    private function getOpcionesEmpresa(): array
    {
        return ['Negosur', 'BSH Support'];
    }
}
