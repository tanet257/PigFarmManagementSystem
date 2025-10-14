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
            // สถานะการขาย: pending (รอ), approved (อนุมัติ), rejected (ปฏิเสธ)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('approved_at');

            // ข้อมูลการปฏิเสธ
            $table->string('rejected_by')->nullable()->after('status');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
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
            $table->dropColumn(['status', 'rejected_by', 'rejected_at', 'rejection_reason']);
        });
    }
};
