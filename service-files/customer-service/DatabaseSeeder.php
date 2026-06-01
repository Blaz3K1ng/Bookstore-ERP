<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Maria Santos',  'email' => 'maria@email.com',  'phone' => '09171234567', 'city' => 'Cebu City',    'address' => '123 Colon St',       'status' => 'vip'],
            ['name' => 'Juan Dela Cruz','email' => 'juan@email.com',  'phone' => '09181234567', 'city' => 'Manila',       'address' => '456 Rizal Ave',      'status' => 'active'],
            ['name' => 'Ana Reyes',     'email' => 'ana@email.com',   'phone' => '09191234567', 'city' => 'Davao City',   'address' => '789 Bonifacio St',   'status' => 'new'],
            ['name' => 'Pedro Garcia',  'email' => 'pedro@email.com', 'phone' => '09201234567', 'city' => 'Quezon City',  'address' => '321 EDSA',           'status' => 'active'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['email' => $customer['email']], $customer);
        }
    }
}
