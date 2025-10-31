<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('batch_treatments', function (Blueprint $table) {
            // ลบ columns ที่ไม่จำเป็นแล้ว
            $table->dropColumn([
                'treatment_end_date',      // ไม่ต้องเก็บ เพราะได้จาก daily_treatment_logs
                'duration_days',           // คำนวณจากวันที่เริ่มจนสิ้นสุด
                'first_day_dosage',        // ไม่ต้องเก็บ เหมือนการให้ยา
                'daily_dosage',            // ไม่จำเป็น เพราะเป็นรายวัน
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_treatments', function (Blueprint $table) {
            // เพิ่มกลับคืน
            $table->date('treatment_end_date')->nullable();
            $table->integer('duration_days')->nullable();
            $table->decimal('first_day_dosage', 10, 2)->nullable();
            $table->decimal('daily_dosage', 10, 2)->nullable();
        });
    }
};
