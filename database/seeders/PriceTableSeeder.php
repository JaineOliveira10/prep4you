<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceTableSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa as tabelas antes de popular
        DB::table('price_ranges')->truncate();
        DB::table('price_tables')->truncate();

        // === TABELA PADRÃO ===
        $defaultTableId = DB::table('price_tables')->insertGetId([
            'name' => 'Tabela padrão',
            'description' => 'Tabela de preços padrão',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Faixas de preço - Tabela padrão
        DB::table('price_ranges')->insert([
            [
                'price_table_id' => $defaultTableId,
                'min_value' => 1,
                'max_value' => 499,
                'price' => 0.80, // Etiqueta
                'price_kit' => 1.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'price_table_id' => $defaultTableId,
                'min_value' => 500,
                'max_value' => 999,
                'price' => 0.65, // Etiqueta
                'price_kit' => 0.95,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'price_table_id' => $defaultTableId,
                'min_value' => 1000,
                'max_value' => 5000,
                'price' => 0.50, // Etiqueta
                'price_kit' => 0.90,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // === SIMONE E GUI ===
        $simoneGuiId = DB::table('price_tables')->insertGetId([
            'name' => 'Simone e Gui',
            'description' => 'Tabela de preços personalizada para Simone e Gui',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Faixas de preço - Simone e Gui (Etiqueta)
        DB::table('price_ranges')->insert([
            [
                'price_table_id' => $simoneGuiId,
                'min_value' => 1,
                'max_value' => 499,
                'price' => 0.50,
                'price_kit' => 0.80,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'price_table_id' => $simoneGuiId,
                'min_value' => 500,
                'max_value' => 999,
                'price' => 0.45,
                'price_kit' => 0.95,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'price_table_id' => $simoneGuiId,
                'min_value' => 1000,
                'max_value' => 5000,
                'price' => 0.50,
                'price_kit' => 0.90,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
