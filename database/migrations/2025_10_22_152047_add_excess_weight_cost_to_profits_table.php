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
        Schema::table('profits', function (Blueprint $table) {
            if (!Schema::hasColumn('profits', 'excess_weight_cost')) {
                $table->decimal('excess_weight_cost', 12, 2)->default(0)->after('transport_cost')->comment('ค่าน้ำหนักส่วนเกิน');
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
        Schema::table('profits', function (Blueprint $table) {
            if (Schema::hasColumn('profits', 'excess_weight_cost')) {
                $table->dropColumn('excess_weight_cost');
            }
        });
    }
};
