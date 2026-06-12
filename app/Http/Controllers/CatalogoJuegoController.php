<?php

namespace App\Http\Controllers;

use App\Models\CatalogoJuego;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CatalogoJuegoController extends Controller
{
    private const FUENTE_VENTAS = 'ventas_usuarios_bet';

    public function index()
    {
        $juegos = CatalogoJuego::query()
            ->orderBy('producto_id')
            ->get();

        return view('mantenimiento.catalogo_juegos.index', compact('juegos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => ['required', 'integer', 'not_in:0', 'unique:catalogo_juegos,producto_id'],
            'tipo' => ['required', 'string', 'max:50'],
            'descripcion' => ['required', 'string', 'max:255'],
        ]);

        CatalogoJuego::create(array_merge($validated, [
            'sistema' => 'ambos',
            'activo' => true,
        ]));

        return redirect()
            ->route('mantenimiento.catalogo-juegos.index')
            ->with('success', 'Juego registrado correctamente.');
    }

    public function update(Request $request, CatalogoJuego $catalogoJuego)
    {
        $validated = $request->validate([
            'producto_id' => [
                'required',
                'integer',
                'not_in:0',
                Rule::unique('catalogo_juegos', 'producto_id')->ignore($catalogoJuego->id),
            ],
            'tipo' => ['required', 'string', 'max:50'],
            'descripcion' => ['required', 'string', 'max:255'],
        ]);

        $catalogoJuego->update($validated);

        return redirect()
            ->route('mantenimiento.catalogo-juegos.index')
            ->with('success', 'Juego actualizado correctamente.');
    }

    public function destroy(CatalogoJuego $catalogoJuego)
    {
        $catalogoJuego->delete();

        return redirect()
            ->route('mantenimiento.catalogo-juegos.index')
            ->with('success', 'Juego eliminado correctamente.');
    }

    public function detectarNuevos(Request $request)
    {
        $validated = $request->validate([
            'todo' => ['nullable', 'boolean'],
            'anio' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $todo = (bool) ($validated['todo'] ?? false);
        $anio = (int) ($validated['anio'] ?? now()->year);
        $mes = (int) ($validated['mes'] ?? now()->month);
        $sugerencia = $this->obtenerUltimoPeriodoConDatos();

        if ($todo) {
            $periodoTexto = 'Todo el histórico';
            $data = $this->obtenerProductosNuevos(null, null, true)->values();
        } else {
            $fechaFiltro = Carbon::create($anio, $mes, 1);
            $periodoTexto = ucfirst($fechaFiltro->locale('es')->translatedFormat('F')) . ' ' . $anio;
            $data = $this->obtenerProductosNuevos($anio, $mes, false)->values();
        }

        $tiposCatalogo = CatalogoJuego::query()
            ->select('tipo')
            ->whereNotNull('tipo')
            ->whereRaw("TRIM(tipo) <> ''")
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo')
            ->map(fn($tipo) => trim((string) $tipo))
            ->filter()
            ->values();

        return response()->json([
            'data' => $data,
            'tipos_catalogo' => $tiposCatalogo,
            'periodo_texto' => $periodoTexto,
            'sugerencia_periodo_texto' => $sugerencia['texto'],
            'sugerencia_anio' => $sugerencia['anio'],
            'sugerencia_mes' => $sugerencia['mes'],
            'debug' => [
                'todo' => $todo,
                'anio' => $anio,
                'mes' => $mes,
                'nuevos_count' => $data->count(),
                'tabla_fuente' => self::FUENTE_VENTAS,
                'max_fecha_fuente' => DB::table(self::FUENTE_VENTAS)->max('fecha'),
            ],
        ]);
    }

    public function comparativoSql(Request $request)
    {
        $validated = $request->validate([
            'todo' => ['nullable', 'boolean'],
            'anio' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $todo = (bool) ($validated['todo'] ?? false);
        $anio = (int) ($validated['anio'] ?? now()->year);
        $mes = (int) ($validated['mes'] ?? now()->month);

        $fechaInicio = null;
        $fechaFin = null;
        $periodoTexto = 'Todo el histórico';

        if (!$todo) {
            $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->toDateString();
            $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->toDateString();
            $periodoTexto = ucfirst(Carbon::create($anio, $mes, 1)->locale('es')->translatedFormat('F')) . ' ' . $anio;
        }

        $nuevos = $this->obtenerProductosNuevos(
            $todo ? null : $anio,
            $todo ? null : $mes,
            $todo
        )->values();

        return response()->json([
            'data' => $nuevos->map(function ($item) {
                return [
                    'no_en_catalogo' => $item['producto_id'] ?? null,
                    'descripcion' => $item['descripcion'] ?? null,
                    'tipo' => $item['tipo_sugerido'] ?? null,
                ];
            })->values(),
            'periodo_texto' => $periodoTexto,
            'totales' => [
                'fuente' => DB::table(self::FUENTE_VENTAS)
                    ->when(!$todo, fn ($query) => $query->whereBetween('fecha', [$fechaInicio, $fechaFin]))
                    ->whereNotNull('producto_id')
                    ->where('producto_id', '<>', 0)
                    ->distinct('producto_id')
                    ->count('producto_id'),
                'no_en_catalogo' => $nuevos->count(),
            ],
        ]);
    }

    public function insertarDetectados(Request $request)
    {
        $validated = $request->validate([
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'integer', 'not_in:0'],
            'productos.*.tipo' => ['required', 'string', 'max:50'],
            'productos.*.descripcion' => ['required', 'string', 'max:255'],
        ]);

        $productos = collect($validated['productos'])
            ->map(function ($item) {
                return [
                    'producto_id' => (int) ($item['producto_id'] ?? 0),
                    'tipo' => trim((string) ($item['tipo'] ?? '')),
                    'descripcion' => trim((string) ($item['descripcion'] ?? '')),
                ];
            })
            ->filter(function ($item) {
                return $item['producto_id'] !== 0 && $item['tipo'] !== '' && $item['descripcion'] !== '';
            })
            ->unique('producto_id')
            ->filter()
            ->values();

        if ($productos->isEmpty()) {
            return response()->json([
                'message' => 'No hay productos válidos para insertar.',
                'insertados' => 0,
                'omitidos' => 0,
            ], 422);
        }

        $existentes = CatalogoJuego::query()
            ->whereIn('producto_id', $productos->pluck('producto_id')->all())
            ->pluck('producto_id')
            ->map(fn($id) => (int) $id)
            ->all();

        $existentesLookup = array_flip($existentes);

        $insertar = $productos
            ->reject(fn($item) => isset($existentesLookup[$item['producto_id']]))
            ->map(fn($item) => [
                'producto_id' => $item['producto_id'],
                'tipo' => $item['tipo'],
                'descripcion' => $item['descripcion'],
                'sistema' => 'ambos',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        if (!empty($insertar)) {
            CatalogoJuego::insert($insertar);
        }

        return response()->json([
            'message' => 'Inserción completada.',
            'insertados' => count($insertar),
            'omitidos' => $productos->count() - count($insertar),
        ]);
    }

    private function obtenerProductosNuevos(?int $anio = null, ?int $mes = null, bool $todo = false): Collection
    {
        $fechaInicio = null;
        $fechaFin = null;
        if (!$todo && $anio !== null && $mes !== null) {
            $fechaInicio = Carbon::create($anio, $mes, 1)->startOfMonth()->toDateString();
            $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->toDateString();
        }

        $whereFechaBet = '';
        $bindings = [];

        if ($fechaInicio && $fechaFin) {
            $whereFechaBet = ' AND fecha BETWEEN ? AND ?';
            $bindings[] = $fechaInicio;
            $bindings[] = $fechaFin;
        }

        $sql = "
            SELECT
                CAST(v.producto_id AS SIGNED) AS producto_id,
                GROUP_CONCAT(DISTINCT NULLIF(TRIM(v.descripcion), '') ORDER BY v.descripcion SEPARATOR '||') AS descripciones,
                GROUP_CONCAT(DISTINCT NULLIF(TRIM(v.tipo), '') ORDER BY v.tipo SEPARATOR '||') AS tipos,
                'lotobet' AS origenes_texto
            FROM " . self::FUENTE_VENTAS . " v
            LEFT JOIN catalogo_juegos c
                ON c.producto_id = CAST(v.producto_id AS SIGNED)
            WHERE c.id IS NULL
              AND v.producto_id IS NOT NULL
              AND v.producto_id <> 0
              {$whereFechaBet}
            GROUP BY CAST(v.producto_id AS SIGNED)
            ORDER BY CAST(v.producto_id AS SIGNED)
        ";

        $rows = collect(DB::select($sql, $bindings));

        return $rows
            ->map(function ($row) {
                $productoId = trim((string) ($row->producto_id ?? ''));
                $descripcionesDetectadas = collect(explode('||', (string) ($row->descripciones ?? '')))
                    ->map(fn($descripcion) => trim((string) $descripcion))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $tiposDetectados = collect(explode('||', (string) ($row->tipos ?? '')))
                    ->map(fn($tipo) => trim((string) $tipo))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $tipoSugerido = $tiposDetectados[0] ?? 'pendiente';
                $descripcionSugerida = $descripcionesDetectadas[0] ?? ('Producto ' . $productoId);

                $listaOrigenes = collect(explode(', ', (string) ($row->origenes_texto ?? '')))
                    ->map(fn($origen) => trim((string) $origen))
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                return [
                    'producto_id' => $productoId,
                    'descripcion' => $descripcionSugerida,
                    'tipo_sugerido' => $tipoSugerido,
                    'tipos_detectados' => $tiposDetectados,
                    'origenes' => $listaOrigenes,
                    'origenes_texto' => implode(', ', $listaOrigenes),
                ];
            })
            ->sortBy('producto_id', SORT_NATURAL)
            ->values();
    }

    private function obtenerUltimoPeriodoConDatos(): array
    {
        $maxBet = DB::table(self::FUENTE_VENTAS)->max('fecha');

        $fechas = collect([$maxBet])
            ->filter()
            ->map(function ($fecha) {
                try {
                    return Carbon::parse($fecha);
                } catch (\Throwable $e) {
                    return null;
                }
            })
            ->filter()
            ->values();

        if ($fechas->isEmpty()) {
            return [
                'anio' => null,
                'mes' => null,
                'texto' => null,
            ];
        }

        /** @var Carbon $ultima */
        $ultima = $fechas->sortByDesc(fn (Carbon $f) => $f->timestamp)->first();

        return [
            'anio' => (int) $ultima->year,
            'mes' => (int) $ultima->month,
            'texto' => ucfirst($ultima->copy()->locale('es')->translatedFormat('F')) . ' ' . $ultima->year,
        ];
    }
}
