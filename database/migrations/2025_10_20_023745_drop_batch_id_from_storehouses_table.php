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
        Schema::table('storehouses', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('storehouses', function (Blueprint $table) {
            $table->unsignedBigInteger('batch_id')->nullable()->after('farm_id');
        });
    }
};
