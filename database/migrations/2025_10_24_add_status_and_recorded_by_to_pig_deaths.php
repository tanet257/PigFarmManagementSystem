<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * เพิ่ม status (pending/recorded) และ recorded_by (user_id) เพื่อติดตามว่าใครบันทึกและสถานะ
     */
    public function up()
    {
        if (Schema::hasTable('pig_deaths')) {
            Schema::table('pig_deaths', function (Blueprint $table) {
                // ✅ Status: 'recorded' (บันทึกแล้ว) | 'sold' (ขายไปแล้ว) | 'disposed' (กำจัดแล้ว)
                if (!Schema::hasColumn('pig_deaths', 'status')) {
                    $table->string('status')->default('recorded')->after('date'); // recorded, sold, disposed
                }

                // ✅ ใครที่บันทึก (admin user_id)
                if (!Schema::hasColumn('pig_deaths', 'recorded_by')) {
                    $table->unsignedBigInteger('recorded_by')->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('pig_deaths')) {
            Schema::table('pig_deaths', function (Blueprint $table) {
                $table->dropColumn(['status', 'recorded_by']);
            });
        }
    }
};
