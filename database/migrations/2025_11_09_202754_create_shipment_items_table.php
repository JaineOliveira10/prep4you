<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name', 50);
            $table->string('fsnku', 15)->unique()->nullable();
            $table->string('sku', 40)->nullable();
            $table->enum('type', ['simple', 'kit', 'super_kit'])->default('simple');
            $table->integer('kit_units')->nullable();
            $table->integer('quantity'); 
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_value', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};