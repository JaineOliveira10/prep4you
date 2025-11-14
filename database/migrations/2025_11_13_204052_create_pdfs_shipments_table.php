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
        Schema::create('pdfs_shipments', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['individual_label', 'master_label', 'invoice']);
            $table->string('path_pdf')->nullable();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdfs_shipments');
    }
};
