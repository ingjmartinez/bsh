<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReporteBonoIncentivoRequest;
use App\Services\IncentivoBonusReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ReporteBonosController extends Controller
{
    public function __construct(
        private readonly IncentivoBonusReportService $bonusReportService
    ) {}

    public function index(): View
    {
        return view('incentivos.reporte-bonos');
    }

    public function datos(ReporteBonoIncentivoRequest $request): JsonResponse
    {
        return response()->json($this->bonusReportService->generate(
            $request->string('fecha_ini')->toString(),
            $request->string('fecha_fin')->toString(),
            $request->string('sistema', 'Todos')->toString()
        ));
    }
}
