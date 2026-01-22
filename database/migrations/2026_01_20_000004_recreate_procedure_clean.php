<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS close_client_month(INT, INT, INT);");

        DB::unprepared(<<<SQL

        CREATE PROCEDURE close_client_month(
            p_client_id INT,
            p_year INT,
            p_month INT
        )
        LANGUAGE plpgsql
        AS $$
        DECLARE
            v_closure_id INT;
            v_closure_client_id INT;

            v_total_simple INT := 0;
            v_total_kit INT := 0;
            v_total_superkit_labels INT := 0;
            v_total_superkit NUMERIC(10,2) := 0;
            v_total_labels INT := 0;

            v_unit_simple NUMERIC(10,2);
            v_unit_kit NUMERIC(10,2);

            v_total_simple_value NUMERIC(10,2) := 0;
            v_total_kit_value NUMERIC(10,2) := 0;
            v_total_original_labels NUMERIC(10,2) := 0;
            v_total_liquid_labels NUMERIC(10,2) := 0;

            v_total_simple_net NUMERIC(10,2) := 0;
            v_total_kit_net NUMERIC(10,2) := 0;
            v_total_discount_simple NUMERIC(10,2) := 0;
            v_total_discount_kit NUMERIC(10,2) := 0;

            v_total_gross NUMERIC(10,2) := 0;
            v_total_net NUMERIC(10,2) := 0;
            v_total_discount NUMERIC(10,2) := 0;
        BEGIN

            INSERT INTO monthly_closures (year, month)
            VALUES (p_year, p_month)
            ON CONFLICT (year, month) DO NOTHING;

            SELECT id INTO v_closure_id
            FROM monthly_closures
            WHERE year=p_year AND month=p_month;

            DELETE FROM monthly_closure_clients
            WHERE closure_id=v_closure_id AND client_id=p_client_id;

            SELECT
                SUM(CASE WHEN si.type='simple' THEN si.quantity ELSE 0 END),
                SUM(CASE WHEN si.type='kit' THEN si.quantity ELSE 0 END)
            INTO v_total_simple, v_total_kit
            FROM shipment_items si
            JOIN shipments s ON s.id = si.shipment_id
            WHERE s.client_id=p_client_id
            AND EXTRACT(YEAR FROM s.creation_date)=p_year
            AND EXTRACT(MONTH FROM s.creation_date)=p_month;

            v_total_labels := v_total_simple + v_total_kit;

            SELECT COALESCE(SUM(si.quantity),0)
            INTO v_total_superkit_labels
            FROM shipment_items si
            JOIN shipments s ON s.id = si.shipment_id
            WHERE s.client_id=p_client_id
            AND si.type='super_kit'
            AND EXTRACT(YEAR FROM s.creation_date)=p_year
            AND EXTRACT(MONTH FROM s.creation_date)=p_month;

            SELECT COALESCE(SUM(si.total_value),0)
            INTO v_total_superkit
            FROM shipment_items si
            JOIN shipments s ON s.id = si.shipment_id
            WHERE s.client_id=p_client_id
            AND si.type='super_kit'
            AND EXTRACT(YEAR FROM s.creation_date)=p_year
            AND EXTRACT(MONTH FROM s.creation_date)=p_month;

            SELECT COALESCE(SUM(si.total_value),0)
            INTO v_total_simple_value
            FROM shipment_items si
            JOIN shipments s ON s.id = si.shipment_id
            WHERE s.client_id=p_client_id
            AND si.type='simple'
            AND EXTRACT(YEAR FROM s.creation_date)=p_year
            AND EXTRACT(MONTH FROM s.creation_date)=p_month;

            SELECT COALESCE(SUM(si.total_value),0)
            INTO v_total_kit_value
            FROM shipment_items si
            JOIN shipments s ON s.id = si.shipment_id
            WHERE s.client_id=p_client_id
            AND si.type='kit'
            AND EXTRACT(YEAR FROM s.creation_date)=p_year
            AND EXTRACT(MONTH FROM s.creation_date)=p_month;

            SELECT COALESCE(SUM(si.total_value),0)
            INTO v_total_original_labels
            FROM shipment_items si
            JOIN shipments s ON s.id = si.shipment_id
            WHERE s.client_id=p_client_id
            AND si.type IN ('simple','kit')
            AND EXTRACT(YEAR FROM s.creation_date)=p_year
            AND EXTRACT(MONTH FROM s.creation_date)=p_month;

            SELECT pr.price, pr.price_kit
            INTO v_unit_simple, v_unit_kit
            FROM price_ranges pr
            JOIN clients c ON c.price_table_id = pr.price_table_id
            WHERE c.id = p_client_id
            AND v_total_labels BETWEEN pr.min_value AND pr.max_value
            LIMIT 1;

            -- Calcular brutos com base na quantidade e preço
            v_total_simple_net := v_total_simple * v_unit_simple;
            v_total_kit_net := v_total_kit * v_unit_kit;

            -- Calcular descontos: bruto - líquido
            v_total_discount_simple := v_total_simple_net - v_total_simple_value;
            v_total_discount_kit := v_total_kit_net - v_total_kit_value;

            v_total_liquid_labels := v_total_simple_value + v_total_kit_value;

            v_total_gross := v_total_original_labels + v_total_superkit;
            v_total_net := v_total_liquid_labels + v_total_superkit;
            v_total_discount := v_total_original_labels - v_total_liquid_labels;

            INSERT INTO monthly_closure_clients (
                closure_id,
                client_id,
                total_simple_labels,
                total_kit_labels,
                total_superkit_labels,
                unit_price_simple,
                unit_price_kit,
                total_simple_value,
                total_kit_value,
                total_superkit_value,
                total_simple_net,
                total_kit_net,
                total_gross,
                total_discount,
                total_discount_simple,
                total_discount_kit,
                total_net,
                created_at,
                updated_at
            )
            VALUES (
                v_closure_id,
                p_client_id,
                v_total_simple,
                v_total_kit,
                v_total_superkit_labels,
                v_unit_simple,
                v_unit_kit,
                v_total_simple_value,
                v_total_kit_value,
                v_total_superkit,
                v_total_simple_net,
                v_total_kit_net,
                v_total_gross,
                v_total_discount,
                v_total_discount_simple,
                v_total_discount_kit,
                v_total_net,
                NOW(),
                NOW()
            );

            SELECT id INTO v_closure_client_id
            FROM monthly_closure_clients
            WHERE closure_id = v_closure_id
            AND client_id = p_client_id
            ORDER BY id DESC
            LIMIT 1;

            DELETE FROM monthly_closure_shipments
            WHERE closure_client_id = v_closure_client_id;

            INSERT INTO monthly_closure_shipments (closure_client_id, shipment_id, created_at, updated_at)
            SELECT
                v_closure_client_id,
                s.id,
                NOW(),
                NOW()
            FROM shipments s
            WHERE s.client_id = p_client_id
            AND EXTRACT(YEAR FROM s.creation_date) = p_year
            AND EXTRACT(MONTH FROM s.creation_date) = p_month;

            UPDATE shipments s
            SET status = 'Invoice Generated'
            WHERE s.id IN (
                SELECT shipment_id
                FROM monthly_closure_shipments
                WHERE closure_client_id = v_closure_client_id
            );

        END;
        $$;

        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS close_client_month(INT, INT, INT);");
    }
};
