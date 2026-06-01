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
            ['name' => 'Admin User',       'email' => 'admin@pagecraft.ph',       'role' => 'admin',             'password' => 'secret123'],
            ['name' => 'Warehouse Lead',   'email' => 'warehouse@pagecraft.ph', 'role' => 'warehouse_manager', 'password' => 'secret123'],
            ['name' => 'Sales Agent',      'email' => 'sales@pagecraft.ph',     'role' => 'sales_agent',       'password' => 'secret123'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                ['name' => $user['name'], 'role' => $user['role'], 'password' => Hash::make($user['password'])]
            );
        }
    }
}
