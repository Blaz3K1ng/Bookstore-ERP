<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin',      'email' => 'admin@pageturn.com',       'role' => 'admin',     'password' => 'password123'],
            ['name' => 'Finance Admin',    'email' => 'finance@pageturn.com',     'role' => 'finance_admin',   'password' => 'password123'],
            ['name' => 'Inventory Admin',  'email' => 'inventory@pageturn.com',   'role' => 'inventory_admin', 'password' => 'password123'],
            ['name' => 'Catalog Admin',    'email' => 'catalog@pageturn.com',     'role' => 'catalog_admin',   'password' => 'password123'],
            ['name' => 'Orders Admin',     'email' => 'orders@pageturn.com',      'role' => 'orders_admin',    'password' => 'password123'],
            ['name' => 'General Staff',    'email' => 'staff@pageturn.com',       'role' => 'staff',           'password' => 'password123'],
            ['name' => 'Sample Customer',  'email' => 'customer@example.com',     'role' => 'customer',        'password' => 'password123'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                ['name' => $user['name'], 'role' => $user['role'], 'password' => Hash::make($user['password'])]
            );
        }
    }
}
