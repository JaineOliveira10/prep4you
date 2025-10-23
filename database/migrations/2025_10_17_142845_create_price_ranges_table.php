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
        Schema::create('price_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_table_id')->constrained('price_tables')->onDelete('cascade');
            $table->decimal('min_value', 10, 2);
            $table->decimal('max_value', 10, 2)->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('price_kit', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_ranges');
    }
};
