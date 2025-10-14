<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pig_sells', function (Blueprint $table) {
            // เพิ่ม customer_id
            $table->foreignId('customer_id')->nullable()->after('batch_id')->constrained('customers')->onDelete('set null');

            // เลขที่เอกสาร
            $table->string('sale_number')->unique()->nullable()->after('id'); // เลขที่ใบขาย (SELL-2025-001)

            // น้ำหนัก
            $table->decimal('estimated_weight', 10, 2)->nullable()->after('total_weight'); // น้ำหนักประมาณการ
            $table->decimal('actual_weight', 10, 2)->nullable()->after('estimated_weight'); // น้ำหนักชั่งจริง
            $table->decimal('avg_weight_per_pig', 10, 2)->nullable()->after('actual_weight'); // น้ำหนักเฉลี่ย/ตัว

            // ราคาและส่วนลด
            $table->decimal('discount', 10, 2)->default(0)->after('total_price'); // ส่วนลด
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('discount'); // ค่าขนส่ง
            $table->decimal('net_total', 10, 2)->nullable()->after('shipping_cost'); // ราคาสุทธิ (total_price - discount + shipping_cost)

            // การชำระเงิน
            $table->enum('payment_method', ['เงินสด', 'โอนเงิน', 'เช็ค', 'เครดิต'])->default('เงินสด')->after('net_total');
            $table->integer('payment_term')->default(0)->after('payment_method'); // จำนวนวันเครดิต
            $table->enum('payment_status', ['ชำระแล้ว', 'ชำระบางส่วน', 'รอชำระ', 'เกินกำหนด'])->default('รอชำระ')->after('payment_term');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('payment_status'); // ยอดที่ชำระแล้ว
            $table->decimal('balance', 10, 2)->default(0)->after('paid_amount'); // ยอดคงเหลือ
            $table->date('due_date')->nullable()->after('balance'); // วันครบกำหนดชำระ
            $table->date('paid_date')->nullable()->after('due_date'); // วันที่ชำระจริง

            // เอกสาร
            $table->string('invoice_number')->nullable()->after('paid_date'); // เลขที่ใบกำกับภาษี
            $table->string('receipt_number')->nullable()->after('invoice_number'); // เลขที่ใบเสร็จ

            // สถานะการขาย
            $table->enum('sale_status', ['รอยืนยัน', 'ยืนยันแล้ว', 'เสร็จสิ้น', 'ยกเลิก'])->default('รอยืนยัน')->after('receipt_file');

            // ผู้ดำเนินการ
            $table->foreignId('created_by')->nullable()->after('note')->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pig_sells', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'customer_id',
                'sale_number',
                'estimated_weight',
                'actual_weight',
                'avg_weight_per_pig',
                'discount',
                'shipping_cost',
                'net_total',
                'payment_method',
                'payment_term',
                'payment_status',
                'paid_amount',
                'balance',
                'due_date',
                'paid_date',
                'invoice_number',
                'receipt_number',
                'sale_status',
                'created_by',
                'approved_by',
                'approved_at',
            ]);
        });
    }
};
