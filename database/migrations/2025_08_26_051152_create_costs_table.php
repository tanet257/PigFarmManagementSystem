<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use App\Models\Batch;
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
        Schema::create('costs', function (Blueprint $table) {
            $table->id();

            // foreign key
            $table->foreignIdFor(Farm::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Batch::class)->constrained()->onDelete('cascade');

            $table->enum('cost_type', [
                'piglet',      // ค่าลูกหมู
                'feed',        // ค่าอาหาร (f931, f932, f933)
                'medicine',    // ค่ายา
                'vaccine',     // ค่าวัคซีน
                'excess_weight', // ค่าน้ำหนักหมูที่เกินมา
                'bran',        // ค่ารำ
                'labor',       // ค่าแรงงาน
                'transport',   // ค่ารถ/ขนส่ง
                'repair',      // ค่าซ่อมแซม
                'dead_pig',    // ขาดทุนจากหมูตาย
                'other'        // อื่น ๆ
    ]);
    
            $table->integer('quantity')->nullable();
            $table->unsignedDecimal('price_per_unit',10,2)->nullable();
            $table->unsignedDecimal('total_price',10,2)->nullable();
            $table->text('note')->nullable();

            //date
            $table->date('date')->default(DB::raw('CURRENT_DATE'));
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
        Schema::dropIfExists('costs');
    }
};
