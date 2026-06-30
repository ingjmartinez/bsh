<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const GUARD = 'web';

    private const PERMISSIONS_BY_MODULE = [
        'dashboard' => [
            'module.dashboard.item.lotedom_ventas.view',
            'module.dashboard.item.lotobet_real_flash.view',
            'module.dashboard.item.lotobet_real_ventas.view',
            'module.dashboard.item.tickets_whatsapp.view',
        ],
        'mantenimiento' => [
            'module.mantenimiento.item.agencias_lotedom.view',
        ],
        'reportes' => [
            'module.reportes.item.faltantes_lotobet_real.view',
            'module.reportes.item.ventas_por_usuario_lotobet_real.view',
        ],
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS_BY_MODULE as $module => $permissions) {
            $requiredRoleNames = ['superadmin', 'admin', "modulo_{$module}"];
            $legacyRoleNames = [$module];

            foreach ($permissions as $permissionName) {
                $permission = Permission::findOrCreate($permissionName, self::GUARD);

                foreach ($requiredRoleNames as $roleName) {
                    Role::findOrCreate($roleName, self::GUARD)->givePermissionTo($permission);
                }

                foreach ($legacyRoleNames as $roleName) {
                    $role = Role::query()
                        ->where('name', $roleName)
                        ->where('guard_name', self::GUARD)
                        ->first();

                    if ($role) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS_BY_MODULE as $module => $permissions) {
            $roleNames = ['superadmin', 'admin', "modulo_{$module}", $module];

            foreach ($permissions as $permissionName) {
                $permission = Permission::query()
                    ->where('name', $permissionName)
                    ->where('guard_name', self::GUARD)
                    ->first();

                if (! $permission) {
                    continue;
                }

                Role::query()
                    ->whereIn('name', $roleNames)
                    ->where('guard_name', self::GUARD)
                    ->get()
                    ->each(fn (Role $role) => $role->revokePermissionTo($permission));
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
