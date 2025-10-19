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
        Schema::table('pig_entry_records', function (Blueprint $table) {
            // ลบ columns ที่ซ้ำซาก (ควรเก็บใน costs table)
            if (Schema::hasColumn('pig_entry_records', 'receipt_file')) {
                $table->dropColumn('receipt_file');
            }
            if (Schema::hasColumn('pig_entry_records', 'transport_cost')) {
                $table->dropColumn('transport_cost');
            }
            if (Schema::hasColumn('pig_entry_records', 'excess_weight')) {
                $table->dropColumn('excess_weight');
            }
            if (Schema::hasColumn('pig_entry_records', 'excess_weight_cost')) {
                $table->dropColumn('excess_weight_cost');
            }
            if (Schema::hasColumn('pig_entry_records', 'price_per_pig')) {
                $table->dropColumn('price_per_pig');
            }

            // เพิ่ม columns สำหรับ average values (เหมือน batch)
            if (!Schema::hasColumn('pig_entry_records', 'average_weight_per_pig')) {
                $table->float('average_weight_per_pig')->nullable()->comment('ค่าเฉลี่ยน้ำหนักต่อตัว');
            }
            if (!Schema::hasColumn('pig_entry_records', 'average_price_per_pig')) {
                $table->float('average_price_per_pig')->nullable()->comment('ค่าเฉลี่ยราคาต่อตัว');
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
        Schema::table('pig_entry_records', function (Blueprint $table) {
            // คืนค่า columns ที่ลบ
            if (!Schema::hasColumn('pig_entry_records', 'receipt_file')) {
                $table->string('receipt_file')->nullable();
            }
            if (!Schema::hasColumn('pig_entry_records', 'transport_cost')) {
                $table->decimal('transport_cost', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('pig_entry_records', 'excess_weight')) {
                $table->float('excess_weight')->nullable();
            }
            if (!Schema::hasColumn('pig_entry_records', 'excess_weight_cost')) {
                $table->decimal('excess_weight_cost', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('pig_entry_records', 'price_per_pig')) {
                $table->float('price_per_pig')->nullable();
            }

            // ลบ average values
            if (Schema::hasColumn('pig_entry_records', 'average_weight_per_pig')) {
                $table->dropColumn('average_weight_per_pig');
            }
            if (Schema::hasColumn('pig_entry_records', 'average_price_per_pig')) {
                $table->dropColumn('average_price_per_pig');
            }
        });
    }
};
