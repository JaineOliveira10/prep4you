<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {

            // Remover o check antigo
            DB::statement('ALTER TABLE shipments DROP CONSTRAINT IF EXISTS shipments_status_check;');

            // (Opcional) recriar check com todos valores corretos
            DB::statement("
                ALTER TABLE shipments
                ADD CONSTRAINT shipments_status_check
                CHECK (status::text IN (
                    'Pending',
                    'In Preparation',
                    'Packed',
                    'Collected',
                    'Invoice Generated',
                    'Paid',
                    'Has Pendency'
                ));
            ");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {

            // Remover o check criado
            DB::statement('ALTER TABLE shipments DROP CONSTRAINT IF EXISTS shipments_status_check;');

            // Restaurar apenas os valores antigos (rollback)
            DB::statement("
                ALTER TABLE shipments
                ADD CONSTRAINT shipments_status_check
                CHECK (status::text IN (
                    'Pending',
                    'In Preparation',
                    'Packed',
                    'Collected',
                    'Invoice Generated',
                    'Paid'
                ));
            ");
        }
    }
};
