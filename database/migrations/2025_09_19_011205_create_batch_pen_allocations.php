<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_pen_allocations', function (Blueprint $table) {
            $table->id();

            // FK ไปยัง batch
            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');

            // FK ไปยัง barn (เล้า)
            $table->unsignedBigInteger('barn_id')->nullable();
            $table->foreign('barn_id')->references('id')->on('barns')->onDelete('set null');

            // FK ไปยัง pen (คอก)
            $table->unsignedBigInteger('pen_id')->nullable();
            $table->foreign('pen_id')->references('id')->on('pens')->onDelete('set null');

            // จำนวนหมูที่จัดสรรในคอกนั้น
            $table->integer('pig_amount')->default(0);

            // วันที่ย้าย/วันที่บันทึก
            $table->date('move_date')->nullable();

            // หมายเหตุ
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_pen_allocations');
    }
};
