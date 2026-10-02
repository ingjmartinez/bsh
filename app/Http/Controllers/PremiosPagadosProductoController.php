<?php

namespace App\Http\Controllers;

use App\Http\Requests\PremiosPagadosProductoRequest;
use App\Services\PremiosPagadosProductoService;
use Illuminate\View\View;

class PremiosPagadosProductoController extends Controller
{
    public function index(PremiosPagadosProductoRequest $request, PremiosPagadosProductoService $service): View
    {
        $fechaInicio = $request->validated('fecha_inicio') ?? now()->startOfMonth()->toDateString();
        $fechaFin = $request->validated('fecha_fin') ?? now()->toDateString();

        return view('reportes.premios-pagados-productos', [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'reporte' => $service->report($fechaInicio, $fechaFin),
        ]);
    }
}
