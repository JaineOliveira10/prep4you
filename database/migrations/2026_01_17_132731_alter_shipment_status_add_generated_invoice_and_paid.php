<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         DB::statement("
            ALTER TYPE shipment_status ADD VALUE IF NOT EXISTS 'Generated Invoice';
        ");

        DB::statement("
            ALTER TYPE shipment_status ADD VALUE IF NOT EXISTS 'Paid';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
