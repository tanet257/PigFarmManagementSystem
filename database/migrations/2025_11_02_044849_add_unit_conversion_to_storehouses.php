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
        Schema::table('storehouses', function (Blueprint $table) {
            // เพิ่ม base_unit - หน่วยหลัก (เช่น ml, pieces)
            if (!Schema::hasColumn('storehouses', 'base_unit')) {
                $table->string('base_unit')->nullable()->comment('หน่วยหลัก (ml, pieces, etc)')->after('unit');
            }
            
            // เพิ่ม conversion_rate - อัตราการแปลงหน่วย (เช่น 1 ขวด = 500 ml)
            if (!Schema::hasColumn('storehouses', 'conversion_rate')) {
                $table->decimal('conversion_rate', 10, 4)->nullable()->comment('อัตราการแปลงหน่วย (base_unit ต่อ unit)')->after('base_unit');
            }
            
            // เพิ่ม quantity_per_unit - จำนวนต่อหน่วย (เช่น 1 ขวด = 500 ml)
            if (!Schema::hasColumn('storehouses', 'quantity_per_unit')) {
                $table->string('quantity_per_unit')->nullable()->comment('จำนวนในแต่ละหน่วย (เช่น 500 ml)')->after('conversion_rate');
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
        Schema::table('storehouses', function (Blueprint $table) {
            $table->dropColumn(['base_unit', 'conversion_rate', 'quantity_per_unit']);
        });
    }
};
