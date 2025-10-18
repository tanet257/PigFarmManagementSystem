<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensure pig_deaths table has the columns used by the PigDeath model.
     */
    public function up()
    {
        if (!Schema::hasTable('pig_deaths')) {
            Schema::create('pig_deaths', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->unsignedBigInteger('pen_id')->nullable();
                $table->unsignedBigInteger('dairy_record_id')->nullable();
                $table->integer('quantity')->nullable();
                $table->string('cause')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('date')->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table('pig_deaths', function (Blueprint $table) {
            if (!Schema::hasColumn('pig_deaths', 'batch_id')) {
                $table->unsignedBigInteger('batch_id')->nullable();
            }
            if (!Schema::hasColumn('pig_deaths', 'pen_id')) {
                $table->unsignedBigInteger('pen_id')->nullable();
            }
            if (!Schema::hasColumn('pig_deaths', 'dairy_record_id')) {
                $table->unsignedBigInteger('dairy_record_id')->nullable();
            }
            if (!Schema::hasColumn('pig_deaths', 'quantity')) {
                $table->integer('quantity')->nullable();
            }
            if (!Schema::hasColumn('pig_deaths', 'cause')) {
                $table->string('cause')->nullable();
            }
            if (!Schema::hasColumn('pig_deaths', 'note')) {
                $table->text('note')->nullable();
            }
            if (!Schema::hasColumn('pig_deaths', 'date')) {
                $table->dateTime('date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // We intentionally do not drop columns to avoid data loss; only drop if table was created by this migration.
        if (Schema::hasTable('pig_deaths')) {
            // no-op: leave existing table intact
        }
    }
};
