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
        Schema::table('inventory_movements', function (Blueprint $table) {
            // เพิ่ม batch_treatment_id - เชื่อมกับ batch_treatments
            $table->unsignedBigInteger('batch_treatment_id')->nullable()->comment('เชื่อมกับ batch_treatments')->after('cost_id');
            $table->foreign('batch_treatment_id')->references('id')->on('batch_treatments')->onDelete('set null');

            // เพิ่ม quantity_unit - หน่วยของ quantity (ml, ขวด, ถัง เป็นต้น)
            $table->string('quantity_unit')->nullable()->comment('หน่วยของ quantity (ml, ขวด, ถัง)')->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['treatment_id']);
            $table->dropColumn(['treatment_id', 'quantity_unit']);
        });
    }
};
