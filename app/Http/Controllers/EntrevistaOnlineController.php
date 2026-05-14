<?php

namespace App\Http\Controllers;

use App\Models\EntrevistaOnline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EntrevistaOnlineController extends Controller
{
    public function index()
    {
        return view('entrevista-online.index');
    }

    public function list()
    {
        $entrevistas = EntrevistaOnline::orderByDesc('fecha_llamada')
            ->orderByDesc('id')
            ->get();

        return response()->json($entrevistas);
    }

    public function show($id)
    {
        $entrevista = EntrevistaOnline::findOrFail($id);

        return response()->json($entrevista);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['user_id'] = Auth::id();

        $entrevista = EntrevistaOnline::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Entrevista guardada correctamente.',
            'entrevista' => $entrevista,
        ]);
    }

    public function update(Request $request, $id)
    {
        $entrevista = EntrevistaOnline::findOrFail($id);
        $data = $this->validar($request);

        $entrevista->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Entrevista actualizada correctamente.',
            'entrevista' => $entrevista,
        ]);
    }

    public function destroy($id)
    {
        $entrevista = EntrevistaOnline::findOrFail($id);
        $entrevista->delete();

        return response()->json([
            'success' => true,
            'message' => 'Entrevista eliminada correctamente.',
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre_completo'         => ['required', 'string', 'max:255'],
            'edad'                    => ['nullable', 'integer', 'min:0', 'max:120'],
            'telefono'                => ['nullable', 'string', 'max:50'],
            'direccion'               => ['nullable', 'string', 'max:500'],
            'estado_civil'            => ['nullable', 'string', 'max:50'],
            'hijos'                   => ['nullable', 'integer', 'min:0', 'max:50'],
            'estudia_actualmente'     => ['nullable', 'string', 'max:255'],
            'licencia_vehiculo'       => ['nullable', 'string', 'max:255'],
            'laborando_actualmente'   => ['nullable', 'string', 'max:255'],
            'ultimo_empleo_posicion'  => ['nullable', 'string', 'max:255'],
            'tiempo'                  => ['nullable', 'string', 'max:100'],
            'salario'                 => ['nullable', 'numeric', 'min:0'],
            'fecha_salida_motivo'     => ['nullable', 'string', 'max:500'],
            'comentarios'             => ['nullable', 'string'],
            'fecha_llamada'           => ['nullable', 'date'],
            'entrevistado_por'        => ['nullable', 'string', 'max:150'],
            'vacante_aplica'          => ['nullable', 'string', 'max:255'],
            'experiencia_demostrable' => ['nullable', 'string'],
            'conoce_del_area'         => ['nullable', 'string'],
            'fortalezas'              => ['nullable', 'string'],
            'debilidades'             => ['nullable', 'string'],
            'manejo_excel'            => ['nullable', 'integer', Rule::in(range(1, 10))],
        ]);
    }
}
