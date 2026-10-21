<?php

namespace App\Http\Controllers;

use App\Http\Requests\FiltrarNuevoIngresoRequest;
use App\Http\Requests\GuardarConfiguracionNuevoIngresoRequest;
use App\Models\Agencia;
use App\Models\Empleado;
use App\Models\NuevoIngresoConfiguracion;
use App\Models\VtUsuarioBet;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class NuevoIngresoController extends Controller
{
    private const BUSINESS_DAY_LIMIT = 20;

    private const CANDIDATE_CALENDAR_DAYS = 35;

    public function index(): View
    {
        $agencias = Agencia::query()->where('estatus', 1);

        return view('recursos_humanos.nuevos-ingresos', [
            'grupos' => (clone $agencias)->whereNotNull('grupo')->where('grupo', '<>', '')->distinct()->orderBy('grupo')->pluck('grupo'),
            'centrales' => (clone $agencias)->whereNotNull('central')->where('central', '<>', '')->distinct()->orderBy('central')->pluck('central'),
            'gerentes' => (clone $agencias)->whereNotNull('gerente_de_servicio')->where('gerente_de_servicio', '<>', '')->distinct()->orderBy('gerente_de_servicio')->pluck('gerente_de_servicio'),
        ]);
    }

    public function data(FiltrarNuevoIngresoRequest $request): JsonResponse
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

        $filters = array_filter($request->safe()->only(['grupo', 'central', 'gerente']), fn ($value): bool => $value !== null && $value !== '');
        if ($filters !== []) {
            $employees = $this->filterByAgency($employees, $filters, $today);
        }

        return response()->json([
            'limite_dias_habiles' => $businessDayLimit,
            'total' => $employees->count(),
            'empleados' => $employees,
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $employees
     * @param  array<string, string>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function filterByAgency(Collection $employees, array $filters, Carbon $today): Collection
    {
        if ($employees->isEmpty()) {
            return $employees;
        }

        $sales = VtUsuarioBet::query()
            ->whereIn('cedula', $employees->pluck('cedula'))
            ->whereDate('fecha', '>=', $today->copy()->subDays(self::CANDIDATE_CALENDAR_DAYS))
            ->selectRaw('cedula, agencia_id, MAX(fecha) AS ultima_fecha, MAX(id) AS ultima_venta_id')
            ->groupBy('cedula', 'agencia_id')
            ->get()
            ->sortBy([['ultima_fecha', 'desc'], ['ultima_venta_id', 'desc']])
            ->groupBy('cedula')
            ->map(fn (Collection $rows) => $rows->first());

        $agencies = Agencia::query()->where('estatus', 1)
            ->whereNotNull('terminal')
            ->get(['terminal', 'grupo', 'central', 'gerente_de_servicio'])
            ->keyBy(fn (Agencia $agency): string => $this->terminalKey((string) $agency->terminal));

        return $employees->filter(function (array $employee) use ($sales, $agencies, $filters): bool {
            $sale = $sales->get($employee['cedula']);
            $agency = $sale ? $agencies->get($this->terminalKey((string) $sale->agencia_id)) : null;
            if (! $agency) {
                return false;
            }

            foreach (['grupo' => 'grupo', 'central' => 'central', 'gerente' => 'gerente_de_servicio'] as $filter => $column) {
                if (isset($filters[$filter]) && $agency->{$column} !== $filters[$filter]) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    private function terminalKey(string $terminal): string
    {
        return ltrim(trim($terminal), '0') ?: '0';
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
