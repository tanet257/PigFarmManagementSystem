<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove duplicate approval columns from PigSale, Cost, and Revenue tables.
     * These columns should only exist in Payment and CostPayment connector tables.
     */
    public function up(): void
    {
        // ===== PigSale Table =====
        // Remove approval-related columns (should be managed by Payment table)
        if (Schema::hasTable('pig_sales')) {
            Schema::table('pig_sales', function (Blueprint $table) {
                $columnsToRemove = [];

                // Check and remove payment_status (should query from Payment.status)
                if (Schema::hasColumn('pig_sales', 'payment_status')) {
                    $columnsToRemove[] = 'payment_status';
                }

                // Check and remove approved_by (approval done in Payment)
                if (Schema::hasColumn('pig_sales', 'approved_by')) {
                    $columnsToRemove[] = 'approved_by';
                }

                // Check and remove approved_at
                if (Schema::hasColumn('pig_sales', 'approved_at')) {
                    $columnsToRemove[] = 'approved_at';
                }

                // Check and remove rejected_by
                if (Schema::hasColumn('pig_sales', 'rejected_by')) {
                    $columnsToRemove[] = 'rejected_by';
                }

                // Check and remove rejected_at
                if (Schema::hasColumn('pig_sales', 'rejected_at')) {
                    $columnsToRemove[] = 'rejected_at';
                }

                // Check and remove rejection_reason (use Payment.reason instead)
                if (Schema::hasColumn('pig_sales', 'rejection_reason')) {
                    $columnsToRemove[] = 'rejection_reason';
                }

                // Check and remove paid_date (should be Payment.payment_date)
                if (Schema::hasColumn('pig_sales', 'paid_date')) {
                    $columnsToRemove[] = 'paid_date';
                }

                // Check and remove paid_amount (should be Payment.amount)
                if (Schema::hasColumn('pig_sales', 'paid_amount')) {
                    $columnsToRemove[] = 'paid_amount';
                }

                // Check and remove balance (calculated field, not needed)
                if (Schema::hasColumn('pig_sales', 'balance')) {
                    $columnsToRemove[] = 'balance';
                }

                // Drop all identified columns
                if (!empty($columnsToRemove)) {
                    $table->dropColumn($columnsToRemove);
                }
            });
        }

        // ===== Cost Table =====
        // Remove payment-related columns (should be managed by CostPayment table)
        if (Schema::hasTable('costs')) {
            Schema::table('costs', function (Blueprint $table) {
                $columnsToRemove = [];

                // Check and remove payment_status (should query from CostPayment.status)
                if (Schema::hasColumn('costs', 'payment_status')) {
                    $columnsToRemove[] = 'payment_status';
                }

                // Check and remove paid_date (should be CostPayment.approved_date)
                if (Schema::hasColumn('costs', 'paid_date')) {
                    $columnsToRemove[] = 'paid_date';
                }

                // Check and remove payment_method (not needed for cost)
                if (Schema::hasColumn('costs', 'payment_method')) {
                    $columnsToRemove[] = 'payment_method';
                }

                // Drop all identified columns
                if (!empty($columnsToRemove)) {
                    $table->dropColumn($columnsToRemove);
                }
            });
        }

        // ===== Revenue Table =====
        // Remove payment_status (should query Payment.status instead)
        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                $columnsToRemove = [];

                // Check and remove payment_status (should be determined by querying Payment.status for pig_sale_id)
                if (Schema::hasColumn('revenues', 'payment_status')) {
                    $columnsToRemove[] = 'payment_status';
                }

                // Check and remove payment_received_date (should be Payment.payment_date)
                if (Schema::hasColumn('revenues', 'payment_received_date')) {
                    $columnsToRemove[] = 'payment_received_date';
                }

                // Drop all identified columns
                if (!empty($columnsToRemove)) {
                    $table->dropColumn($columnsToRemove);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ===== PigSale Table - Restore columns =====
        if (Schema::hasTable('pig_sales')) {
            Schema::table('pig_sales', function (Blueprint $table) {
                // Restore payment-related columns
                if (!Schema::hasColumn('pig_sales', 'payment_status')) {
                    $table->string('payment_status')->nullable();
                }
                if (!Schema::hasColumn('pig_sales', 'paid_amount')) {
                    $table->decimal('paid_amount', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('pig_sales', 'balance')) {
                    $table->decimal('balance', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('pig_sales', 'paid_date')) {
                    $table->dateTime('paid_date')->nullable();
                }

                // Restore approval-related columns
                if (!Schema::hasColumn('pig_sales', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable();
                }
                if (!Schema::hasColumn('pig_sales', 'approved_at')) {
                    $table->dateTime('approved_at')->nullable();
                }
                if (!Schema::hasColumn('pig_sales', 'rejected_by')) {
                    $table->unsignedBigInteger('rejected_by')->nullable();
                }
                if (!Schema::hasColumn('pig_sales', 'rejected_at')) {
                    $table->dateTime('rejected_at')->nullable();
                }
                if (!Schema::hasColumn('pig_sales', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable();
                }
            });
        }

        // ===== Cost Table - Restore columns =====
        if (Schema::hasTable('costs')) {
            Schema::table('costs', function (Blueprint $table) {
                if (!Schema::hasColumn('costs', 'payment_status')) {
                    $table->string('payment_status')->nullable();
                }
                if (!Schema::hasColumn('costs', 'paid_date')) {
                    $table->dateTime('paid_date')->nullable();
                }
                if (!Schema::hasColumn('costs', 'payment_method')) {
                    $table->string('payment_method')->nullable();
                }
            });
        }

        // ===== Revenue Table - Restore columns =====
        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                if (!Schema::hasColumn('revenues', 'payment_status')) {
                    $table->string('payment_status')->default('pending')->nullable();
                }
                if (!Schema::hasColumn('revenues', 'payment_received_date')) {
                    $table->dateTime('payment_received_date')->nullable();
                }
            });
        }
    }
};
