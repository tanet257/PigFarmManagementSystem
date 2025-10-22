<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add soft delete support to pig_entry_records table
     */
    public function up(): void
    {
        Schema::table('pig_entry_records', function (Blueprint $table) {
            // Add soft delete columns
            $table->string('status')->default('active')->after('total_cost')->comment('active, cancelled');
            $table->string('cancellation_reason')->nullable()->after('status')->comment('เหตุผลการยกเลิก');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason')->comment('เวลาที่ยกเลิก');
            $table->string('cancelled_by')->nullable()->after('cancelled_at')->comment('ใครยกเลิก');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pig_entry_records', function (Blueprint $table) {
            $table->dropColumn(['status', 'cancellation_reason', 'cancelled_at', 'cancelled_by']);
        });
    }
};
