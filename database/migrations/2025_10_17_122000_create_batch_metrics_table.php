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
        if (! Schema::hasTable('batch_metrics')) {
            Schema::create('batch_metrics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');

                // derived metrics
                $table->decimal('adg', 10, 4)->nullable();
                $table->decimal('fcr', 10, 4)->nullable();
                $table->decimal('fcg', 14, 4)->nullable();

                // raw/summary metrics
                $table->decimal('total_feed_used', 12, 3)->nullable(); // kg
                $table->integer('total_mortality')->nullable(); // number of deaths in summary period

                $table->timestamps();

                $table->unique('batch_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('batch_metrics');
    }
};
