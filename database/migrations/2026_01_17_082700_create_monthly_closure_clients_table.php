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
        Schema::create('monthly_closure_clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('closure_id')
                ->constrained('monthly_closures')
                ->onDelete('cascade');

            $table->foreignId('client_id')
                ->constrained('clients')
                ->onDelete('cascade');

            $table->integer('total_simple_labels')->default(0);
            $table->integer('total_kit_labels')->default(0);
            $table->integer('total_superkit_labels')->default(0);

            $table->decimal('unit_price_simple', 10, 2)->nullable();
            $table->decimal('unit_price_kit', 10, 2)->nullable();

            $table->decimal('total_simple_value', 10, 2)->default(0);
            $table->decimal('total_kit_value', 10, 2)->default(0);
            $table->decimal('total_superkit_value', 10, 2)->default(0);

            $table->decimal('total_gross', 10, 2)->default(0);
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->decimal('total_net', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_closure_clients');
    }
};
