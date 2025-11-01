<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ===========================================
 * Migration: Update batch_treatments Table
 * ===========================================
 *
 * สำหรับอัปเดท schema ของ batch_treatments table
 * ให้เหมาะสมกับหน้า treatments ที่ใหม่
 *
 * Changes:
 * 1. เพิ่ม treatment_level (herd, pen)
 * 2. เพิ่ม farm_id เพื่อให้ query ได้ง่าย
 * 3. เปลี่ยน disease_name เป็น disease และ medicine_name เป็น medicine_code
 * 4. เพิ่ม dosage, frequency, withdrawal_period
 * 5. เพิ่ม planned_start_date, actual_start_date, planned_duration, actual_end_date
 * 6. ลบ treatment_start_date (ใช้ planned_start_date แทน)
 * 7. เพิ่ม effective_date สำหรับบันทึกวันที่ใช้จริง
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('batch_treatments', function (Blueprint $table) {
            // ======================== เพิ่ม Fields ใหม่ ========================

            // ✅ ระดับการรักษา: herd (เล้า) หรือ pen (คอก)
            $table->string('treatment_level')->after('id')->nullable()->comment('herd=ระดับเล้า, pen=ระดับคอก');

            // ✅ Farm ID สำหรับ query ได้ง่าย
            $table->unsignedBigInteger('farm_id')->after('batch_id')->nullable();
            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('set null');

            // ✅ โรค/อาการ (ใช้ disease_name ที่มีอยู่แล้ว แต่เพิ่ม index)
            // $table->string('disease_name')->change(); // ไม่ต้องเปลี่ยน เพราะมีอยู่แล้ว

            // ✅ ยา/วัคซีน (ใช้ medicine_name และ medicine_code ที่มี)
            // ไม่ต้องเพิ่ม เพราะมีอยู่แล้ว

            // ✅ ขนาดยา
            $table->decimal('dosage', 8, 2)->after('quantity')->nullable()->comment('ขนาดยา/ตัว (มล.)');

            // ✅ ความถี่ในการให้ยา
            $table->string('frequency')->after('dosage')->nullable()
                ->comment('once, daily, twice_daily, every_other_day, weekly, custom');

            // ✅ ระยะงดเว้น
            $table->integer('withdrawal_period')->after('frequency')->nullable()
                ->comment('ระยะเวลางดเว้นก่อนจำหน่าย (วัน)');

            // ✅ วันที่เริ่มตามแผน
            $table->date('planned_start_date')->after('date')->nullable()
                ->comment('วันที่เริ่มตามแผน');

            // ✅ วันที่เริ่มจริง
            $table->date('actual_start_date')->after('planned_start_date')->nullable()
                ->comment('วันที่เริ่มจริง');

            // ✅ ระยะเวลาตามแผน (วัน)
            $table->integer('planned_duration')->after('actual_start_date')->nullable()
                ->comment('ระยะเวลาตามแผน (วัน)');

            // ✅ วันที่สิ้นสุดจริง (จะ auto-update จาก status เมื่อเปลี่ยนเป็น completed/stopped)
            $table->date('actual_end_date')->after('planned_duration')->nullable()
                ->comment('วันที่สิ้นสุดจริง (auto-update จาก status)');

            // ✅ วันที่บันทึก (เมื่อใดก็ตามที่บันทึก)
            $table->timestamp('effective_date')->after('actual_end_date')->nullable()
                ->comment('วันเวลาที่บันทึกจริง');

            // ✅ เอกสารแนบ (URL หรือ path)
            $table->text('attachment_url')->after('effective_date')->nullable()
                ->comment('URL หรือ path ของเอกสารแนบ');
        });
    }

    public function down()
    {
        Schema::table('batch_treatments', function (Blueprint $table) {
            // Drop foreign key ก่อน
            $table->dropForeign(['farm_id']);

            // Drop columns ในลำดับที่เหมาะสม
            $table->dropColumn([
                'treatment_level',
                'farm_id',
                'dosage',
                'frequency',
                'withdrawal_period',
                'planned_start_date',
                'actual_start_date',
                'planned_duration',
                'actual_end_date',
                'effective_date',
                'attachment_url'
            ]);
        });
    }
};
