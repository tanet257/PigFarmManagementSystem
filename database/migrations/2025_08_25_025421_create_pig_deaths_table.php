<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use App\Models\Batch;
use App\Models\Farm;
use App\Models\Pen;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pig_deaths', function (Blueprint $table) {
            $table->id();

            // foreign key
            $table->foreignIdFor(Farm::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Batch::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Pen::class)->constrained()->onDelete('cascade');

            $table->integer('amount'); //จำนวนหมูตาย
            $table->text('cause')->nullable(); //สาเหตุที่ตาย
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
        Schema::dropIfExists('pig_deaths');
    }
};
