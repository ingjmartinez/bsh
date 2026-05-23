<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    private const GUARD = 'web';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'usuarios.view',
            'usuarios.list',
            'usuarios.create',
            'usuarios.edit',
            'usuarios.delete',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'tickets.view',
            'tickets.manage',
            'servicios_generales.view',
            'servicios_generales.create',
            'servicios_generales.manage',
            'servicios_generales.close',
        ];

        $modulePermissions = $this->modulePermissions();
        $permissions = array_values(array_unique(array_merge($permissions, ...array_values($modulePermissions))));

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, self::GUARD);
        }

        $adminPermissions = array_values(array_filter(
            $permissions,
            static fn(string $permission): bool => !str_ends_with($permission, '.delete')
        ));

        $roles = [
            'superadmin' => $permissions,
            'admin' => $adminPermissions,
            'contabilidad' => $modulePermissions['contabilidad'],
            'rh' => $modulePermissions['recursos_humanos'],
            'comercial' => $modulePermissions['comercial'],
            'monitoreo' => ['usuarios.view', 'usuarios.list'],
            'tickets' => [
                'tickets.view',
                'tickets.manage',
            ],
            'servicios_generales' => [
                'servicios_generales.view',
                'servicios_generales.create',
                'servicios_generales.manage',
                'servicios_generales.close',
            ],
        ];

        foreach ($modulePermissions as $module => $moduleRolePermissions) {
            $roles['modulo_' . $module] = $moduleRolePermissions;
        }

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, self::GUARD);
            $role->syncPermissions($rolePermissions);
        }

        // Fallbacks defensivos para entornos donde SUPERADMIN_EMAIL no está definido
        // o quedó un valor legacy con typo.
        $superAdminEmailCandidates = array_values(array_unique(array_filter([
            env('SUPERADMIN_EMAIL'),
            'admin@grupojoselito.com',
            'admin@joselitogroud.com', // legacy typo
        ])));

        $superAdmin = User::whereIn('email', $superAdminEmailCandidates)->first();

        if ($superAdmin) {
            $superAdmin->syncRoles(['superadmin']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function modulePermissions(): array
    {
        $permissions = [];

        foreach (array_keys(config('module_hubs', [])) as $module) {
            $permissions[$module] = $this->permissionsForModule($module);
        }

        $permissions['recursos_humanos'] = array_values(array_unique(array_merge(
            $permissions['recursos_humanos'] ?? [],
            ['module.recursos_humanos.view'],
            $this->itemPermissionsFromConfig('recursos_humanos', config('recursos_humanos', []))
        )));

        $permissions['reportes'] = array_values(array_unique(array_merge(
            $permissions['reportes'] ?? [],
            ['module.reportes.view'],
            $this->itemPermissionsFromConfig('reportes', config('reportes', []))
        )));

        $permissions['proyecto'] = [
            'module.proyecto.view',
        ];

        $permissions['tareas'] = [
            'module.tareas.view',
        ];

        $permissions['ticket'] = [
            'module.ticket.view',
            'tickets.view',
            'tickets.manage',
        ];

        $permissions['servicios_generales'] = array_values(array_unique(array_merge(
            $permissions['servicios_generales'] ?? [],
            [
                'servicios_generales.view',
                'servicios_generales.create',
                'servicios_generales.manage',
                'servicios_generales.close',
            ]
        )));

        return $permissions;
    }

    private function permissionsForModule(string $module): array
    {
        $hub = config("module_hubs.{$module}", []);

        return array_values(array_unique(array_merge(
            ["module.{$module}.view"],
            $this->itemPermissionsFromConfig($module, $hub['items'] ?? [])
        )));
    }

    private function itemPermissionsFromConfig(string $module, array $items): array
    {
        $permissions = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $explicitPermission = trim((string) ($item['permission'] ?? ''));
            if ($explicitPermission !== '') {
                $permissions[] = $explicitPermission;
            }

            $itemName = trim((string) ($item['nombre'] ?? 'item'));
            $slug = Str::slug($itemName, '_');
            if ($slug === '') {
                $slug = 'item_' . substr(md5($module . '|' . $itemName . '|' . ($item['url'] ?? '')), 0, 8);
            }

            $permissions[] = "module.{$module}.item.{$slug}.view";
        }

        return $permissions;
    }
}
