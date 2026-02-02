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
        Schema::table('monthly_closure_clients', function (Blueprint $table) {
            $table->boolean('paid_flag')->default(false)->after('client_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_closure_clients', function (Blueprint $table) {
            $table->dropColumn(['paid_flag']);
        });
    }
};
