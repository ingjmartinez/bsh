<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BiModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAuthorizationTables();

        $migration = require database_path('migrations/2026_09_26_155546_add_bi_module_permissions.php');
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

    public function test_bi_hub_contains_the_three_sales_platforms(): void
    {
        $items = collect(config('module_hubs.bi.items'));

        $this->assertSame(['Lotobet Real', 'Lotodom', 'Delta'], $items->pluck('nombre')->all());
        $this->assertSame(
            ['/bi/lotobet-real', '/bi/lotodom', '/bi/delta'],
            $items->pluck('url')->all()
        );
    }

    public function test_bi_route_and_sidebar_are_registered(): void
    {
        $route = Route::getRoutes()->getByName('bi.index');
        $sidebar = file_get_contents(resource_path('views/app.blade.php'));

        $this->assertNotNull($route);
        $this->assertContains('role_or_permission:superadmin|admin|module.bi.view', $route->gatherMiddleware());
        $this->assertStringContainsString('id="sidebarBi"', $sidebar);
        $this->assertStringContainsString('Lotobet Real', $sidebar);
        $this->assertStringContainsString('Lotodom', $sidebar);
        $this->assertStringContainsString('Delta', $sidebar);
    }

    public function test_bi_platforms_have_independent_routes(): void
    {
        foreach ([
            'bi.lotobet-real' => 'module.bi.item.lotobet_real.view',
            'bi.lotodom' => 'module.bi.item.lotodom.view',
            'bi.delta' => 'module.bi.item.delta.view',
        ] as $routeName => $permission) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route);
            $this->assertContains("role_or_permission:superadmin|admin|{$permission}", $route->gatherMiddleware());
        }

        $this->assertStringContainsString(
            'BiLotobetController@index',
            Route::getRoutes()->getByName('bi.lotobet-real')?->getActionName() ?? ''
        );
        $this->assertStringContainsString(
            'BiLotobetController@dashboard',
            Route::getRoutes()->getByName('bi.lotobet-real.dashboard')?->getActionName() ?? ''
        );
        $this->assertSame('bi.plataforma', Route::getRoutes()->getByName('bi.lotodom')?->defaults['view'] ?? null);
        $this->assertSame('bi.plataforma', Route::getRoutes()->getByName('bi.delta')?->defaults['view'] ?? null);
    }

    public function test_bi_permissions_are_assigned_to_module_and_administrator_roles(): void
    {
        foreach ([
            'module.bi.view',
            'module.bi.item.lotobet_real.view',
            'module.bi.item.lotodom.view',
            'module.bi.item.delta.view',
        ] as $permissionName) {
            $permission = Permission::findByName($permissionName);

            foreach (['module_bi', 'admin', 'superadmin'] as $roleName) {
                $this->assertTrue(Role::findByName($roleName)->hasPermissionTo($permission));
            }
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
