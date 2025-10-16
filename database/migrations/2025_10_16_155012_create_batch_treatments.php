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
            Schema::create('batch_treatments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pen_id')->nullable();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->unsignedBigInteger('dairy_record_id')->nullable();
                $table->string('medicine_name')->nullable();
                $table->string('medicine_code')->nullable();
                $table->decimal('quantity', 12, 2)->nullable();
                $table->string('unit')->nullable();
                $table->string('status')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('date')->nullable();
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
        Schema::dropIfExists('batch_treatments');
    }
};
