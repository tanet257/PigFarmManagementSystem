<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Rename cancelled_by/cancelled_at to rejected_by/rejected_at in cost_payments table
     * This standardizes terminology across Payment and CostPayment tables
     */
    public function up(): void
    {
        if (Schema::hasTable('cost_payments')) {
            Schema::table('cost_payments', function (Blueprint $table) {
                // Rename cancelled_by to rejected_by
                if (Schema::hasColumn('cost_payments', 'cancelled_by') && !Schema::hasColumn('cost_payments', 'rejected_by')) {
                    $table->renameColumn('cancelled_by', 'rejected_by');
                }
                
                // Rename cancelled_at to rejected_at
                if (Schema::hasColumn('cost_payments', 'cancelled_at') && !Schema::hasColumn('cost_payments', 'rejected_at')) {
                    $table->renameColumn('cancelled_at', 'rejected_at');
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
                // Rename back to cancelled_by
                if (Schema::hasColumn('cost_payments', 'rejected_by') && !Schema::hasColumn('cost_payments', 'cancelled_by')) {
                    $table->renameColumn('rejected_by', 'cancelled_by');
                }
                
                // Rename back to cancelled_at
                if (Schema::hasColumn('cost_payments', 'rejected_at') && !Schema::hasColumn('cost_payments', 'cancelled_at')) {
                    $table->renameColumn('rejected_at', 'cancelled_at');
                }
            });
        }
    }
};
