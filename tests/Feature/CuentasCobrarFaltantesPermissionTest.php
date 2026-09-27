<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CuentasCobrarFaltantesPermissionTest extends TestCase
{
    private const PERMISSION = 'module.contabilidad.item.cxc_faltantes.view';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAuthorizationTables();
        Role::findOrCreate('contabilidad');

        $migration = require database_path('migrations/2026_09_25_172107_add_cxc_faltantes_permission.php');
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

    public function test_permission_is_created_and_assigned_to_accounting_roles(): void
    {
        $permission = Permission::findByName(self::PERMISSION);

        foreach (['superadmin', 'admin', 'modulo_contabilidad', 'contabilidad'] as $roleName) {
            $this->assertTrue(Role::findByName($roleName)->hasPermissionTo($permission));
        }
    }

    public function test_hub_item_and_all_routes_use_the_specific_permission(): void
    {
        $item = collect(config('module_hubs.contabilidad.items'))
            ->firstWhere('url', '/contabilidad/cuentas-cobrar-faltantes');

        $this->assertSame(self::PERMISSION, $item['permission'] ?? null);

        foreach ([
            'contabilidad.cuentas-cobrar-faltantes',
            'contabilidad.cuentas-cobrar-faltantes.data',
            'contabilidad.cuentas-cobrar-faltantes.detalle',
            'contabilidad.cuentas-cobrar-faltantes.faltantes',
            'contabilidad.cuentas-cobrar-faltantes.abonos',
        ] as $routeName) {
            $middleware = Route::getRoutes()->getByName($routeName)?->gatherMiddleware() ?? [];

            $this->assertContains('role_or_permission:superadmin|admin|'.self::PERMISSION, $middleware);
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
