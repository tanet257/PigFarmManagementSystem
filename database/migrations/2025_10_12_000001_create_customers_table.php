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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique(); // รหัสลูกค้า (CUST-001)
            $table->string('customer_name'); // ชื่อลูกค้า/บริษัท
            $table->enum('customer_type', ['นายหน้า', 'โรงชำแหละ', 'ผู้บริโภค'])->default('นายหน้า');

            // ข้อมูลติดต่อ
            $table->string('phone')->nullable();
            $table->string('line_id')->nullable();
            $table->text('address')->nullable();

            // ข้อมูลภาษี (สำหรับออกใบกำกับ)
            $table->string('tax_id')->nullable(); // เลขผู้เสียภาษี
            $table->string('branch')->nullable(); // สาขา (สำนักงานใหญ่/สาขา)

            // เงื่อนไขการชำระเงิน
            $table->integer('credit_days')->default(0); // จำนวนวันเครดิต (0 = เงินสด)
            $table->decimal('credit_limit', 15, 2)->default(0); // วงเงินเครดิตสูงสุด

            // สถิติ
            $table->decimal('total_purchased', 15, 2)->default(0); // ยอดซื้อสะสม
            $table->decimal('total_outstanding', 15, 2)->default(0); // ยอดค้างชำระ
            $table->integer('total_orders')->default(0); // จำนวนครั้งที่ซื้อ
            $table->date('last_purchase_date')->nullable(); // วันที่ซื้อล่าสุด

            // หมายเหตุ
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true); // สถานะใช้งาน

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
