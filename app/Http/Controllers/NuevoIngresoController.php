<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarConfiguracionNuevoIngresoRequest;
use App\Models\Empleado;
use App\Models\NuevoIngresoConfiguracion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NuevoIngresoController extends Controller
{
    private const BUSINESS_DAY_LIMIT = 20;

    private const CANDIDATE_CALENDAR_DAYS = 35;

    public function index(): View
    {
        return view('recursos_humanos.nuevos-ingresos');
    }

    public function data(): JsonResponse
    {
        $today = today();
        $businessDayLimit = $this->businessDayLimit();
        $employees = Empleado::query()
            ->where('estatus', 1)
            ->whereNotNull('fecha_ingreso')
            ->whereDate('fecha_ingreso', '>=', $today->copy()->subDays(self::CANDIDATE_CALENDAR_DAYS))
            ->whereDate('fecha_ingreso', '<=', $today)
            ->get(['id', 'cedula', 'nombres', 'apellidos', 'fecha_ingreso'])
            ->map(function (Empleado $employee) use ($today): array {
                $entryDate = Carbon::parse($employee->fecha_ingreso)->startOfDay();

                return [
                    'id' => $employee->id,
                    'cedula' => $employee->cedula,
                    'nombre' => trim($employee->nombres.' '.$employee->apellidos),
                    'fecha_ingreso' => $entryDate->toDateString(),
                    'dias_habiles' => (int) $entryDate->diffInWeekdays($today),
                ];
            })
            ->filter(fn (array $employee): bool => $employee['dias_habiles'] < $businessDayLimit)
            ->sortBy('dias_habiles')
            ->values();

        return response()->json([
            'limite_dias_habiles' => $businessDayLimit,
            'total' => $employees->count(),
            'empleados' => $employees,
        ]);
    }

    public function configuration(): JsonResponse
    {
        return response()->json(['dias_habiles' => $this->businessDayLimit()]);
    }

    public function updateConfiguration(GuardarConfiguracionNuevoIngresoRequest $request): JsonResponse
    {
        $configuration = NuevoIngresoConfiguracion::query()->firstOrNew();
        $configuration->fill([
            'dias_habiles' => $request->validated('dias_habiles'),
            'actualizado_por' => $request->user()?->id,
        ])->save();

        return response()->json([
            'message' => 'Configuración guardada correctamente.',
            'dias_habiles' => (int) $configuration->dias_habiles,
        ]);
    }

    private function businessDayLimit(): int
    {
        return (int) (NuevoIngresoConfiguracion::query()->value('dias_habiles') ?? self::BUSINESS_DAY_LIMIT);
    }
}
