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
            // เพิ่ม quantity - จำนวนที่ใช้ (ในหน่วย unit)
            if (!Schema::hasColumn('batch_treatments', 'quantity')) {
                $table->decimal('quantity', 10, 4)->nullable()->comment('จำนวนที่ใช้ (เช่น 20 ml)')->after('medicine_name');
            }

            // เพิ่ม unit - หน่วยการใช้ (ml, pieces, etc)
            if (!Schema::hasColumn('batch_treatments', 'unit')) {
                $table->string('unit')->nullable()->comment('หน่วยการใช้ (ml, pieces, etc)')->after('quantity');
            }

            // เพิ่ม frequency - ความถี่ในการให้ (เช่น 2 ครั้งต่อวัน)
            if (!Schema::hasColumn('batch_treatments', 'frequency')) {
                $table->integer('frequency')->nullable()->comment('ความถี่ต่อวัน')->after('unit');
            }

            // เพิ่ม actual_start_date - วันที่เริ่มรักษาจริง
            if (!Schema::hasColumn('batch_treatments', 'actual_start_date')) {
                $table->date('actual_start_date')->nullable()->comment('วันที่เริ่มรักษาจริง')->after('status');
            }

            // เพิ่ม actual_end_date - วันที่สิ้นสุดรักษาจริง
            if (!Schema::hasColumn('batch_treatments', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable()->comment('วันที่สิ้นสุดรักษาจริง')->after('actual_start_date');
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
        Schema::table('batch_treatments', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit', 'frequency', 'actual_start_date', 'actual_end_date']);
        });
    }
};
