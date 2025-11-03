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
            // เปลี่ยนชื่อ treatment_id เป็น batch_treatment_id
            $table->renameColumn('treatment_id', 'batch_treatment_id');
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
            // ย้อนกลับชื่อจาก batch_treatment_id เป็น treatment_id
            $table->renameColumn('batch_treatment_id', 'treatment_id');
        });
    }
};
