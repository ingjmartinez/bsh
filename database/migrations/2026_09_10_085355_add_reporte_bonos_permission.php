<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const GUARD = 'web';

    private const PERMISSION = 'module.incentivos.item.reporte_de_bonos.view';

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::findOrCreate(self::PERMISSION, self::GUARD);

        foreach (['superadmin', 'admin', 'modulo_incentivos'] as $roleName) {
            Role::findOrCreate($roleName, self::GUARD)->givePermissionTo($permission);
        }

        $legacyRole = Role::query()
            ->where('name', 'incentivos')
            ->where('guard_name', self::GUARD)
            ->first();

        $legacyRole?->givePermissionTo($permission);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()
            ->where('name', self::PERMISSION)
            ->where('guard_name', self::GUARD)
            ->first();

        if ($permission) {
            $permission->roles()->detach();
            $permission->users()->detach();
            $permission->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
