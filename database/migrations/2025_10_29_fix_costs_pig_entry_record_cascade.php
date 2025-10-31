<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Change costs.pig_entry_record_id foreign key from cascadeOnDelete to nullOnDelete
     * This prevents costs from being hard-deleted when pig_entry_records are deleted
     */
    public function up(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            // Drop the existing cascade foreign key
            $table->dropForeign(['pig_entry_record_id']);

            // Re-create with nullOnDelete instead
            $table->foreign('pig_entry_record_id')
                ->references('id')
                ->on('pig_entry_records')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['pig_entry_record_id']);

            // Restore the cascade behavior
            $table->foreign('pig_entry_record_id')
                ->references('id')
                ->on('pig_entry_records')
                ->onDelete('cascade');
        });
    }
};
