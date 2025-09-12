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
        Schema::create('pig_entry_records', function (Blueprint $table) {
            $table->id();

            // FK
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('barn_id')->constrained()->onDelete('cascade');
            $table->foreignId('pen_id')->constrained()->onDelete('cascade');

            // Fields
            $table->dateTime('pig_entry_date');
            $table->integer('quantity');
            $table->decimal('total_weight', 10, 2);
            $table->decimal('excess_weight', 10, 2)->default(0); //น้ำหนักหมูที่เกินมา
            $table->decimal('excess_weight_cost', 10, 2)->default(0); //ค่าน้ำหนักหมูที่เกินมา
            $table->decimal('price_per_pig', 10, 2)->default(0);
            $table->decimal('transport_cost', 10, 2)->default(0);
            $table->string('receipt_file')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pig_entry_records');
    }
};
