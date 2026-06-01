<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name'          => 'Filbooks Distribution Inc.',
                'contact_name'  => 'Maria Santos',
                'contact_email' => 'maria@filbooks.ph',
                'phone'         => '+63 2 8888 1234',
                'address'       => '123 Rizal Ave, Manila, Metro Manila',
                'notes'         => 'Primary distributor for Filipino literature and textbooks.',
                'status'        => 'active',
            ],
            [
                'name'          => 'Asia Pacific Publishers Ltd.',
                'contact_name'  => 'John Chen',
                'contact_email' => 'jchen@asiapacpub.com',
                'phone'         => '+63 32 999 5678',
                'address'       => '456 Cebu IT Park, Cebu City',
                'notes'         => 'Specializes in tech books, programming, and academic titles.',
                'status'        => 'active',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['contact_email' => $supplier['contact_email']],
                $supplier
            );
        }
    }
}
