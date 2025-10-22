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
        Schema::table('users', function (Blueprint $table) {
            $table->string('cancellation_reason')->nullable()->after('rejection_reason')->comment('เหตุผลการขอยกเลิก');
            $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_reason')->comment('เวลาที่ขอยกเลิก');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancellation_requested_at']);
        });
    }
};
