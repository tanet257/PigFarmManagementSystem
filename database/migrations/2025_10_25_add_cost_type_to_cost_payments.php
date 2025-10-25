<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add cost_type field to track cost type for auto-approval logic
     * Feed/Medicine costs should auto-approve without admin review
     * Piglet/Wage/Transport costs need manual admin approval
     */
    public function up(): void
    {
        if (Schema::hasTable('cost_payments')) {
            Schema::table('cost_payments', function (Blueprint $table) {
                // Add cost_type for easy filtering of auto-approve items
                if (!Schema::hasColumn('cost_payments', 'cost_type')) {
                    $table->string('cost_type')->nullable()->after('cost_id')
                        ->comment('feed, medicine, piglet, wage, transport, etc - for filtering auto-approval');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cost_payments')) {
            Schema::table('cost_payments', function (Blueprint $table) {
                if (Schema::hasColumn('cost_payments', 'cost_type')) {
                    $table->dropColumn('cost_type');
                }
            });
        }
    }
};
