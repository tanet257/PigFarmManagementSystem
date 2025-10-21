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
        Schema::table('pig_entry_records', function (Blueprint $table) {
            // เพิ่ม receipt_file กลับมา (สำหรับเก็บหลักฐาน)
            if (!Schema::hasColumn('pig_entry_records', 'receipt_file')) {
                $table->string('receipt_file')->nullable()->comment('หลักฐานการรับเงิน');
            }

            // Payment-related columns
            if (!Schema::hasColumn('pig_entry_records', 'payment_method')) {
                $table->string('payment_method')->nullable()->comment('เงินสด, โอนเงิน');
            }
            if (!Schema::hasColumn('pig_entry_records', 'payment_term')) {
                $table->string('payment_term')->nullable()->comment('ระยะเวลาการชำระ');
            }
            if (!Schema::hasColumn('pig_entry_records', 'payment_status')) {
                $table->string('payment_status')->default('pending')->comment('pending, partial, completed');
            }
            if (!Schema::hasColumn('pig_entry_records', 'paid_amount')) {
                $table->decimal('paid_amount', 14, 2)->nullable()->comment('จำนวนเงินที่ชำระ');
            }
            if (!Schema::hasColumn('pig_entry_records', 'balance')) {
                $table->decimal('balance', 14, 2)->nullable()->comment('ยอดคงเหลือ');
            }
            if (!Schema::hasColumn('pig_entry_records', 'due_date')) {
                $table->dateTime('due_date')->nullable()->comment('วันครบกำหนดการชำระ');
            }
            if (!Schema::hasColumn('pig_entry_records', 'paid_date')) {
                $table->dateTime('paid_date')->nullable()->comment('วันที่ชำระเงิน');
            }
            if (!Schema::hasColumn('pig_entry_records', 'total_cost')) {
                $table->decimal('total_cost', 14, 2)->nullable()->comment('ยอดรวมค่าใช้สอย (transport + excess_weight)');
            }
            if (!Schema::hasColumn('pig_entry_records', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->comment('เลขที่ใบเสร็จ');
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
        Schema::table('pig_entry_records', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_file',
                'payment_method',
                'payment_term',
                'payment_status',
                'paid_amount',
                'balance',
                'due_date',
                'paid_date',
                'total_cost',
                'receipt_number',
            ]);
        });
    }
};
