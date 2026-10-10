<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const GUARD = 'web';

    /**
     * @var array<string, array<int, string>>
     */
    private const PERMISSIONS_BY_MODULE = [
        'dashboard' => [
            'tickets.view',
        ],
        'contabilidad' => [
            'module.contabilidad.item.movimiento_mayor.view',
        ],
        'mantenimiento' => [
            'module.mantenimiento.item.agencias_delta.view',
        ],
        'incentivos' => [
            'module.incentivos.item.calculo_de_incentivos.view',
        ],
        'recursos_humanos' => [
            'module.recursos_humanos.item.nuevos_ingresos.view',
            'module.recursos_humanos.item.movimiento_de_usuario.view',
            'module.recursos_humanos.item.agencias_sin_actividad.view',
            'module.recursos_humanos.item.cruce_de_usuarios.view',
            'module.recursos_humanos.item.reporte_de_faltantes.view',
        ],
        'reportes' => [
            'module.reportes.item.premios_pagados_por_productos.view',
        ],
        'bi' => [
            'module.bi.view',
            'module.bi.item.lotobet_real.view',
            'module.bi.item.lotodom.view',
            'module.bi.item.delta.view',
        ],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const LEGACY_ROLES_BY_MODULE = [
        'dashboard' => ['dashboard'],
        'contabilidad' => ['contabilidad'],
        'mantenimiento' => ['mantenimiento'],
        'incentivos' => ['incentivos'],
        'recursos_humanos' => ['rh', 'recursos_humanos'],
        'reportes' => ['reportes'],
        'bi' => ['module_bi'],
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS_BY_MODULE as $module => $permissions) {
            $roleNames = ['superadmin', 'admin', 'admin2', "modulo_{$module}"];

            foreach ($permissions as $permissionName) {
                $permission = Permission::findOrCreate($permissionName, self::GUARD);

                foreach ($roleNames as $roleName) {
                    Role::findOrCreate($roleName, self::GUARD)->givePermissionTo($permission);
                }

                Role::query()
                    ->where('guard_name', self::GUARD)
                    ->whereIn('name', self::LEGACY_ROLES_BY_MODULE[$module])
                    ->get()
                    ->each(fn (Role $role) => $role->givePermissionTo($permission));
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::query()
            ->where('name', 'modulo_bi')
            ->where('guard_name', self::GUARD)
            ->first()
            ?->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
