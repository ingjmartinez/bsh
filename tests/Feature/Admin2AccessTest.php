<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventAdmin2Deletion;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class Admin2AccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAuthorizationTables();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        foreach (['role_has_permissions', 'model_has_roles', 'model_has_permissions', 'roles', 'permissions'] as $tableName) {
            Schema::dropIfExists($tableName);
        }

        parent::tearDown();
    }

    public function test_migration_creates_admin2_role(): void
    {
        $migration = require database_path('migrations/2026_09_26_222000_create_admin2_role_without_delete_permissions.php');
        $migration->up();

        $this->assertSame('admin2', Role::findByName('admin2')->name);
    }

    public function test_admin2_cannot_send_delete_requests(): void
    {
        $request = Request::create('/registros/10', 'DELETE', server: ['HTTP_ACCEPT' => 'application/json']);
        $request->setUserResolver(fn () => $this->admin2User());
        $request->setRouteResolver(fn () => new Route('DELETE', '/registros/{id}', fn () => null));

        $response = app(PreventAdmin2Deletion::class)->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('El rol admin2 no tiene permiso para eliminar contenido.', $response->getData(true)['message']);
    }

    public function test_admin2_cannot_use_legacy_get_delete_routes(): void
    {
        $route = (new Route('GET', '/delete-registros', fn () => null))->name('registros.delete');
        $request = Request::create('/delete-registros', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
        $request->setUserResolver(fn () => $this->admin2User());
        $request->setRouteResolver(fn () => $route);

        $response = app(PreventAdmin2Deletion::class)->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_admin2_can_use_non_destructive_routes(): void
    {
        $request = Request::create('/reportes', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
        $request->setUserResolver(fn () => $this->admin2User());
        $request->setRouteResolver(fn () => (new Route('GET', '/reportes', fn () => null))->name('reportes.index'));

        $response = app(PreventAdmin2Deletion::class)->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['ok' => true], $response->getData(true));
    }

    public function test_admin2_gate_allows_views_but_denies_delete_abilities(): void
    {
        $user = $this->admin2User();

        $this->assertTrue(Gate::forUser($user)->allows('module.bi.view'));
        $this->assertFalse(Gate::forUser($user)->allows('usuarios.delete'));
        $this->assertFalse(Gate::forUser($user)->allows('registros.destroy'));
    }

    public function test_layout_hides_delete_controls_before_body_is_rendered(): void
    {
        $layout = file_get_contents(resource_path('views/app.blade.php'));
        $stylePosition = strpos($layout, 'id="admin2-delete-guard"');
        $observerPosition = strpos($layout, 'id="admin2-delete-observer"');
        $bodyPosition = strpos($layout, '<body');

        $this->assertNotFalse($stylePosition);
        $this->assertNotFalse($observerPosition);
        $this->assertNotFalse($bodyPosition);
        $this->assertLessThan($bodyPosition, $stylePosition);
        $this->assertLessThan($bodyPosition, $observerPosition);
        $this->assertStringContainsString('form:has(input[name="_method"][value="DELETE" i])', $layout);
        $this->assertStringContainsString('button[class*="delete" i]', $layout);
    }

    private function admin2User(): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->andReturnUsing(
            static fn (string $role): bool => $role === 'admin2'
        );

        return $user;
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
