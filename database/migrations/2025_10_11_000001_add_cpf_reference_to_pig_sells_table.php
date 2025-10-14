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
        Schema::table('pig_sales', function (Blueprint $table) {
            // เพิ่มคอลัมน์สำหรับเก็บราคาอ้างอิงจาก CPF
            $table->decimal('cpf_reference_price', 10, 2)->nullable()->after('price_per_kg');
            $table->date('cpf_reference_date')->nullable()->after('cpf_reference_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pig_sales', function (Blueprint $table) {
            $table->dropColumn(['cpf_reference_price', 'cpf_reference_date']);
        });
    }
};
