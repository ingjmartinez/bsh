<?php

namespace App\Http\Controllers;

class ModuleHubController extends Controller
{
    public function dashboard()
    {
        return $this->show('dashboard');
    }

    public function comercial()
    {
        return $this->show('comercial');
    }

    public function contabilidad()
    {
        return $this->show('contabilidad');
    }

    public function operaciones()
    {
        return $this->show('operaciones');
    }

    public function mantenimiento()
    {
        return $this->show('mantenimiento');
    }

    public function tecnologia()
    {
        return $this->show('tecnologia');
    }

    public function incentivos()
    {
        return $this->show('incentivos');
    }

    public function procesos()
    {
        return $this->show('procesos');
    }

    public function gerencia()
    {
        return $this->show('gerencia');
    }

    public function serviciosGenerales()
    {
        return $this->show('servicios_generales');
    }

    public function show(string $module)
    {
        $hub = config("module_hubs.{$module}");

        abort_unless(is_array($hub), 404);

        $user = auth()->user();

        $items = collect($hub['items'] ?? [])
            ->filter(function ($item) use ($user) {
                if (! (bool) ($item['activo'] ?? true)) {
                    return false;
                }

                if (!empty($item['permission']) && (! $user || ! $user->can($item['permission']))) {
                    return false;
                }

                if (!empty($item['role']) && (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole($item['role']))) {
                    return false;
                }

                return true;
            })
            ->map(function ($item) {
                $item['url'] = url($item['url']);
                $item['tags'] = $item['tags'] ?? [];

                return $item;
            })
            ->sortBy('nombre')
            ->values();

        $categorias = $items
            ->pluck('categoria')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('module-hub.index', [
            'module' => $module,
            'titulo' => $hub['titulo'] ?? ucfirst($module),
            'breadcrumb' => $hub['breadcrumb'] ?? ($hub['titulo'] ?? ucfirst($module)),
            'items' => $items,
            'categorias' => $categorias,
        ]);
    }
}
