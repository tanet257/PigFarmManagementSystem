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
        Schema::create('cost_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_id')->constrained('costs')->cascadeOnDelete();
            $table->decimal('amount', 15, 2); // จำนวนเงินที่ชำระ
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // สถานะอนุมัติ
            $table->dateTime('approved_date')->nullable(); // วันอนุมัติ
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); // ผู้อนุมัติ
            $table->text('reason')->nullable(); // เหตุผล (ถ้า rejected)

            // Soft delete fields for audit trail
            $table->enum('action_type', ['created', 'updated', 'approved', 'rejected'])->default('created');
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_payments');
    }
};
