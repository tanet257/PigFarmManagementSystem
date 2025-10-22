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
        Schema::table('batches', function (Blueprint $table) {
            // Change status from enum to varchar to support 'cancelled' status
            $table->string('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batches', function (Blueprint $table) {
            // Revert back to enum (you may need to adjust the enum values)
            $table->enum('status', ['กำลังเลี้ยง', 'เสร็จสิ้น'])->change();
        });
    }
};
