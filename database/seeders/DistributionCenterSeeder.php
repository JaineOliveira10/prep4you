<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistributionCenterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('distribution_centers')->insert([
            [
                'acronym' => 'GRU8',
                'name' => 'Centro de Distribuição GRU8',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'acronym' => 'GRU9', 
                'name' => 'Centro de Distribuição GRU9',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'acronym' => 'XCV9',
                'name' => 'Centro de Distribuição XCV9', 
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}