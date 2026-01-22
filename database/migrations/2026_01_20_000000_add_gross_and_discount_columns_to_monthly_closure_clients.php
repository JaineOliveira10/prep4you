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
            $table->decimal('total_simple_net', 10, 2)->default(0)->after('total_simple_value');
            $table->decimal('total_kit_net', 10, 2)->default(0)->after('total_kit_value');
            $table->decimal('total_discount_simple', 10, 2)->default(0)->after('total_discount');
            $table->decimal('total_discount_kit', 10, 2)->default(0)->after('total_discount_simple');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_closure_clients', function (Blueprint $table) {
            $table->dropColumn('total_simple_gross');
            $table->dropColumn('total_kit_gross');
            $table->dropColumn('total_discount_simple');
            $table->dropColumn('total_discount_kit');
        });
    }
};
