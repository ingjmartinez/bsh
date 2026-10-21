<?php

namespace App\Http\Controllers;

use App\Http\Requests\MonitoreoVentasAsignacionRequest;
use App\Http\Requests\MonitoreoVentasIndexRequest;
use App\Http\Requests\MonitoreoVentasSeguimientoRequest;
use App\Models\Agencia;
use App\Models\MonitoreoVentaAsignacion;
use App\Models\MonitoreoVentaCaso;
use App\Models\User;
use App\Services\MonitoreoVentasCmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServicioGeneralMonitoreoVentasController extends Controller
{
    public function index(MonitoreoVentasIndexRequest $request): View
    {
        $puedeAdministrar = $request->user()->can('servicios_generales.manage');
        $casos = MonitoreoVentaCaso::query()->with(['agencia:id,terminal,nombre_agencia,nombre', 'asesor:id,name', 'seguimientos.usuario:id,name'])
            ->when(! $puedeAdministrar, fn ($query) => $query->where('asesor_id', $request->user()->id))
            ->when($request->filled('fecha'), fn ($query) => $query->whereDate('fecha', $request->input('fecha')))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->input('estado')))
            ->latest('fecha')->latest('id')->paginate(25)->withQueryString();
        $reincidencias = MonitoreoVentaCaso::query()->whereIn('agencia_id', $casos->getCollection()->pluck('agencia_id'))
            ->selectRaw('agencia_id, COUNT(*) AS total')->groupBy('agencia_id')->pluck('total', 'agencia_id');
        $fechaRutas = $request->input('fecha') ?: MonitoreoVentaCaso::query()->max('fecha');
        $rutasCriticas = $puedeAdministrar && $fechaRutas
            ? app(MonitoreoVentasCmsService::class)->rutasCriticas(Carbon::parse($fechaRutas))
            : collect();

        return view('servicios-generales.monitoreo-ventas', [
            'casos' => $casos,
            'puedeAdministrar' => $puedeAdministrar,
            'umbrales' => MonitoreoVentasCmsService::UMBRALES,
            'reincidencias' => $reincidencias,
            'rutasCriticas' => $rutasCriticas,
            'fechaRutas' => $fechaRutas,
            'asignaciones' => $puedeAdministrar ? MonitoreoVentaAsignacion::query()->with('asesor:id,name')->orderBy('central')->orderBy('grupo')->get() : collect(),
            'asesores' => $puedeAdministrar ? User::query()->orderBy('name')->get(['id', 'name']) : collect(),
            'centrales' => $puedeAdministrar ? Agencia::query()->where('estatus', 1)->whereNotNull('central')->distinct()->orderBy('central')->pluck('central') : collect(),
            'grupos' => $puedeAdministrar ? Agencia::query()->where('estatus', 1)->whereNotNull('grupo')->distinct()->orderBy('grupo')->pluck('grupo') : collect(),
        ]);
    }

    public function asignar(MonitoreoVentasAsignacionRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $existeCombinacion = Agencia::query()->where('estatus', 1)->where('central', $datos['central'])->where('grupo', $datos['grupo'])->exists();
        if (! $existeCombinacion) {
            return back()->withErrors(['grupo' => 'El grupo no pertenece a la central seleccionada.']);
        }

        DB::transaction(function () use ($datos): void {
            MonitoreoVentaAsignacion::query()->updateOrCreate(
                ['central' => $datos['central'], 'grupo' => $datos['grupo']],
                ['asesor_id' => $datos['asesor_id']]
            );
            MonitoreoVentaCaso::query()->where('central', $datos['central'])->where('grupo', $datos['grupo'])
                ->where('estado', '<>', 'cerrado')
                ->where(function ($query) use ($datos): void {
                    $query->where('asesor_id', '<>', $datos['asesor_id'])->orWhereNull('asesor_id');
                })
                ->each(function (MonitoreoVentaCaso $caso) use ($datos): void {
                    $anterior = $caso->asesor_id;
                    $caso->update(['asesor_id' => $datos['asesor_id']]);
                    $caso->seguimientos()->create([
                        'user_id' => auth()->id(),
                        'accion' => 'reasignada',
                        'detalle' => 'Asesor anterior: '.($anterior ?? 'sin asignar').". Nuevo asesor: {$datos['asesor_id']}.",
                    ]);
                });
        });

        return back()->with('success', 'Asignación CMS guardada.');
    }

    public function seguimiento(MonitoreoVentasSeguimientoRequest $request, MonitoreoVentaCaso $caso): RedirectResponse
    {
        if ($caso->estado === 'cerrado') {
            return back()->withErrors(['caso' => 'El caso ya está cerrado.']);
        }

        $datos = $request->validated();
        $cerrar = (bool) ($datos['cerrar'] ?? false);
        if ($cerrar && (int) $caso->asesor_id !== (int) $request->user()->id) {
            abort(403, 'El asesor asignado debe cerrar el caso.');
        }

        DB::transaction(function () use ($caso, $datos, $cerrar, $request): void {
            $caso->update([
                'causa' => $datos['causa'],
                'gestion' => $datos['gestion'],
                'resultado' => $datos['resultado'],
                'estado' => $cerrar ? 'cerrado' : 'en_seguimiento',
                'cerrado_por' => $cerrar ? $request->user()->id : null,
                'cerrado_at' => $cerrar ? now() : null,
            ]);
            $caso->seguimientos()->create([
                'user_id' => $request->user()->id,
                'accion' => $cerrar ? 'cerrado' : 'seguimiento',
                'detalle' => "Causa: {$datos['causa']}\nGestión: {$datos['gestion']}\nResultado: {$datos['resultado']}",
            ]);
        });

        return back()->with('success', $cerrar ? 'Caso cerrado manualmente.' : 'Seguimiento registrado.');
    }
}
