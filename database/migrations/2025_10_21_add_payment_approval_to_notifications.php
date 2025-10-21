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
        Schema::table('notifications', function (Blueprint $table) {
            // Add payment approval fields if they don't exist
            if (!Schema::hasColumn('notifications', 'related_model')) {
                $table->string('related_model')->nullable()->comment('ชนิดของ model ที่เกี่ยวข้อง (PigEntryRecord, PigSale)');
            }
            if (!Schema::hasColumn('notifications', 'related_model_id')) {
                $table->unsignedBigInteger('related_model_id')->nullable()->comment('ID ของ model ที่เกี่ยวข้อง');
            }
            if (!Schema::hasColumn('notifications', 'approval_status')) {
                $table->string('approval_status')->default('pending')->comment('pending, approved, rejected');
            }
            if (!Schema::hasColumn('notifications', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->comment('หมายเหตุในการอนุมัติ');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['related_model', 'related_model_id', 'approval_status', 'approval_notes']);
        });
    }
};
