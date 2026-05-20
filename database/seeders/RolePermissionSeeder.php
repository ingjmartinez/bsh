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
            'tickets.view',
            'tickets.manage',
            'servicios_generales.view',
            'servicios_generales.create',
            'servicios_generales.manage',
            'servicios_generales.close',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $adminPermissions = array_values(array_filter(
            $permissions,
            static fn(string $permission): bool => !str_ends_with($permission, '.delete')
        ));

        $roles = [
            'superadmin' => $permissions,
            'admin' => $adminPermissions,
            'contabilidad' => ['usuarios.view', 'usuarios.list'],
            'rh' => ['usuarios.view', 'usuarios.list'],
            'comercial' => ['usuarios.view', 'usuarios.list'],
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

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
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
    }
}
