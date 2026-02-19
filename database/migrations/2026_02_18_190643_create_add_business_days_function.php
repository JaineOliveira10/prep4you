<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(<<<SQL
        CREATE OR REPLACE FUNCTION add_business_days(start_date DATE, days INT)
        RETURNS DATE AS $$
        DECLARE
            result_date DATE := start_date;
            business_days_added INT := 0;
            max_iterations INT := days * 2 + 10;
            iterations INT := 0;
        BEGIN
            IF days <= 0 THEN
                RETURN start_date;
            END IF;
            
            WHILE business_days_added < days AND iterations < max_iterations LOOP
                result_date := result_date + INTERVAL '1 day';
                -- DOW: 0=Sunday, 6=Saturday
                IF EXTRACT(DOW FROM result_date) NOT IN (0, 6) THEN
                    business_days_added := business_days_added + 1;
                    IF business_days_added = days THEN
                        RETURN result_date;
                    END IF;
                END IF;
                iterations := iterations + 1;
            END LOOP;
            
            RETURN result_date;
        END;
        $$ LANGUAGE plpgsql IMMUTABLE;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS add_business_days(DATE, INT);");
    }
};
