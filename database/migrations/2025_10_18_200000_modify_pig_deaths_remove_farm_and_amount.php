<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - Back up pig_deaths to pig_deaths_backup
     * - Create new pig_deaths_new with desired columns/order
     * - Copy data (mapping columns)
     * - Drop old table and rename new to pig_deaths
     */
    public function up()
    {
        // Only run if pig_deaths exists
        if (!Schema::hasTable('pig_deaths')) {
            return;
        }

        // Backup existing data
        if (Schema::hasTable('pig_deaths_backup')) {
            DB::table('pig_deaths_backup')->truncate();
        } else {
            Schema::create('pig_deaths_backup', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('original_id')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        // Copy each row into backup (store original id and json payload)
        $rows = DB::table('pig_deaths')->get();
        foreach ($rows as $r) {
            DB::table('pig_deaths_backup')->insert([
                'original_id' => $r->id,
                'payload'     => json_encode($r),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // Create new table pig_deaths_new with desired structure
        if (Schema::hasTable('pig_deaths_new')) {
            Schema::dropIfExists('pig_deaths_new');
        }

        Schema::create('pig_deaths_new', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('dairy_record_id')->nullable();
            $table->unsignedBigInteger('pen_id')->nullable();

            $table->integer('quantity')->default(0);
            $table->string('cause')->nullable();

            $table->text('note')->nullable();
            $table->dateTime('date')->nullable();

            $table->timestamps();

            // indexes and foreign keys kept minimal here; adapt if your app requires them
        });

        // Copy data from old pig_deaths to pig_deaths_new mapping columns where possible
        $oldRows = DB::table('pig_deaths')->get();
        foreach ($oldRows as $old) {
            DB::table('pig_deaths_new')->insert([
                'id'              => $old->id,
                'batch_id'        => $old->batch_id ?? null,
                'dairy_record_id' => $old->dairy_record_id ?? null,
                'pen_id'          => $old->pen_id ?? null,
                'quantity'        => $old->quantity ?? 0,
                'cause'           => $old->cause ?? null,
                'note'            => $old->note ?? null,
                'date'            => $old->date ?? null,
                'created_at'      => $old->created_at ?? now(),
                'updated_at'      => $old->updated_at ?? now(),
            ]);
        }

        // Drop old table and replace
        Schema::dropIfExists('pig_deaths');
        Schema::rename('pig_deaths_new', 'pig_deaths');
    }

    /**
     * Reverse the migrations.
     * We will attempt to restore from pig_deaths_backup if it exists.
     */
    public function down()
    {
        if (!Schema::hasTable('pig_deaths_backup')) {
            return;
        }

        // Create a restore table to hold old structure
        if (Schema::hasTable('pig_deaths_original_restore')) {
            Schema::dropIfExists('pig_deaths_original_restore');
        }

        Schema::create('pig_deaths_original_restore', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('payload');
            $table->timestamps();
        });

        // Restore payloads from backup
        $backups = DB::table('pig_deaths_backup')->get();
        foreach ($backups as $b) {
            DB::table('pig_deaths_original_restore')->insert([
                'payload' => $b->payload,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
