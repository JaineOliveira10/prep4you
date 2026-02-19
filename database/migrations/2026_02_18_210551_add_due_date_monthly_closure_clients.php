<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('monthly_closure_clients', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('total_net');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_closure_clients', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }
};
