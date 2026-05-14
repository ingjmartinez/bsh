<?php

namespace App\Http\Controllers;

use App\Models\ProcesoDepartamento;
use Illuminate\Http\Request;

class ProcesoController extends Controller
{
    private array $departamentos = [
        'gerencia'         => 'Gerencia',
        'contabilidad'     => 'Contabilidad',
        'recursos-humanos' => 'Recursos Humanos',
        'operaciones'      => 'Operaciones',
        'comercial'        => 'Comercial',
        'mantenimiento'    => 'Mantenimiento',
        'tecnologia'       => 'Tecnologia',
    ];

    public function index()
    {
        return redirect()->route('procesos.departamento', ['departamento' => 'gerencia']);
    }

    public function departamento(string $departamento)
    {
        abort_unless(isset($this->departamentos[$departamento]), 404);

        $procesosBaseEditados = ProcesoDepartamento::where('departamento', $departamento)
            ->where('es_personalizado', false)
            ->get()
            ->keyBy(function (ProcesoDepartamento $proceso) {
                return $proceso->proceso_base ?: $proceso->nombre;
            });

        $procesosCustom = ProcesoDepartamento::where('departamento', $departamento)
            ->where('es_personalizado', true)
            ->orderBy('id')
            ->get();

        return view('procesos.departamento', [
            'departamentoSlug'  => $departamento,
            'departamentoNombre' => $this->departamentos[$departamento],
            'departamentos'     => $this->departamentos,
            'procesosBaseEditados' => $procesosBaseEditados,
            'procesosCustom'    => $procesosCustom,
        ]);
    }

    public function guardarProtocolo(Request $request)
    {
        $data = $request->validate([
            'departamento' => 'required|string|max:50',
            'nombre'       => 'required|string|max:150',
            'proceso_base' => 'nullable|string|max:150',
            'protocolo'    => 'nullable|string|max:10000',
        ]);

        abort_unless(isset($this->departamentos[$data['departamento']]), 404);

        $procesoBase = trim((string) ($data['proceso_base'] ?? ''));
        $nombre = trim((string) $data['nombre']);

        if ($procesoBase !== '') {
            $registro = ProcesoDepartamento::where('departamento', $data['departamento'])
                ->where('es_personalizado', false)
                ->where(function ($query) use ($procesoBase) {
                    $query->where('proceso_base', $procesoBase)
                        ->orWhere(function ($subquery) use ($procesoBase) {
                            $subquery->whereNull('proceso_base')
                                ->where('nombre', $procesoBase);
                        });
                })
                ->first();

            if (!$registro) {
                $registro = new ProcesoDepartamento([
                    'departamento' => $data['departamento'],
                    'es_personalizado' => false,
                    'proceso_base' => $procesoBase,
                    'nombre' => $nombre,
                ]);
            }

            $registro->fill([
                'proceso_base' => $procesoBase,
                'nombre' => $registro->nombre ?: $nombre,
                'protocolo' => $data['protocolo'],
            ]);
            $registro->save();
        } else {
            ProcesoDepartamento::updateOrCreate(
                [
                    'departamento'    => $data['departamento'],
                    'nombre'          => $nombre,
                    'es_personalizado' => false,
                ],
                [
                    'protocolo' => $data['protocolo'],
                ]
            );
        }

        return response()->json(['ok' => true]);
    }

    public function actualizarProcesoBase(Request $request)
    {
        $data = $request->validate([
            'departamento' => 'required|string|max:50',
            'proceso_base' => 'required|string|max:150',
            'nombre'       => 'required|string|max:150',
            'descripcion'  => 'nullable|string|max:500',
            'icono'        => 'nullable|string|max:80',
            'protocolo'    => 'nullable|string|max:10000',
        ]);

        abort_unless(isset($this->departamentos[$data['departamento']]), 404);

        $proceso = ProcesoDepartamento::where('departamento', $data['departamento'])
            ->where('es_personalizado', false)
            ->where(function ($query) use ($data) {
                $query->where('proceso_base', $data['proceso_base'])
                    ->orWhere(function ($subquery) use ($data) {
                        $subquery->whereNull('proceso_base')
                            ->where('nombre', $data['proceso_base']);
                    });
            })
            ->first();

        if (!$proceso) {
            $proceso = new ProcesoDepartamento([
                'departamento' => $data['departamento'],
                'es_personalizado' => false,
                'proceso_base' => $data['proceso_base'],
            ]);
        }

        $proceso->fill([
            'proceso_base' => $data['proceso_base'],
            'nombre'       => $data['nombre'],
            'descripcion'  => $data['descripcion'] ?? '',
            'icono'        => $data['icono'] ?? 'ri-file-list-3-line',
            'protocolo'    => $data['protocolo'] ?? null,
        ]);
        $proceso->save();

        return response()->json(['ok' => true]);
    }

    public function crearProceso(Request $request)
    {
        $data = $request->validate([
            'departamento' => 'required|string|max:50',
            'nombre'       => 'required|string|max:150',
            'descripcion'  => 'nullable|string|max:500',
            'icono'        => 'nullable|string|max:80',
            'protocolo'    => 'nullable|string|max:10000',
        ]);

        abort_unless(isset($this->departamentos[$data['departamento']]), 404);

        $proceso = ProcesoDepartamento::create([
            'departamento'    => $data['departamento'],
            'nombre'          => $data['nombre'],
            'descripcion'     => $data['descripcion'] ?? '',
            'icono'           => $data['icono'] ?? 'ri-file-list-3-line',
            'protocolo'       => $data['protocolo'] ?? null,
            'es_personalizado' => true,
        ]);

        return response()->json(['ok' => true, 'id' => $proceso->id]);
    }

    public function actualizarProceso(Request $request, $id)
    {
        $data = $request->validate([
            'nombre'       => 'required|string|max:150',
            'descripcion'  => 'nullable|string|max:500',
            'icono'        => 'nullable|string|max:80',
            'protocolo'    => 'nullable|string|max:10000',
        ]);

        $proceso = ProcesoDepartamento::where('id', $id)
            ->where('es_personalizado', true)
            ->firstOrFail();

        $proceso->update([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? '',
            'icono'       => $data['icono'] ?? 'ri-file-list-3-line',
            'protocolo'   => $data['protocolo'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function eliminarProceso($id)
    {
        $proceso = ProcesoDepartamento::where('id', $id)
            ->where('es_personalizado', true)
            ->firstOrFail();

        $proceso->delete();

        return response()->json(['ok' => true]);
    }
}
