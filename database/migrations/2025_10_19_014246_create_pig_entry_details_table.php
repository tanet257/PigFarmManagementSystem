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
        Schema::create('pig_entry_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pig_entry_id')->constrained('pig_entry_records')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('barn_id')->constrained('barns')->cascadeOnDelete();
            $table->foreignId('pen_id')->constrained('pens')->cascadeOnDelete();
            $table->integer('quantity')->default(0); // จำนวนหมูที่แจกจ่ายให้คอกนี้
            $table->timestamps();

            // Indexes for quick lookups
            $table->index(['pig_entry_id', 'batch_id']);
            $table->index(['batch_id', 'pen_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pig_entry_details');
    }
};
