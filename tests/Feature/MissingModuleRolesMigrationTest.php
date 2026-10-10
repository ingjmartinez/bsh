<?php

namespace Tests\Feature;

use App\Support\ModuleHubAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MissingModuleRolesMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAuthorizationTables();

        foreach (['contabilidad', 'rh', 'module_bi'] as $legacyRole) {
            Role::findOrCreate($legacyRole);
        }

        $migration = require database_path('migrations/2026_10_09_111500_add_missing_module_item_permissions_and_roles.php');
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

    public function test_hub_items_without_permission_now_have_one_granted_to_their_module_role(): void
    {
        $access = app(ModuleHubAccess::class);
        $items = [
            'contabilidad' => [config('module_hubs.contabilidad.items'), 'Movimiento Mayor'],
            'mantenimiento' => [config('module_hubs.mantenimiento.items'), 'Agencias Delta'],
            'incentivos' => [config('module_hubs.incentivos.items'), 'Cálculo de Incentivos'],
            'recursos_humanos' => [config('recursos_humanos'), 'Nuevos ingresos'],
            'reportes' => [config('reportes'), 'Premios pagados por productos'],
        ];

        foreach ($items as $module => [$moduleItems, $itemName]) {
            $item = collect($moduleItems)->firstWhere('nombre', $itemName);
            $this->assertNotNull($item, "{$itemName} no existe en la configuración");

            $permissionName = $access->itemPermission($module, $item);
            $this->assertTrue(Role::findByName("modulo_{$module}")->hasPermissionTo($permissionName));
        }
    }

    public function test_new_item_permissions_are_assigned_to_admin_and_legacy_roles(): void
    {
        $this->assertTrue(Role::findByName('admin2')->hasPermissionTo('module.mantenimiento.item.agencias_delta.view'));
        $this->assertTrue(Role::findByName('superadmin')->hasPermissionTo('module.incentivos.item.calculo_de_incentivos.view'));
        $this->assertTrue(Role::findByName('rh')->hasPermissionTo('module.recursos_humanos.item.nuevos_ingresos.view'));
        $this->assertTrue(Role::findByName('contabilidad')->hasPermissionTo('module.contabilidad.item.movimiento_mayor.view'));
    }

    public function test_modulo_bi_role_is_created_with_bi_permissions(): void
    {
        $role = Role::findByName('modulo_bi');

        foreach (['module.bi.view', 'module.bi.item.lotobet_real.view', 'module.bi.item.lotodom.view', 'module.bi.item.delta.view'] as $permission) {
            $this->assertTrue($role->hasPermissionTo($permission));
            $this->assertTrue(Role::findByName('module_bi')->hasPermissionTo($permission));
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
