<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::create([
            'name' => 'admin',
            'permissions' => ['manage_users', 'edit_invoices', 'view_reports'],
        ]);

        User::create([
            'name' => 'Usuario Prueba',
            'email' => 'root@root.com',
            'password' => bcrypt('pepe123'),
            'role_id' => $adminRole->id,
        ]);
    }
}
