<?php

namespace Tests\Feature;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SuperadminProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAuthorizationTables();
        Role::findOrCreate('superadmin');
        Role::findOrCreate('admin');
        Role::findOrCreate('admin2');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        foreach (['role_has_permissions', 'model_has_roles', 'model_has_permissions', 'roles', 'permissions', 'users'] as $tableName) {
            Schema::dropIfExists($tableName);
        }

        parent::tearDown();
    }

    public function test_admin_and_admin2_cannot_manage_the_superadmin_role(): void
    {
        $superadminRole = Role::findByName('superadmin');

        foreach (['admin', 'admin2'] as $actorRole) {
            $this->actingAsRole($actorRole);
            $controller = app(RoleController::class);

            foreach ([
                fn () => $controller->edit($superadminRole),
                fn () => $controller->update(Request::create('/roles/1', 'PUT', ['name' => 'otro', 'permissions' => []]), $superadminRole),
                fn () => $controller->destroy($superadminRole),
            ] as $action) {
                $this->assertForbidden($action);
            }
        }

        $this->assertSame('superadmin', $superadminRole->fresh()->name);
    }

    public function test_admin_can_still_manage_other_roles(): void
    {
        $this->actingAsRole('admin');
        $role = Role::findOrCreate('soporte');

        app(RoleController::class)->update(
            Request::create('/roles/'.$role->id, 'PUT', ['name' => 'soporte_ti', 'permissions' => []]),
            $role
        );

        $this->assertSame('soporte_ti', $role->fresh()->name);
    }

    public function test_superadmin_can_edit_its_role_but_cannot_rename_it(): void
    {
        $this->actingAsRole('superadmin');
        $role = Role::findByName('superadmin');

        app(RoleController::class)->update(
            Request::create('/roles/'.$role->id, 'PUT', ['name' => 'renombrado', 'permissions' => []]),
            $role
        );

        $this->assertSame('superadmin', $role->fresh()->name);
    }

    public function test_admin_and_admin2_cannot_manage_superadmin_users(): void
    {
        $target = $this->userWithRole('superadmin');

        foreach (['admin', 'admin2'] as $actorRole) {
            $this->actingAsRole($actorRole);
            $controller = app(UserController::class);

            foreach ([
                fn () => $controller->edit($target),
                fn () => $controller->update(Request::create('/usuarios/1', 'PUT', ['name' => 'x', 'email' => 'x@x.com']), $target),
                fn () => $controller->resetClave($target),
                fn () => $controller->destroy($target),
            ] as $action) {
                $this->assertForbidden($action);
            }
        }
    }

    public function test_only_superadmin_can_assign_the_superadmin_role(): void
    {
        $rule = new \ReflectionMethod(UserController::class, 'forbiddenRolesRule');

        foreach (['admin' => true, 'admin2' => true, 'superadmin' => false] as $actorRole => $shouldFail) {
            $this->actingAsRole($actorRole);
            $failed = false;

            $rule->invoke(app(UserController::class))(
                'roles.0',
                'superadmin',
                function () use (&$failed): void {
                    $failed = true;
                }
            );

            $this->assertSame($shouldFail, $failed, $actorRole);
        }
    }

    public function test_superadmin_users_are_hidden_from_the_users_list_unless_viewer_is_superadmin(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();
        });

        $owner = $this->createUser('Dueno', 'dueno@example.com', 'superadmin');
        $admin = $this->createUser('Admin', 'admin@example.com', 'admin');
        $admin2 = $this->createUser('Admin Dos', 'admin2@example.com', 'admin2');

        $expectations = [
            [$admin, ['Admin', 'Admin Dos']],
            [$admin2, ['Admin', 'Admin Dos']],
            [$owner, ['Admin', 'Admin Dos', 'Dueno']],
        ];

        foreach ($expectations as [$viewer, $expectedNames]) {
            auth()->setUser($viewer);

            $payload = app(UserController::class)->list(Request::create('/usuarios-list', 'GET'))->getData(true);

            $this->assertEqualsCanonicalizing($expectedNames, array_column($payload['data'], 'name'));
            $this->assertSame(count($expectedNames), $payload['recordsTotal']);
        }

        auth()->setUser($admin);
        $searched = app(UserController::class)->list(
            Request::create('/usuarios-list', 'GET', ['search' => ['value' => 'dueno']])
        )->getData(true);

        $this->assertSame([], $searched['data']);
    }

    private function createUser(string $name, string $email, string $role): User
    {
        $user = User::create(['name' => $name, 'email' => $email, 'password' => 'secret-123']);
        $user->assignRole($role);

        return $user;
    }

    private function assertForbidden(callable $action): void
    {
        try {
            $action();
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());

            return;
        }

        $this->fail('Se esperaba una respuesta 403.');
    }

    private function actingAsRole(string $role): void
    {
        auth()->setUser($this->userWithRole($role));
    }

    private function userWithRole(string $role): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->andReturnUsing(
            static fn (string $name): bool => $name === $role
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
