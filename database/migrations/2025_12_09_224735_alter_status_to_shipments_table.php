<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {

            DB::statement("
                CREATE TYPE shipment_status_new AS ENUM (
                    'Pending',
                    'In Preparation',
                    'Has Pendency',
                    'Packed',
                    'Collected'
                );
            ");

            DB::statement("ALTER TABLE shipments ALTER COLUMN status DROP DEFAULT");

            DB::statement("
                ALTER TABLE shipments
                ALTER COLUMN status TYPE shipment_status_new
                USING CASE
                    WHEN TRIM(UPPER(status::text)) = 'HAS PENDENCY' THEN 'Has Pendency'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'IN PREPARATION' THEN 'In Preparation'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'PACKED' THEN 'Packed'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'COLLECTED' THEN 'Collected'::shipment_status_new
                    WHEN TRIM(UPPER(status::text)) = 'PENDING' THEN 'Pending'::shipment_status_new
                    ELSE 'Pending'::shipment_status_new
                END
            ");

            DB::statement("DROP TYPE shipment_status");

            DB::statement("ALTER TYPE shipment_status_new RENAME TO shipment_status");

            DB::statement("
                ALTER TABLE shipments
                ALTER COLUMN status SET DEFAULT 'Pending'
            ");
        }

        Schema::table('shipments', function (Blueprint $table) {
            $table->text('pendency_reason')->nullable()->after('status');
            $table->string('collection_proof')->nullable()->after('pendency_reason');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['pendency_reason', 'collection_proof']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE shipments ALTER COLUMN status DROP DEFAULT");
            DB::statement("ALTER TABLE shipments ALTER COLUMN status TYPE VARCHAR(255)");
            DB::statement("DROP TYPE IF EXISTS shipment_status");
        }
    }
};
