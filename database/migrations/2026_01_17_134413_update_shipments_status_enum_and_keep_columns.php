<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {

            // 1. Remover qualquer CHECK antigo que possa bloquear os novos valores
            DB::statement("ALTER TABLE shipments DROP CONSTRAINT IF EXISTS shipments_status_check");

            // 2. Criar novo tipo ENUM com todos os valores necessários
            DB::statement("
                CREATE TYPE shipment_status_new AS ENUM (
                    'Pending',
                    'In Preparation',
                    'Has Pendency',
                    'Packed',
                    'Collected',
                    'Invoice Generated',
                    'Paid'
                );
            ");

            // 3. Remover default temporário
            DB::statement("ALTER TABLE shipments ALTER COLUMN status DROP DEFAULT");

            // 4. Alterar coluna status para usar o novo ENUM
            DB::statement("
                ALTER TABLE shipments
                ALTER COLUMN status TYPE shipment_status_new
                USING CASE
                    WHEN TRIM(UPPER(status::text)) = 'HAS PENDENCY' THEN 'Has Pendency'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'IN PREPARATION' THEN 'In Preparation'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'PACKED' THEN 'Packed'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'COLLECTED' THEN 'Collected'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'INVOICE GENERATED' THEN 'Invoice Generated'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'PAID' THEN 'Paid'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'PENDING' THEN 'Pending'::shipment_status_new
                    ELSE 'Pending'::shipment_status_new
                END
            ");

            // 5. Remover tipo antigo e renomear o novo
            DB::statement("DROP TYPE IF EXISTS shipment_status");
            DB::statement("ALTER TYPE shipment_status_new RENAME TO shipment_status");

            // 6. Restaurar default
            DB::statement("ALTER TABLE shipments ALTER COLUMN status SET DEFAULT 'Pending'");
        }

        // 7. Manter as colunas extras caso não existam
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'pendency_reason')) {
                $table->text('pendency_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('shipments', 'collection_proof')) {
                $table->string('collection_proof')->nullable()->after('pendency_reason');
            }
        });

    }

    public function down(): void
    {

        if (DB::connection()->getDriverName() === 'pgsql') {
            // Voltar coluna status para VARCHAR e remover ENUM
            DB::statement("ALTER TABLE shipments ALTER COLUMN status DROP DEFAULT");
            DB::statement("ALTER TABLE shipments ALTER COLUMN status TYPE VARCHAR(255)");
            DB::statement("DROP TYPE IF EXISTS shipment_status");
        }
    }
};
