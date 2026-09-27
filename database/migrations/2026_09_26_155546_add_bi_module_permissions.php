<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const GUARD = 'web';

    private const PERMISSIONS = [
        'module.bi.view',
        'module.bi.item.lotobet_real.view',
        'module.bi.item.lotodom.view',
        'module.bi.item.delta.view',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permissionName) {
            Permission::findOrCreate($permissionName, self::GUARD);
        }

        Role::findOrCreate('module_bi', self::GUARD)->syncPermissions(self::PERMISSIONS);

        foreach (['admin', 'superadmin'] as $roleName) {
            Role::findOrCreate($roleName, self::GUARD)->givePermissionTo(self::PERMISSIONS);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::query()
            ->where('name', 'module_bi')
            ->where('guard_name', self::GUARD)
            ->first()
            ?->delete();

        Permission::query()
            ->where('guard_name', self::GUARD)
            ->whereIn('name', self::PERMISSIONS)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
