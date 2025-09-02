<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Barn;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pens', function (Blueprint $table) {
            $table->id();

            // Foreign key
            $table->foreignIdFor(Barn::class)->constrained()->onDelete('cascade');

            $table->string('pen_code')->unique();
            $table->unsignedInteger('pig_capacity')->nullable();
            $table->enum('status', ['กำลังใช้งาน', 'ไม่ได้ใช้งาน','ใช้กักหมูป่วย'])->default('กำลังใช้งาน');
            $table->text('note')->nullable();
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
        Schema::dropIfExists('pens');
    }
};
