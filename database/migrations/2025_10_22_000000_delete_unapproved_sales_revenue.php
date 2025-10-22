<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ลบ Revenue records ที่เกี่ยวข้องกับ PigSale ที่ยังไม่ได้อนุมัติ
        DB::statement('
            DELETE FROM revenues
            WHERE pig_sale_id IS NOT NULL
            AND pig_sale_id IN (
                SELECT id FROM pig_sales
                WHERE approved_at IS NULL
            )
        ');

        // อัปเดต Profit records - ทำให้ status = "incomplete" เพื่อให้ recalculate
        DB::statement('
            UPDATE profits
            SET status = "incomplete", total_revenue = 0
            WHERE total_revenue > 0
            AND batch_id IN (
                SELECT id FROM batches WHERE status != "cancelled"
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ไม่ทำการ restore เพราะเป็นการทำความสะอาดข้อมูลเก่า
        //
    }
};
