<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MovimientoMayorPermissionTest extends TestCase
{
    private const PERMISSION = 'module.contabilidad.item.movimiento_mayor.view';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAuthorizationTables();
        Role::findOrCreate('contabilidad');

        $migration = require database_path('migrations/2026_09_14_112614_add_movimiento_mayor_permission.php');
        $migration->up();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        foreach (['role_has_permissions', 'model_has_roles', 'model_has_permissions', 'roles', 'permissions'] as $tableName) {
            Schema::dropIfExists($tableName);
        }

        parent::tearDown();
    }

    public function test_permission_is_created_and_assigned_to_contabilidad_roles(): void
    {
        $permission = Permission::findByName(self::PERMISSION);

        foreach (['superadmin', 'admin', 'modulo_contabilidad', 'contabilidad'] as $roleName) {
            $this->assertTrue(Role::findByName($roleName)->hasPermissionTo($permission));
        }
    }

    private function createAuthorizationTables(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });
    }
}
