<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
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
            'servicios_generales.view',
            'servicios_generales.create',
            'servicios_generales.manage',
            'servicios_generales.close',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'superadmin' => $permissions,
            'admin' => $permissions,
            'contabilidad' => ['usuarios.view', 'usuarios.list'],
            'rh' => ['usuarios.view', 'usuarios.list'],
            'comercial' => ['usuarios.view', 'usuarios.list'],
            'monitoreo' => ['usuarios.view', 'usuarios.list'],
            'servicios_generales' => [
                'servicios_generales.view',
                'servicios_generales.create',
                'servicios_generales.manage',
                'servicios_generales.close',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }

        $superAdminEmail = env('SUPERADMIN_EMAIL', 'admin@joselitogroud.com');
        $superAdmin = User::where('email', $superAdminEmail)->first();

        if ($superAdmin) {
            $superAdmin->syncRoles(['superadmin']);
        }
    }
}
