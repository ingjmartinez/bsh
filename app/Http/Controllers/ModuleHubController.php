<?php

namespace App\Http\Controllers;

use App\Support\ModuleHubAccess;
use Illuminate\View\View;

class ModuleHubController extends Controller
{
    public function __construct(private readonly ModuleHubAccess $moduleHubAccess) {}

    public function dashboard(): View
    {
        return $this->show('dashboard');
    }

    public function comercial(): View
    {
        return $this->show('comercial');
    }

    public function contabilidad(): View
    {
        return $this->show('contabilidad');
    }

    public function operaciones(): View
    {
        return $this->show('operaciones');
    }

    public function mantenimiento(): View
    {
        return $this->show('mantenimiento');
    }

    public function tecnologia(): View
    {
        return $this->show('tecnologia');
    }

    public function incentivos(): View
    {
        return $this->show('incentivos');
    }

    public function procesos(): View
    {
        return $this->show('procesos');
    }

    public function gerencia(): View
    {
        return $this->show('gerencia');
    }

    public function serviciosGenerales(): View
    {
        return $this->show('servicios_generales');
    }

    public function show(string $module): View
    {
        $hub = config("module_hubs.{$module}");

        abort_unless(is_array($hub), 404);

        $user = auth()->user();
        abort_unless($this->moduleHubAccess->canAccessModule($user, $module, $hub), 403);

        $items = collect($hub['items'] ?? [])
            ->filter(fn ($item): bool => is_array($item) && $this->moduleHubAccess->canViewItem($user, $module, $item))
            ->map(function (array $item): array {
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
