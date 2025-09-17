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
        Schema::create('dairy_record', function (Blueprint $table) {
            // FK
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('barn_id')->constrained()->onDelete('cascade');
            $table->foreignId('pen_id')->constrained()->onDelete('cascade');

            // Fields
            $table->dateTime('record_date');
            $table->integer('quantity');
            $table->decimal('total_weight', 10, 2);
            $table->decimal('excess_weight', 10, 2)->default(0);
            $table->decimal('excess_weight_cost', 10, 2)->default(0);
            $table->decimal('price_per_pig', 10, 2)->default(0);
            $table->decimal('transport_cost', 10, 2)->default(0);
            $table->string('receipt_file')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dairy_record');
    }
};
