<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_treatment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_treatment_id');
            $table->date('treatment_date'); // วันที่บันทึก
            $table->decimal('quantity_given', 10, 2); // จำนวนยาที่ให้
            $table->string('unit')->nullable(); // หน่วย (มล., กรัม, ขวด)
            $table->text('note')->nullable(); // หมายเหตุ (อาการหมู, ข้อสังเกต)
            $table->unsignedBigInteger('recorded_by')->nullable(); // ผู้บันทึก
            $table->timestamps();

            // Foreign keys
            $table->foreign('batch_treatment_id')
                ->references('id')
                ->on('batch_treatments')
                ->onDelete('cascade');

            $table->foreign('recorded_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes
            $table->index('batch_treatment_id');
            $table->index('treatment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_treatment_logs');
    }
};
