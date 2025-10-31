<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove constraints únicos existentes
        Schema::table('distribution_centers', function (Blueprint $table) {
            $table->dropUnique(['acronym']);
        });
        
        Schema::table('price_tables', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
        
        // Cria índices únicos parciais que ignoram soft deletes
        DB::statement('CREATE UNIQUE INDEX distribution_centers_acronym_unique ON distribution_centers (acronym) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX price_tables_name_unique ON price_tables (name) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        // Remove índices únicos parciais
        DB::statement('DROP INDEX IF EXISTS distribution_centers_acronym_unique');
        DB::statement('DROP INDEX IF EXISTS price_tables_name_unique');
        
        // Restaura constraints únicos originais
        Schema::table('distribution_centers', function (Blueprint $table) {
            $table->unique('acronym');
        });
        
        Schema::table('price_tables', function (Blueprint $table) {
            $table->unique('name');
        });
    }
};