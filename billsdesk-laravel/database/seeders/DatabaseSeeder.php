<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\Company;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $company = Company::create([
            'name' => 'Empresa Ejemplo',
            'address' => 'Dirección de ejemplo',
        ]);

        $company2 = Company::create([
            'name' => 'Empresa Ejemplo 2',
            'address' => 'Dirección de ejemplo 2',
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'isAdmin' => true,
            'permissions' => ['manage_users', 'edit_invoices', 'view_reports'],
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'permissions' => [
                'manage_files', 'manage_invoices', 'manage_invoice_templates', 'meProfile', 'manage_correction_rules', 'view_contacts'
            ],
        ]);

        User::create([
            'name' => 'Usuario Prueba',
            'email' => 'root@root.com',
            'password' => bcrypt('pepe123'),
            'phone' => '123456789',
            'address' => 'Dirección de prueba',
            'role_id' => $adminRole->id,
            'company_id' => $company->id,
        ]);

        User::create([
            'name' => 'Root Prueba 2',
            'email' => 'root2@root2.com',
            'password' => bcrypt('pepe123'),
            'phone' => '123456789',
            'address' => 'Dirección de prueba',
            'role_id' => $adminRole->id,
            'company_id' => $company2->id,
        ]);

        User::create([
            'name' => 'Usuario Prueba 3',
            'email' => 'user@user.com',
            'password' => bcrypt('pepe123'),
            'phone' => '123456789',
            'address' => 'Dirección de prueba',
            'role_id' => $userRole->id,
            'company_id' => $company->id,
        ]);
    }
}
