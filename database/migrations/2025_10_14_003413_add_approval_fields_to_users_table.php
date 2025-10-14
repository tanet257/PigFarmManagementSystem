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
            // สถานะการอนุมัติ: pending (รอ), approved (อนุมัติ), rejected (ปฏิเสธ)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('email');

            // ผู้อนุมัติ (FK to users.id)
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();

            // วันเวลาที่อนุมัติ
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            // เหตุผลที่ปฏิเสธ (ถ้าถูกปฏิเสธ)
            $table->text('rejection_reason')->nullable()->after('approved_at');
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
            // ลบ foreign key ก่อน
            $table->dropForeign(['approved_by']);

            // ลบ columns
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'rejection_reason']);
        });
    }
};
