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
        Schema::create('store_house_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_house_id')->constrained('storehouses')->cascadeOnDelete();
            $table->enum('action', ['create', 'update', 'delete', 'restore'])->default('create');
            $table->enum('change_type', ['quantity', 'price', 'location', 'batch', 'other'])->default('other');

            // ข้อมูลการเปลี่ยนแปลง
            $table->decimal('old_quantity', 15, 2)->nullable();
            $table->decimal('new_quantity', 15, 2)->nullable();
            $table->decimal('old_price', 15, 2)->nullable();
            $table->decimal('new_price', 15, 2)->nullable();

            // ใครทำการเปลี่ยนแปลง
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->index(['store_house_id', 'action']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_house_audit_logs');
    }
};
