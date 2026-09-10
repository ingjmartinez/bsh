<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReporteBonosAuthorizationTest extends TestCase
{
    private const PERMISSION = 'module.incentivos.item.reporte_de_bonos.view';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAuthorizationTables();

        $migration = require database_path('migrations/2026_09_10_085355_add_reporte_bonos_permission.php');
        $migration->up();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        foreach (['role_has_permissions', 'model_has_roles', 'model_has_permissions', 'roles', 'permissions', 'users'] as $tableName) {
            Schema::dropIfExists($tableName);
        }

        parent::tearDown();
    }

    public function test_permission_is_created_and_assigned_to_module_roles(): void
    {
        $permission = Permission::findByName(self::PERMISSION);

        $this->assertTrue(Role::findByName('superadmin')->hasPermissionTo($permission));
        $this->assertTrue(Role::findByName('admin')->hasPermissionTo($permission));
        $this->assertTrue(Role::findByName('modulo_incentivos')->hasPermissionTo($permission));
    }

    public function test_user_without_permission_cannot_open_the_report_or_query_its_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('incentivos.reporte-bonos.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->getJson(route('incentivos.reporte-bonos.datos', [
                'fecha_ini' => '2026-07-01',
                'fecha_fin' => '2026-07-31',
            ]))
            ->assertForbidden();
    }

    public function test_user_with_permission_can_open_the_report(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(self::PERMISSION);

        $this->actingAs($user)
            ->get(route('incentivos.reporte-bonos.index'))
            ->assertOk()
            ->assertSee('Reporte de Bonos');
    }

    private function createAuthorizationTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

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
