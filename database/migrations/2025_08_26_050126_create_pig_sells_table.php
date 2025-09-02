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
        Schema::create('pig_sells', function (Blueprint $table) {
            $table->id();

            // foreign key
            $table->foreignIdFor(Farm::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Batch::class)->constrained()->onDelete('cascade');

            $table->string('sell_type'); //หมูโต, หมูตาย, หมูคัดทิ้ง
            $table->integer('quantity'); //จำนวนตัว
            $table->unsignedDecimal('total_weight',10, 2);
            $table->unsignedDecimal('price_per_kg',10,2)->nullable();
            $table->unsignedDecimal('total_price',10,2)->nullable();
            $table->string('buyer_name')->nullable();

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
        Schema::dropIfExists('pig_sells');
    }
};
