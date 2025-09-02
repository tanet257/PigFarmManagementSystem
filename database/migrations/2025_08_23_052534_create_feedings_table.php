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
        Schema::create('feedings', function (Blueprint $table) {
            $table->id();

            // foreign key
            $table->foreignIdFor(Farm::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Batch::class)->constrained()->onDelete('cascade');

            $table->string('feed_type'); //ประเภทการให้อาหาร
            $table->integer('quantity');
            $table->string('unit')->default('bag');
            $table->decimal('amount',10,2)->nullable();
            $table->decimal('price_per_unit', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
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
        Schema::dropIfExists('feedings');
    }
};
