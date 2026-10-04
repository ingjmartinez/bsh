<?php

namespace App\Http\Controllers;

use App\Http\Requests\PremiosPagadosProductoRequest;
use App\Models\Agencia;
use App\Services\PremiosPagadosProductoService;
use Illuminate\View\View;

class PremiosPagadosProductoController extends Controller
{
    public function index(PremiosPagadosProductoRequest $request, PremiosPagadosProductoService $service): View
    {
        $fechaInicio = $request->validated('fecha_inicio') ?? now()->startOfMonth()->toDateString();
        $fechaFin = $request->validated('fecha_fin') ?? now()->toDateString();
        $grupo = $request->validated('grupo') ?: null;
        $vista = $request->validated('vista') ?? 'consolidado';
        $categoria = $request->validated('categoria') ?? 'todos';

        return view('reportes.premios-pagados-productos', [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'grupoSeleccionado' => $grupo,
            'vistaSeleccionada' => $vista,
            'categoriaSeleccionada' => $categoria,
            'gruposAgencia' => Agencia::query()->whereNotNull('grupo')->where('grupo', '<>', '')
                ->distinct()->orderBy('grupo')->pluck('grupo'),
            'reporte' => $service->summary($fechaInicio, $fechaFin, $grupo, $vista, $categoria),
        ]);
    }
}
