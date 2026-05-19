<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const GUARD = 'web';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $hubs = config('module_hubs', []);
        if (!is_array($hubs) || empty($hubs)) {
            return;
        }

        $permissionsByModule = [];
        $allGeneratedPermissions = [];

        foreach ($hubs as $module => $hub) {
            if (!is_array($hub)) {
                continue;
            }

            $modulePermission = "module.$module.view";
            Permission::findOrCreate($modulePermission, self::GUARD);
            $permissionsByModule[$module][] = $modulePermission;
            $allGeneratedPermissions[] = $modulePermission;

            $items = $hub['items'] ?? [];
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $itemName = trim((string) ($item['nombre'] ?? 'item'));
                $slug = Str::slug($itemName, '_');
                if ($slug === '') {
                    $slug = 'item_' . substr(md5($module . '|' . $itemName . '|' . (($item['url'] ?? ''))), 0, 8);
                }

                $itemPermission = "module.$module.item.$slug.view";
                Permission::findOrCreate($itemPermission, self::GUARD);
                $permissionsByModule[$module][] = $itemPermission;
                $allGeneratedPermissions[] = $itemPermission;

                $explicitPermission = trim((string) ($item['permission'] ?? ''));
                if ($explicitPermission !== '') {
                    Permission::findOrCreate($explicitPermission, self::GUARD);
                    $permissionsByModule[$module][] = $explicitPermission;
                }
            }
        }

        // Permisos CRUD de seguridad para mantenimiento (rutas/controllers existentes).
        $securityCrudPermissions = [
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
        ];

        foreach ($securityCrudPermissions as $permission) {
            Permission::findOrCreate($permission, self::GUARD);
        }

        if (isset($permissionsByModule['mantenimiento'])) {
            $permissionsByModule['mantenimiento'] = array_merge(
                $permissionsByModule['mantenimiento'],
                $securityCrudPermissions
            );
        }

        // Crea roles por modulo y les asigna permisos del modulo/tarjetas.
        foreach ($permissionsByModule as $module => $permissions) {
            $roleName = 'module_' . $module;
            $role = Role::findOrCreate($roleName, self::GUARD);
            $role->syncPermissions(array_values(array_unique($permissions)));

            // Si existe un rol legacy con el mismo nombre del modulo, le anexamos estos permisos.
            $legacyRole = Role::where('name', $module)->where('guard_name', self::GUARD)->first();
            if ($legacyRole) {
                $legacyRole->givePermissionTo(array_values(array_unique($permissions)));
            }
        }

        // Admin y superadmin reciben todos los permisos generados por modulos.
        $generatedUnique = array_values(array_unique($allGeneratedPermissions));
        foreach (['admin', 'superadmin'] as $adminRoleName) {
            $adminRole = Role::findOrCreate($adminRoleName, self::GUARD);
            $adminRole->givePermissionTo($generatedUnique);

            // Asegura tambien CRUD de seguridad.
            $adminRole->givePermissionTo($securityCrudPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $hubs = config('module_hubs', []);
        if (!is_array($hubs)) {
            $hubs = [];
        }

        foreach (array_keys($hubs) as $module) {
            $roleName = 'module_' . $module;
            $role = Role::where('name', $roleName)->where('guard_name', self::GUARD)->first();
            if ($role) {
                $role->delete();
            }
        }

        Permission::query()
            ->where('guard_name', self::GUARD)
            ->where('name', 'like', 'module.%')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

