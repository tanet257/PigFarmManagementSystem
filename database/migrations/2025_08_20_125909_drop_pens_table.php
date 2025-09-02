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
        //
        Schema::dropIfExists('pens');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::create('pens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barns_id')->constrained()->onDelete('cascade');
            $table->string('pens_code')->nullable();
            $table->integer('capacity')->nullable();
            $table->timestamps();
        });
    }
};
