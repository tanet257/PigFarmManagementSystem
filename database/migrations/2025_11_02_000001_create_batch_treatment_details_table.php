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
        Schema::create('batch_treatment_details', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->unsignedBigInteger('batch_treatment_id');
            $table->unsignedBigInteger('pen_id')->nullable();
            $table->unsignedBigInteger('barn_id')->nullable();
            
            // Treatment Application Info
            $table->date('treatment_date')->comment('วันที่ให้ยา');
            $table->decimal('quantity_used', 8, 2)->comment('จำนวนที่ใช้จริง');
            $table->string('unit')->comment('หน่วย (ml, ตัว, ถัง ฯลฯ)');
            
            // Notes & Status
            $table->text('note')->nullable()->comment('หมายเหตุการให้ยา');
            $table->string('applied_by')->nullable()->comment('ชื่อผู้ให้ยา');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            // Foreign Keys
            $table->foreign('batch_treatment_id')->references('id')->on('batch_treatments')->onDelete('cascade');
            $table->foreign('pen_id')->references('id')->on('pens')->onDelete('set null');
            $table->foreign('barn_id')->references('id')->on('barns')->onDelete('set null');
            
            // Indexes
            $table->index('batch_treatment_id');
            $table->index('pen_id');
            $table->index('barn_id');
            $table->index('treatment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_treatment_details');
    }
};
