<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });
        
        // Criar índice único que permite emails duplicados quando deleted_at não é null
        DB::statement('CREATE UNIQUE INDEX users_email_unique_not_deleted ON users (email) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_email_unique_not_deleted');
        
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};