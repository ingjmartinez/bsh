<?php

namespace App\Http\Controllers;

use App\Models\IncentivoAdministrativo;
use App\Models\PorcentajeIncentivo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncentivoConfiguracionController extends Controller
{
    public function incentivoAdministrativoIndex()
    {
        $registros = IncentivoAdministrativo::query()
            ->orderBy('grupo')
            ->orderBy('empresa')
            ->orderBy('nombre')
            ->paginate(30);

        $posiciones = PorcentajeIncentivo::query()
            ->orderBy('posicion')
            ->get(['posicion', 'bono_pct']);

        return view('incentivos.incentivo_administrativo.index', compact('registros', 'posiciones'));
    }

    public function incentivoAdministrativoStore(Request $request)
    {
        $validated = $request->validate([
            'grupo' => ['required', 'string', 'max:70', Rule::exists('porcentaje_incentivos', 'posicion')],
            'nombre' => ['required', 'string', 'max:120'],
            'empresa' => ['required', 'string', 'max:50'],
            'pct_total' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        IncentivoAdministrativo::create($validated);

        return redirect()
            ->route('incentivos.incentivo-administrativo.index')
            ->with('success', 'Registro creado correctamente.');
    }

    public function incentivoAdministrativoUpdate(Request $request, IncentivoAdministrativo $incentivoAdministrativo)
    {
        $validated = $request->validate([
            'grupo' => ['required', 'string', 'max:70', Rule::exists('porcentaje_incentivos', 'posicion')],
            'nombre' => ['required', 'string', 'max:120'],
            'empresa' => ['required', 'string', 'max:50'],
            'pct_total' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $incentivoAdministrativo->update($validated);

        return redirect()
            ->route('incentivos.incentivo-administrativo.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function incentivoAdministrativoDestroy(IncentivoAdministrativo $incentivoAdministrativo)
    {
        $incentivoAdministrativo->delete();

        return redirect()
            ->route('incentivos.incentivo-administrativo.index')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function porcentajeIncentivoIndex()
    {
        $registros = PorcentajeIncentivo::query()
            ->orderBy('posicion')
            ->paginate(30);

        return view('incentivos.porcentaje_incentivo.index', compact('registros'));
    }

    public function porcentajeIncentivoStore(Request $request)
    {
        $validated = $request->validate([
            'posicion' => [
                'required',
                'string',
                'max:70',
                Rule::unique('porcentaje_incentivos', 'posicion'),
            ],
            'bono_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        PorcentajeIncentivo::create($validated);

        return redirect()
            ->route('incentivos.porcentaje-incentivo.index')
            ->with('success', 'Registro creado correctamente.');
    }

    public function porcentajeIncentivoUpdate(Request $request, PorcentajeIncentivo $porcentajeIncentivo)
    {
        $validated = $request->validate([
            'posicion' => [
                'required',
                'string',
                'max:70',
                Rule::unique('porcentaje_incentivos', 'posicion')
                    ->ignore($porcentajeIncentivo->id),
            ],
            'bono_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        $porcentajeIncentivo->update($validated);

        return redirect()
            ->route('incentivos.porcentaje-incentivo.index')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function porcentajeIncentivoDestroy(PorcentajeIncentivo $porcentajeIncentivo)
    {
        $porcentajeIncentivo->delete();

        return redirect()
            ->route('incentivos.porcentaje-incentivo.index')
            ->with('success', 'Registro eliminado correctamente.');
    }
}
