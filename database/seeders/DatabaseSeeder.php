<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@grupojoselito.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password123'),
            ]
        );

        $this->call(ConsorciosSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(ServiciosGeneralesRolePermissionSeeder::class);

        $superAdminRole = Role::where('name', 'superadmin')->where('guard_name', 'web')->first();
        if ($superAdminRole) {
            $admin->syncRoles([$superAdminRole->name]);
        }
    }
}
