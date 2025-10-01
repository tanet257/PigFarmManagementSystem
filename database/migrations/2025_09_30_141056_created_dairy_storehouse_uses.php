<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dairy_storehouse_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dairy_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('storehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('barn_id')->constrained()->cascadeOnDelete();
            $table->dateTime('date');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dairy_storehouse_uses');
    }
};
