<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ServiciosGeneralesRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'servicios_generales.view',
            'servicios_generales.create',
            'servicios_generales.manage',
            'servicios_generales.close',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $rolesPermisos = [
            'servicios_generales' => [
                'servicios_generales.view',
                'servicios_generales.create',
                'servicios_generales.manage',
                'servicios_generales.close',
            ],
            'superadmin' => $permissions,
            'admin' => $permissions,
        ];

        foreach ($rolesPermisos as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->givePermissionTo($rolePermissions);
        }
    }
}
