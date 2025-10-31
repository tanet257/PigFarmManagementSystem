<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dairy_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dairy_record_id')->constrained('dairy_records')->onDelete('cascade');
            $table->enum('item_type', ['feed', 'medicine', 'death']);

            // For Feed (from dairy_storehouse_uses)
            $table->foreignId('storehouse_id')->nullable()->constrained('storehouses')->onDelete('set null');

            // For Medicine (from batch_treatments)
            $table->string('medicine_code')->nullable();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('set null');
            $table->date('treatment_date')->nullable();
            $table->string('treatment_status')->nullable();

            // For Death (from pig_deaths)
            $table->foreignId('pen_id')->nullable()->constrained('pens')->onDelete('set null');
            $table->date('death_date')->nullable();

            // Common fields
            $table->foreignId('barn_id')->nullable()->constrained('barns')->onDelete('set null');
            $table->integer('quantity')->default(0);
            $table->string('unit')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('dairy_record_id');
            $table->index('item_type');
            $table->index('storehouse_id');
            $table->index(['dairy_record_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dairy_record_items');
    }
};
