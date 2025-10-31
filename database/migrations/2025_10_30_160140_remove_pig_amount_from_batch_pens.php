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
        Schema::table('batch_pen_allocations', function (Blueprint $table) {
            if (Schema::hasColumn('batch_pen_allocations', 'pig_amount')) {
                $table->dropColumn('pig_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batch_pen_allocations', function (Blueprint $table) {
            $table->integer('pig_amount')->nullable();
        });
    }
};
