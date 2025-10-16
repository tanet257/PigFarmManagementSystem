<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename table `dairy_record` to `dairy_records` and ensure an `id` column exists.
     *
     * @return void
     */
    public function up(): void
    {
        // If the old table exists and the new one does not, rename it.
        if (Schema::hasTable('dairy_record') && !Schema::hasTable('dairy_records')) {
            Schema::rename('dairy_record', 'dairy_records');
        }

        // Ensure the table has an 'id' primary key column (models expect a primary id).
        if (Schema::hasTable('dairy_records') && !Schema::hasColumn('dairy_records', 'id')) {
            Schema::table('dairy_records', function (Blueprint $table) {
                // add id as bigIncrements (unsigned bigint primary key)
                $table->bigIncrements('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     * Attempt to rename back to original name if appropriate.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('dairy_records') && !Schema::hasTable('dairy_record')) {
            Schema::rename('dairy_records', 'dairy_record');
        }
        // Note: we intentionally do not drop the id column on rollback to avoid accidental data loss.
    }
};
