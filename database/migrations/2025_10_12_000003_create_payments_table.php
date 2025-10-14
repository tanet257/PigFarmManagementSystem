<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PigSell;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // อ้างอิงใบขาย
            $table->foreignIdFor(PigSell::class)->constrained()->onDelete('cascade');

            // ข้อมูลการชำระเงิน
            $table->string('payment_number')->unique(); // เลขที่การชำระ (PAY-2025-001)
            $table->date('payment_date'); // วันที่ชำระ
            $table->decimal('amount', 10, 2); // จำนวนเงินที่ชำระ

            // วิธีชำระเงิน
            $table->enum('payment_method', ['เงินสด', 'โอนเงิน']);
            $table->string('reference_number')->nullable(); // เลขที่โอน (ถ้าโอนเงิน)
            $table->string('bank_name')->nullable(); // ธนาคาร (ถ้าโอนเงิน)

            // ไฟล์หลักฐาน
            $table->string('receipt_file')->nullable(); // สลิปโอน

            // หมายเหตุ
            $table->text('note')->nullable();

            // ผู้บันทึก
            $table->foreignIdFor(User::class, 'recorded_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
