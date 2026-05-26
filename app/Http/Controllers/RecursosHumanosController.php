<?php

namespace App\Http\Controllers;

class RecursosHumanosController extends Controller
{
    public function index()
    {
        // Los modulos inactivos permanecen configurados para preservar rutas/datos,
        // pero no se muestran en el hub de Recursos Humanos.
        $modulos = collect(config('recursos_humanos', []))
            ->filter(fn ($modulo) => (bool) ($modulo['activo'] ?? true))
            ->map(function ($modulo) {
                $modulo['url'] = url($modulo['url']);
                $modulo['tags'] = $modulo['tags'] ?? [];

                return $modulo;
            })
            ->sortBy('nombre')
            ->values();

        $categorias = $modulos
            ->pluck('categoria')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('recursos_humanos.index', compact('modulos', 'categorias'));
    }
}
