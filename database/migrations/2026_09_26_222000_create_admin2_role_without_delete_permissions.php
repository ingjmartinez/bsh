<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Role::findOrCreate('admin2', 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::query()
            ->where('name', 'admin2')
            ->where('guard_name', 'web')
            ->first()
            ?->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
