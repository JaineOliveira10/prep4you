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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('asin', 15)->unique()->nullable();
            $table->string('fsnku', 15)->unique()->nullable();
            $table->string('sku', 40)->unique()->nullable();
            $table->string('photo_path')->nullable();
            $table->string('observation', 200)->nullable();
            $table->enum('type', ['simple', 'kit', 'super_kit'])->default('simple');
            $table->integer('kit_units')->nullable(); // número de unidades em kits/super kits
            $table->decimal('unit_price', 10, 2)->nullable(); // apenas para super kits
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
