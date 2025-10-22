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
        // Add missing columns to payments table
        Schema::table('payments', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('payments', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('note');
            }
            
            if (!Schema::hasColumn('payments', 'approved_by')) {
                $table->string('approved_by')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('payments', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            
            if (!Schema::hasColumn('payments', 'rejected_by')) {
                $table->string('rejected_by')->nullable()->after('approved_at');
            }
            
            if (!Schema::hasColumn('payments', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            
            if (!Schema::hasColumn('payments', 'reject_reason')) {
                $table->text('reject_reason')->nullable()->after('rejected_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop columns if they exist
            $columns = ['status', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'reject_reason'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
