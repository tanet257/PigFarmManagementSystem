<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('storehouses', function (Blueprint $table) {
            // ประเภทการเข้า: import, purchase, transfer, production, return
            $table->enum('source', ['import', 'purchase', 'transfer', 'production', 'return'])->default('import')->after('unit');

            // ผู้สร้าง/อัปเดท record
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // เหตุผล (ถ้า cancel หรือเปลี่ยนแปลง)
            $table->text('reason')->nullable();

            // Soft delete fields
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            // เชื่อมกับ Cost record (ถ้าสินค้ามาจากการซื้อ)
            $table->foreignId('cost_id')->nullable()->constrained('costs')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('storehouses', function (Blueprint $table) {
            $table->dropColumn(['source', 'created_by', 'updated_by', 'reason', 'cancelled_at', 'cancelled_by', 'cost_id']);
        });
    }
};
