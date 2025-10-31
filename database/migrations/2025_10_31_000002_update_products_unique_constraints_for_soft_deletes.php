<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove constraints únicos existentes se existirem
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique(['asin']);
            });
        } catch (Exception $e) {}
        
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique(['fsnku']);
            });
        } catch (Exception $e) {}
        
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique(['sku']);
            });
        } catch (Exception $e) {}
        
        // Cria índices únicos parciais que ignoram soft deletes
        DB::statement('CREATE UNIQUE INDEX products_asin_unique ON products (asin) WHERE deleted_at IS NULL AND asin IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX products_fsnku_unique ON products (fsnku) WHERE deleted_at IS NULL AND fsnku IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX products_sku_unique ON products (sku) WHERE deleted_at IS NULL AND sku IS NOT NULL');
    }

    public function down(): void
    {
        // Remove índices únicos parciais
        DB::statement('DROP INDEX IF EXISTS products_asin_unique');
        DB::statement('DROP INDEX IF EXISTS products_fsnku_unique');
        DB::statement('DROP INDEX IF EXISTS products_sku_unique');
    }
};