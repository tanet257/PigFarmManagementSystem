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
        // แก้ stock และ min_quantity ใน storehouses จาก decimal(12,2) เป็น integer
        Schema::table('storehouses', function (Blueprint $table) {
            $table->integer('stock')->default(0)->change();
            $table->integer('min_quantity')->nullable()->change();
        });

        // แก้ quantity ใน inventory_movements จาก decimal(12,2) เป็น integer
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->integer('quantity')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ถ้าต้อง rollback
        Schema::table('storehouses', function (Blueprint $table) {
            $table->decimal('stock', 12, 2)->default(0)->change();
            $table->decimal('min_quantity', 12, 2)->nullable()->change();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->nullable()->change();
        });
    }
};
