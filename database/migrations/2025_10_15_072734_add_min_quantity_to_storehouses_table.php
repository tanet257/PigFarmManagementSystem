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
            $table->decimal('min_quantity', 10, 2)->default(0)->after('stock')->comment('จำนวนขั้นต่ำสำหรับแจ้งเตือน');
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
            $table->dropColumn('min_quantity');
        });
    }
};
