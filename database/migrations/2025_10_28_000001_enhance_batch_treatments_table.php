<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batch_treatments', function (Blueprint $table) {
            // เพิ่มฟิลด์สำหรับระยะเวลารักษา
            $table->string('disease_name')->nullable()->comment('ชื่อโรค/ไข้ที่เล้า')->after('medicine_code');
            $table->date('treatment_start_date')->nullable()->comment('วันเริ่มให้ยา')->after('date');
            $table->date('treatment_end_date')->nullable()->comment('วันจบการให้ยา')->after('treatment_start_date');
            $table->unsignedSmallInteger('duration_days')->nullable()->comment('จำนวนวันรักษา')->after('treatment_end_date');
            
            // เพิ่มฟิลด์สำหรับปริมาณยารายวัน
            $table->decimal('first_day_dosage', 10, 2)->nullable()->comment('ปริมาณยาวันแรก')->after('duration_days');
            $table->decimal('daily_dosage', 10, 2)->nullable()->comment('ปริมาณยาต่อวัน (วันต่อมา)')->after('first_day_dosage');
            
            // เพิ่มฟิลด์สำหรับสถานะการรักษา (ฟิลด์ใหม่)
            $table->string('treatment_status')->default('pending')->comment('สถานะการรักษา: pending, ongoing, completed, stopped')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batch_treatments', function (Blueprint $table) {
            $table->dropColumn([
                'disease_name',
                'treatment_start_date',
                'treatment_end_date',
                'duration_days',
                'first_day_dosage',
                'daily_dosage',
            ]);
        });
    }
};
