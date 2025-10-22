<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Consolidated migration for 2025-10-22 changes
     *
     * This migration combines:
     * 1. Change batch status from enum to varchar (to support 'cancelled')
     * 2. Add cancellation fields to users table
     * 3. Clean up unapproved sales revenue and recalculate profits
     */
    public function up(): void
    {
        // ===== 1. Change batch status from enum to varchar =====
        Schema::table('batches', function (Blueprint $table) {
            // Change status from enum to varchar to support 'cancelled' status
            $table->string('status')->change();
        });

        // ===== 2. Add cancellation fields to users table =====
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't already exist
            if (!Schema::hasColumn('users', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('rejection_reason')->comment('เหตุผลการขอยกเลิก');
            }
            if (!Schema::hasColumn('users', 'cancellation_requested_at')) {
                $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_reason')->comment('เวลาที่ขอยกเลิก');
            }
        });

        // ===== 3. Clean up unapproved sales revenue =====
        // ลบ Revenue records ที่เกี่ยวข้องกับ PigSale ที่ยังไม่ได้อนุมัติ
        DB::statement('
            DELETE FROM revenues
            WHERE pig_sale_id IS NOT NULL
            AND pig_sale_id IN (
                SELECT id FROM pig_sales
                WHERE approved_at IS NULL
            )
        ');

        // ===== 4. Recalculate profits =====
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
        // ===== Reverse: Remove cancellation fields from users =====
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancellation_requested_at']);
        });

        // ===== Reverse: Change batch status back to enum =====
        Schema::table('batches', function (Blueprint $table) {
            // Revert back to enum (you may need to adjust the enum values)
            $table->enum('status', ['กำลังเลี้ยง', 'เสร็จสิ้น'])->change();
        });

        // Note: Revenue data cleanup and profit recalculation cannot be reversed
        // as data was permanently deleted/modified. This is intentional.
    }
};
