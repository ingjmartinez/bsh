<?php

namespace App\Http\Controllers;

use App\Models\ServicioGeneralRutaInspeccion;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ServicioGeneralRutaInspeccionController extends Controller
{
    public function index(): View
    {
        return view('servicios-generales.ruta-inspeccion', [
            'rutasInspeccion' => Schema::hasTable('servicios_generales_rutas_inspeccion')
                ? ServicioGeneralRutaInspeccion::query()->latest()->get()
                : collect(),
        ]);
    }
}
