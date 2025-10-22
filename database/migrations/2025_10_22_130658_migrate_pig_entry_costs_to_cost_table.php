<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ย้าย PigEntry piglet costs → Cost table + CostPayment table
        DB::statement('
            INSERT INTO costs (
                farm_id,
                batch_id,
                pig_entry_record_id,
                cost_type,
                total_price,
                payment_status,
                paid_date,
                date,
                created_at,
                updated_at
            )
            SELECT
                farm_id,
                batch_id,
                id as pig_entry_record_id,
                "piglet" as cost_type,
                total_cost as total_price,
                CASE
                    WHEN payment_status = "approved" THEN "paid"
                    ELSE payment_status
                END as payment_status,
                paid_date,
                pig_entry_date as date,
                created_at,
                updated_at
            FROM pig_entry_records
            WHERE total_cost > 0 AND status != "cancelled"
        ');

        // สร้าง CostPayment records สำหรับ approved payments
        DB::statement('
            INSERT INTO cost_payments (
                cost_id,
                amount,
                status,
                approved_date,
                action_type,
                created_at,
                updated_at
            )
            SELECT
                c.id,
                c.total_price as amount,
                "approved" as status,
                COALESCE(per.paid_date, NOW()) as approved_date,
                "approved" as action_type,
                NOW(),
                NOW()
            FROM costs c
            JOIN pig_entry_records per ON c.pig_entry_record_id = per.id
            WHERE c.cost_type = "piglet"
            AND per.payment_status = "approved"
            AND per.status != "cancelled"
        ');
    }

    public function down()
    {
        // ลบ piglet costs ที่ถูกย้ายไป
        DB::statement('
            DELETE FROM cost_payments
            WHERE cost_id IN (
                SELECT id FROM costs WHERE cost_type = "piglet"
            )
        ');

        DB::statement('
            DELETE FROM costs WHERE cost_type = "piglet"
        ');
    }
};
