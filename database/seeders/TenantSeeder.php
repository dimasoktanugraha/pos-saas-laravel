<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $tenants = [
            [
                'name' => 'Toko Jaya Abadi',
                'slug' => 'jaya-abadi',
                'email' => 'admin@jaya-abadi.com',
                'phone' => '08123456789',
                'address' => 'Jl. Jaya Abadi No. 1',
                'logo' => '',
                'status' => 'active',
            ],
            [
                'name' => 'Toko Berkah Keramat',
                'slug' => 'berkah-keramat',
                'email' => 'admin@berkah-keramat.com',
                'phone' => '08123456789',
                'address' => 'Jl. Berkah Keramat No. 1',
                'logo' => '',
                'status' => 'trial',
            ]
        ];

        foreach ($tenants as $tenant){
            Tenant::create($tenant);
        }
    }
}
