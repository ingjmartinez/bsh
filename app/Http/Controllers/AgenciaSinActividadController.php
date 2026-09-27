<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgenciaSinActividadReportRequest;
use App\Http\Requests\GuardarConfiguracionAgenciaSinActividadRequest;
use App\Http\Requests\GuardarMotivoAgenciaSinActividadRequest;
use App\Models\AgenciaSinActividadConfiguracion;
use App\Models\AgenciaSinActividadMotivo;
use App\Services\AgenciaSinActividadReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgenciaSinActividadController extends Controller
{
    public function __construct(private readonly AgenciaSinActividadReportService $reportService) {}

    public function index(): View
    {
        return view('recursos_humanos.agencias-sin-actividad');
    }

    public function data(AgenciaSinActividadReportRequest $request): JsonResponse
    {
        return response()->json($this->reportService->report($request->validated()));
    }

    public function options(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sistema' => ['required', Rule::in(['lotobet', 'lotedom', 'delta', 'ds_virtual'])],
        ]);

        return response()->json($this->reportService->filterOptions($validated['sistema']));
    }

    public function storeReason(GuardarMotivoAgenciaSinActividadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $reason = AgenciaSinActividadMotivo::query()->updateOrCreate(
            ['sistema' => $validated['sistema'], 'terminal' => $validated['terminal']],
            [
                'motivo' => $validated['motivo'],
                'observacion' => $validated['observacion'] ?? null,
                'registrado_por' => $request->user()?->id,
            ]
        );

        return response()->json([
            'message' => 'Motivo guardado correctamente.',
            'motivo' => $reason,
        ]);
    }

    public function configuration(): JsonResponse
    {
        $configuration = AgenciaSinActividadConfiguracion::query()->first();

        return response()->json([
            'porcentaje_minimo' => (float) ($configuration?->porcentaje_minimo ?? 90),
        ]);
    }

    public function updateConfiguration(GuardarConfiguracionAgenciaSinActividadRequest $request): JsonResponse
    {
        $configuration = AgenciaSinActividadConfiguracion::query()->firstOrNew();
        $configuration->fill([
            'porcentaje_minimo' => $request->validated('porcentaje_minimo'),
            'actualizado_por' => $request->user()?->id,
        ])->save();

        return response()->json([
            'message' => 'Configuración guardada correctamente.',
            'porcentaje_minimo' => (float) $configuration->porcentaje_minimo,
        ]);
    }
}
