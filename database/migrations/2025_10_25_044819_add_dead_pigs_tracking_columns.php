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
        // เพิ่มคอลัมน์สำหรับติดตาม สะสมหมูตายที่ขายไปแล้ว
        Schema::table('pig_deaths', function (Blueprint $table) {
            // ✅ quantity_sold_total: จำนวนสะสมที่ขายไปแล้ว (ไม่เปลี่ยนแปลง)
            if (!Schema::hasColumn('pig_deaths', 'quantity_sold_total')) {
                $table->integer('quantity_sold_total')->default(0)->comment('จำนวนหมูตายที่ขายไปแล้ว - สะสม');
            }

            // ✅ price_per_pig: ราคาต่อตัว (สำหรับคำนวณ revenue)
            if (!Schema::hasColumn('pig_deaths', 'price_per_pig')) {
                $table->decimal('price_per_pig', 10, 2)->nullable()->comment('ราคาต่อตัว ที่ขายหมูตายไป');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pig_deaths', function (Blueprint $table) {
            $table->dropColumn(['quantity_sold_total', 'price_per_pig']);
        });
    }
};
