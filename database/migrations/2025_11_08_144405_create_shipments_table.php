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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->date('creation_date')->default(now());
            $table->date('shipment_date');
            $table->date('collection_date');
            $table->enum('status', [
                'Pending',
                'In Preparation',
                'Packed',
                'Collected',
                'Invoice Generated',
                'Paid'
            ])->default('Pending');
            $table->string('name')->nullable();
            $table->decimal('total_value', 10, 2);
            $table->integer('total_items');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('distribution_center_id')->constrained('distribution_centers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
