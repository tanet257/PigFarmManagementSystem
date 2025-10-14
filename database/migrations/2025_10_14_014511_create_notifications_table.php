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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); //ประเภทแจ้งเตือนซ user_registered, user_approved, user_rejected
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete(); // ผู้รับแจ้งเตือน
            $table->foreignId('related_user_id')->nullable()->constrained('users')->cascadeOnDelete(); //ผู้ใช้ที่เกี่ยวข้อง
            $table->string('title');
            $table->text('message');
            $table->string('url')->nullable(); // URL สำหรับดำเนินการ
            $table->boolean('is_read')->default(false); // อ่านแล้ว/ยัง
            $table->timestamp('read_at')->nullable(); //วันเวลาที่อ่าน
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
