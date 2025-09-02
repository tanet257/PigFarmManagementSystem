<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Pen;
use App\Models\Barn;
use App\Models\Farm;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();

            // foreign key
            $table->foreignIdFor(Barn::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Pen::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Farm::class)->constrained()->onDelete('cascade');

            $table->string('batch_code')->unique();
            $table->unsignedDecimal('total_pig_weight',10,2)->nullable();
            $table->unsignedDecimal('total_pig_amount',10,2)->nullable();
            $table->unsignedDecimal('initial_pig_amount',10,2)->nullable();
            $table->unsignedDecimal('total_pig_price',10,2)->nullable();
            $table->enum('status', ['กำลังเลี้ยง', 'เสร็จสิ้น'])->default('กำลังเลี้ยง');
            $table->text('note')->nullable();

            //date
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable(); // วันที่สิ้นสุด
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
        Schema::dropIfExists('batches');
    }
};
