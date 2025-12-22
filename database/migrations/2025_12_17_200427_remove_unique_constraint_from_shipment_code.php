<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se a constraint existe antes de dropar
        $constraintExists = DB::select(
            "SELECT constraint_name FROM information_schema.table_constraints 
             WHERE table_name = 'shipments' 
             AND constraint_name = 'shipments_shipment_code_unique'"
        );

        if (!empty($constraintExists)) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropUnique(['shipment_code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->unique(['shipment_code']);
        });
    }
};
