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
        Schema::create('pig_sell_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pig_sell_id');
            $table->unsignedBigInteger('pen_id');
            $table->integer('quantity'); // จำนวนหมูที่ขายจากคอกนี้
            $table->timestamps();

            // Foreign keys
            $table->foreign('pig_sell_id')->references('id')->on('pig_sells')->onDelete('cascade');
            $table->foreign('pen_id')->references('id')->on('pens')->onDelete('cascade');

            // Index สำหรับ query
            $table->index(['pig_sell_id', 'pen_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pig_sell_details');
    }
};
