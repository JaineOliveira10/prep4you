<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Criar o tipo ENUM caso não exista
        DB::statement("
            DO $$ BEGIN
                CREATE TYPE shipment_status AS ENUM (
                    'Pending',
                    'In Preparation',
                    'Packed',
                    'Collected',
                    'Invoice Generated',
                    'Paid',
                    'Presents Errors'
                );
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        // Normalizar valores existentes antes de converter
        DB::statement("
            UPDATE shipments 
            SET status = TRIM(UPPER(status))
            WHERE status IS NOT NULL
        ");

        // Remover default temporariamente
        DB::statement("ALTER TABLE shipments ALTER COLUMN status DROP DEFAULT");

        // Alterar a coluna para ENUM com tratamento de valores inválidos
        DB::statement("
            ALTER TABLE shipments
            ALTER COLUMN status TYPE shipment_status
            USING CASE 
                WHEN TRIM(UPPER(status)) = 'PENDING' THEN 'Pending'::shipment_status
                WHEN TRIM(UPPER(status)) = 'IN PREPARATION' THEN 'In Preparation'::shipment_status
                WHEN TRIM(UPPER(status)) = 'PACKED' THEN 'Packed'::shipment_status
                WHEN TRIM(UPPER(status)) = 'COLLECTED' THEN 'Collected'::shipment_status
                WHEN TRIM(UPPER(status)) = 'INVOICE GENERATED' THEN 'Invoice Generated'::shipment_status
                WHEN TRIM(UPPER(status)) = 'PAID' THEN 'Paid'::shipment_status
                WHEN TRIM(UPPER(status)) = 'PRESENTS ERRORS' THEN 'Presents Errors'::shipment_status
                ELSE 'Pending'::shipment_status
            END
        ");

        // Restaurar default
        DB::statement("
            ALTER TABLE shipments ALTER COLUMN status SET DEFAULT 'Pending'
        ");
    }

    public function down(): void
    {
        // Remover default
        DB::statement("ALTER TABLE shipments ALTER COLUMN status DROP DEFAULT");

        // Voltar a VARCHAR
        DB::statement("ALTER TABLE shipments ALTER COLUMN status TYPE VARCHAR(255)");

        // Remover o tipo ENUM
        DB::statement("DROP TYPE IF EXISTS shipment_status");
    }

};