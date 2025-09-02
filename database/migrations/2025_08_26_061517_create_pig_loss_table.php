<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pig_loss', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->foreignId('pen_id')->nullable()->constrained('pens')->onDelete('set null');

            // Enum type: หมูตาย หรือ หมูคัดทิ้ง
            $table->enum('loss_type', ['หมูตาย', 'หมูคัดทิ้ง'])->default('หมูตาย');

            $table->integer('quantity');                 // จำนวนตัว
            $table->decimal('weight', 10,2)->nullable(); // น้ำหนักรวม
            $table->decimal('price_per_kg', 10,2)->nullable();
            $table->decimal('total_price', 10,2)->nullable();

            $table->string('cause')->nullable(); // สาเหตุ
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pig_loss');
    }
};
