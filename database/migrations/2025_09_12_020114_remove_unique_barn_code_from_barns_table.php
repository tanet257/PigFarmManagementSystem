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
        Schema::table('barns', function (Blueprint $table) {
            $table->dropUnique('barns_barns_code_unique'); // ชื่อ index ตาม error
        });
    }

    public function down()
    {
        Schema::table('barns', function (Blueprint $table) {
            $table->unique('barn_code');
        });
    }
};
