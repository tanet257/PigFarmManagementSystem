<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Pen;
use App\Models\Barn;
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
        Schema::create('batch_treatments', function (Blueprint $table) {
            $table->id();

            // foreign key
            $table->foreignIdFor(Barn::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Pen::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Batch::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Farm::class)->constrained()->onDelete('cascade');

            $table->string('medicine_name');
            $table->unsignedDecimal('dosage',10,2)->nullable();
            $table->string('unit')->nullable();
            $table->enum('status', ['วางแผนว่าจะให้ยา','กำลังดำเนินการ (กำลังฉีด/กำลังให้ยาอยู่)', 'ให้ยาเสร็จแล้ว','ยกเลิก'])->default('วางแผนว่าจะให้ยา');
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
        Schema::dropIfExists('batch_treatments');
    }
};
