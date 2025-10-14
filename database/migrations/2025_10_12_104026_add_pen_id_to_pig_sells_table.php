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
        Schema::table('pig_sales', function (Blueprint $table) {
            $table->unsignedBigInteger('pen_id')->nullable()->after('batch_id');
            $table->foreign('pen_id')->references('id')->on('pens')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pig_sales', function (Blueprint $table) {
            $table->dropForeign(['pen_id']);
            $table->dropColumn('pen_id');
        });
    }
};
