<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Rename cancelled columns to rejected for consistency with Payment table
     * CostPayment uses: pending -> approved/rejected (same as Payment)
     */
    public function up(): void
    {
        if (Schema::hasTable('cost_payments')) {
            Schema::table('cost_payments', function (Blueprint $table) {
                // Check if old columns exist and rename them
                if (Schema::hasColumn('cost_payments', 'cancelled_at') && !Schema::hasColumn('cost_payments', 'rejected_at')) {
                    $table->renameColumn('cancelled_at', 'rejected_at');
                }
                
                if (Schema::hasColumn('cost_payments', 'cancelled_by') && !Schema::hasColumn('cost_payments', 'rejected_by')) {
                    $table->renameColumn('cancelled_by', 'rejected_by');
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
                // Rename back to original names
                if (Schema::hasColumn('cost_payments', 'rejected_at') && !Schema::hasColumn('cost_payments', 'cancelled_at')) {
                    $table->renameColumn('rejected_at', 'cancelled_at');
                }
                
                if (Schema::hasColumn('cost_payments', 'rejected_by') && !Schema::hasColumn('cost_payments', 'cancelled_by')) {
                    $table->renameColumn('rejected_by', 'cancelled_by');
                }
            });
        }
    }
};
