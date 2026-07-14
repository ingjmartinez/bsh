<?php

namespace App\Http\Controllers;

use App\Models\CoordinadorOperador;
use App\Models\ServicioGeneralRutaInspeccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ServicioGeneralRutaInspeccionController extends Controller
{
    private const ESTADOS = [
        'asignada' => 'Asignada',
        'en_camino' => 'En camino',
        'en_inspeccion' => 'En inspección',
        'pendiente_solucion' => 'Pendiente de solución',
        'solicitud_cierre' => 'Solicitud de cierre',
        'cerrada' => 'Cerrada',
        'cancelada' => 'Cancelada',
    ];

    private const PRIORIDADES = [
        'baja' => 'Baja',
        'media' => 'Media',
        'alta' => 'Alta',
        'critica' => 'Crítica',
    ];

    public function index(Request $request): View
    {
        $setupPending = !$this->tablaLista();

        if ($setupPending) {
            return view('servicios-generales.ruta-inspeccion', [
                'trabajos' => collect(),
                'agenciasAsignadas' => collect(),
                'responsablesCarga' => collect(),
                'stats' => $this->statsVacias(),
                'estados' => self::ESTADOS,
                'prioridades' => self::PRIORIDADES,
                'setupPending' => true,
                'agenciasSinResponsable' => 0,
            ]);
        }

        $fecha = $request->input('fecha', now()->toDateString());
        $visitasDia = ServicioGeneralRutaInspeccion::with('agencia')
            ->where('tipo', 'inspeccion')->where('generado_automaticamente', true)
            ->whereDate('fecha', $fecha)->get();
        $visitasPorResponsable = $visitasDia->groupBy('coordinador_operador_id');
        $buscar = mb_strtolower(trim((string) $request->input('buscar')));

        $resumenUsuarios = CoordinadorOperador::with('agencias')->orderBy('nombre')->get()
            ->map(function (CoordinadorOperador $persona) use ($visitasPorResponsable) {
                $agencias = $persona->agencias->unique('id')->values();
                $visitas = $visitasPorResponsable->get($persona->id, collect())->keyBy('agencia_id');
                $detalle = $agencias->map(function ($agencia) use ($visitas) {
                    $visita = $visitas->get($agencia->id);
                    return (object) [
                        'agencia' => $agencia,
                        'visita' => $visita,
                        'visitada' => $visita && ($visita->check_out_at || in_array($visita->estado, ['solicitud_cierre', 'cerrada', 'pendiente_solucion'], true)),
                    ];
                });

                return (object) [
                    'id' => $persona->id,
                    'nombre' => trim($persona->nombre . ' ' . $persona->apellido),
                    'cargo' => $persona->cargo,
                    'total' => $agencias->count(),
                    'visitadas' => $detalle->where('visitada', true)->count(),
                    'pendientes' => $detalle->where('visitada', false)->count(),
                    'detalle' => $detalle,
                ];
            })
            ->filter(fn($persona) => $persona->total > 0)
            ->when($buscar !== '', fn($items) => $items->filter(fn($persona) => str_contains(mb_strtolower($persona->nombre . ' ' . $persona->cargo), $buscar)))
            ->values();

        return view('servicios-generales.ruta-inspeccion', [
            'fecha' => $fecha,
            'resumenUsuarios' => $resumenUsuarios,
            'statsRuta' => [
                'usuarios' => $resumenUsuarios->count(),
                'agencias' => $resumenUsuarios->sum('total'),
                'visitadas' => $resumenUsuarios->sum('visitadas'),
                'pendientes' => $resumenUsuarios->sum('pendientes'),
            ],
            'setupPending' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->tablaLista(), 503, 'La estructura de rutas de inspección no está instalada.');

        $validated = $request->validate([
            'agencia_id' => ['required', 'integer', 'exists:agencias,id'],
            'tipo' => ['required', 'in:inspeccion,averia'],
            'fecha' => ['required', 'date'],
            'prioridad' => ['required', 'in:' . implode(',', array_keys(self::PRIORIDADES))],
            'descripcion' => ['required', 'string', 'max:5000'],
            'evidencia' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $responsable = $this->responsableDeAgencia((int) $validated['agencia_id']);

        if ($responsable === null) {
            return back()->withInput()->with('error', 'La agencia no tiene un responsable único. Asígnala primero desde Coordinador.');
        }

        $agencia = DB::table('agencias')->where('id', $validated['agencia_id'])->first();
        $terminal = trim((string) ($agencia->terminal ?? $agencia->codigo ?? $agencia->agencia ?? $validated['agencia_id']));
        $evidenciaPath = $this->guardarEvidencia($request);

        DB::transaction(function () use ($validated, $responsable, $terminal, $evidenciaPath) {
            $trabajo = ServicioGeneralRutaInspeccion::create([
                'user_id' => auth()->id(),
                'agencia_id' => $validated['agencia_id'],
                'coordinador_operador_id' => $responsable->id,
                'responsable_nombre' => $responsable->nombre,
                'tipo' => $validated['tipo'],
                'nombre' => ($validated['tipo'] === 'averia' ? 'Avería' : 'Inspección') . ' - ' . $terminal,
                'fecha' => $validated['fecha'],
                'estado' => 'asignada',
                'prioridad' => $validated['prioridad'],
                'descripcion' => $validated['descripcion'],
                'evidencia_path' => $evidenciaPath,
                'metadata' => ['asignacion' => 'automatica_por_agencia'],
            ]);

            $this->registrarHistorial($trabajo, 'creada', null, 'asignada', 'Responsable asignado automáticamente según la agencia.', [
                'tipo' => $validated['tipo'],
                'prioridad' => $validated['prioridad'],
            ]);
        });

        return redirect()->route('servicios-generales.ruta-inspeccion.index')
            ->with('success', 'Trabajo creado y asignado automáticamente a ' . $responsable->nombre . '.');
    }

    public function update(Request $request, ServicioGeneralRutaInspeccion $rutaInspeccion): RedirectResponse
    {
        if (in_array($rutaInspeccion->estado, ['cerrada', 'cancelada'], true)) {
            return back()->with('error', 'Un trabajo cerrado o cancelado no puede modificarse.');
        }

        $validated = $request->validate([
            'estado' => ['required', 'in:' . implode(',', array_keys(self::ESTADOS))],
            'prioridad' => ['required', 'in:' . implode(',', array_keys(self::PRIORIDADES))],
            'descripcion' => ['required', 'string', 'max:5000'],
            'observacion' => ['nullable', 'string', 'max:5000', 'required_if:estado,cancelada'],
            'evidencia' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        if ($validated['estado'] === 'cerrada') {
            return back()->with('error', 'Utiliza la acción Aprobar cierre para cerrar el trabajo.');
        }

        $estadoAnterior = $rutaInspeccion->estado;
        $cambios = [];

        foreach (['estado', 'prioridad', 'descripcion'] as $campo) {
            if ((string) $rutaInspeccion->{$campo} !== (string) $validated[$campo]) {
                $cambios[$campo] = ['anterior' => $rutaInspeccion->{$campo}, 'nuevo' => $validated[$campo]];
            }
        }

        $evidenciaPath = $this->guardarEvidencia($request);
        if ($evidenciaPath !== null) {
            $cambios['evidencia'] = ['nuevo' => $evidenciaPath];
        }

        DB::transaction(function () use ($rutaInspeccion, $validated, $estadoAnterior, $cambios, $evidenciaPath) {
            $rutaInspeccion->fill([
                'estado' => $validated['estado'],
                'prioridad' => $validated['prioridad'],
                'descripcion' => $validated['descripcion'],
            ]);

            if ($evidenciaPath !== null) {
                $rutaInspeccion->evidencia_path = $evidenciaPath;
            }
            if ($estadoAnterior === 'asignada' && $validated['estado'] !== 'asignada') {
                $rutaInspeccion->iniciado_at = now();
            }
            if ($validated['estado'] === 'solicitud_cierre') {
                $rutaInspeccion->cierre_solicitado_at = now();
            } elseif ($estadoAnterior === 'solicitud_cierre') {
                $rutaInspeccion->cierre_solicitado_at = null;
            }

            $rutaInspeccion->save();

            $this->registrarHistorial(
                $rutaInspeccion,
                $validated['estado'] === 'cancelada' ? 'cancelada' : 'actualizada',
                $estadoAnterior,
                $validated['estado'],
                $validated['observacion'] ?? null,
                $cambios
            );
        });

        return back()->with('success', 'Trabajo actualizado. El cambio quedó registrado en el historial.');
    }

    public function cerrar(Request $request, ServicioGeneralRutaInspeccion $rutaInspeccion): RedirectResponse
    {
        if ($rutaInspeccion->estado !== 'solicitud_cierre') {
            return back()->with('error', 'El responsable debe solicitar el cierre antes de aprobarlo.');
        }

        $validated = $request->validate([
            'detalle_solucion' => ['required', 'string', 'max:5000'],
            'observacion_cierre' => ['nullable', 'string', 'max:5000'],
            'evidencia' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $evidenciaPath = $this->guardarEvidencia($request);

        DB::transaction(function () use ($rutaInspeccion, $validated, $evidenciaPath) {
            $estadoAnterior = $rutaInspeccion->estado;
            $rutaInspeccion->estado = 'cerrada';
            $rutaInspeccion->detalle_solucion = $validated['detalle_solucion'];
            $rutaInspeccion->cerrado_at = now();
            $rutaInspeccion->cerrado_por = auth()->id();
            if ($evidenciaPath !== null) {
                $rutaInspeccion->evidencia_path = $evidenciaPath;
            }
            $rutaInspeccion->save();

            $this->registrarHistorial(
                $rutaInspeccion,
                'cierre_aprobado',
                $estadoAnterior,
                'cerrada',
                $validated['observacion_cierre'] ?? null,
                ['detalle_solucion' => $validated['detalle_solucion'], 'evidencia' => $evidenciaPath]
            );
        });

        return back()->with('success', 'Cierre aprobado y registrado correctamente.');
    }

    private function responsableDeAgencia(int $agenciaId): ?object
    {
        $tabla = CoordinadorOperador::resolveTableName();
        $nombreSql = $this->nombreResponsableSql('co');
        $responsables = DB::table('coordinador_operador_agencia as coa')
            ->join($tabla . ' as co', 'co.id', '=', 'coa.coordinador_operador_id')
            ->where('coa.agencia_id', $agenciaId)
            ->selectRaw("co.id, {$nombreSql} as nombre")
            ->get();

        return $responsables->count() === 1 ? $responsables->first() : null;
    }

    private function agenciasConResponsableUnico()
    {
        $tabla = CoordinadorOperador::resolveTableName();
        $codigo = Schema::hasColumn('agencias', 'codigo') ? 'codigo' : 'agencia';
        $nombre = Schema::hasColumn('agencias', 'nombre') ? 'nombre' : 'nombre_agencia';
        $nombreResponsable = $this->nombreResponsableSql('co');

        return DB::table('agencias as a')
            ->join('coordinador_operador_agencia as coa', 'coa.agencia_id', '=', 'a.id')
            ->join($tabla . ' as co', 'co.id', '=', 'coa.coordinador_operador_id')
            ->whereRaw('(SELECT COUNT(*) FROM coordinador_operador_agencia x WHERE x.agencia_id = a.id) = 1')
            ->selectRaw("a.id, a.terminal, a.{$codigo} as codigo, a.{$nombre} as nombre, co.id as responsable_id, {$nombreResponsable} as responsable_nombre")
            ->orderBy('a.terminal')
            ->get();
    }

    private function responsablesConCarga()
    {
        $tabla = CoordinadorOperador::resolveTableName();
        $nombre = $this->nombreResponsableSql('co');

        return DB::table($tabla . ' as co')
            ->selectRaw("co.id, {$nombre} as nombre, co.cargo")
            ->selectRaw('(SELECT COUNT(*) FROM coordinador_operador_agencia coa WHERE coa.coordinador_operador_id = co.id) as agencias_count')
            ->selectRaw("(SELECT COUNT(*) FROM servicios_generales_rutas_inspeccion sri WHERE sri.coordinador_operador_id = co.id AND sri.estado NOT IN ('cerrada', 'cancelada')) as trabajos_abiertos")
            ->orderByDesc('agencias_count')
            ->orderBy('nombre')
            ->get();
    }

    private function stats(): array
    {
        $base = ServicioGeneralRutaInspeccion::query();

        return [
            'total' => (clone $base)->count(),
            'abiertas' => (clone $base)->whereNotIn('estado', ['cerrada', 'cancelada'])->count(),
            'averias' => (clone $base)->where('tipo', 'averia')->whereNotIn('estado', ['cerrada', 'cancelada'])->count(),
            'cierre' => (clone $base)->where('estado', 'solicitud_cierre')->count(),
            'cerradas' => (clone $base)->where('estado', 'cerrada')->count(),
        ];
    }

    private function statsVacias(): array
    {
        return ['total' => 0, 'abiertas' => 0, 'averias' => 0, 'cierre' => 0, 'cerradas' => 0];
    }

    private function agenciasSinResponsable(): int
    {
        return DB::table('agencias as a')
            ->leftJoin('coordinador_operador_agencia as coa', 'coa.agencia_id', '=', 'a.id')
            ->whereNull('coa.agencia_id')
            ->count('a.id');
    }

    private function registrarHistorial(
        ServicioGeneralRutaInspeccion $trabajo,
        string $accion,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?string $observacion,
        array $cambios = []
    ): void {
        $trabajo->historial()->create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'responsable_id' => $trabajo->coordinador_operador_id,
            'responsable_nombre' => $trabajo->responsable_nombre,
            'observacion' => $observacion,
            'cambios' => $cambios ?: null,
        ]);
    }

    private function guardarEvidencia(Request $request): ?string
    {
        return $request->hasFile('evidencia')
            ? $request->file('evidencia')->store('rutas-inspeccion', 'public')
            : null;
    }

    private function nombreResponsableSql(string $alias): string
    {
        if (DB::connection()->getDriverName() === 'sqlite' && CoordinadorOperador::hasResolvedColumn('apellido')) {
            return "TRIM(COALESCE({$alias}.nombre, '') || ' ' || COALESCE({$alias}.apellido, ''))";
        }

        return CoordinadorOperador::hasResolvedColumn('apellido')
            ? "TRIM(CONCAT(COALESCE({$alias}.nombre, ''), ' ', COALESCE({$alias}.apellido, '')))"
            : "TRIM(COALESCE({$alias}.nombre, ''))";
    }

    private function tablaLista(): bool
    {
        return Schema::hasTable('servicios_generales_rutas_inspeccion')
            && Schema::hasTable('servicios_generales_rutas_inspeccion_historial')
            && Schema::hasColumn('servicios_generales_rutas_inspeccion', 'agencia_id');
    }
}
