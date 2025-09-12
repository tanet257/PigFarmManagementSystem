<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('farm_id');
            $table->string('item_type');       // feed, medicine, vaccine
            $table->string('item_code')->unique(); // รหัสอาหาร/ยา
            $table->string('item_name');
            $table->decimal('quantity', 10, 2)->default(0); // คงเหลือ
            $table->string('unit')->nullable();  // หน่วย เช่น กระสอบ, ขวด
            $table->timestamps();

            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storehouses');
    }
};
