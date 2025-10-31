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
        Schema::table('inventory_movements', function (Blueprint $table) {
            // Add dairy_record_item_id to link with DairyRecordItem
            $table->foreignId('dairy_record_item_id')
                ->nullable()
                ->constrained('dairy_record_items')
                ->onDelete('set null')
                ->after('id');

            // Add index for faster queries
            $table->index('dairy_record_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['dairy_record_item_id']);
            $table->dropIndex(['dairy_record_item_id']);
            $table->dropColumn('dairy_record_item_id');
        });
    }
};
