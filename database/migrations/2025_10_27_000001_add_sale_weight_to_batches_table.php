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
        Schema::table('batches', function (Blueprint $table) {
            // Add fields for tracking weight at sale time
            if (!Schema::hasColumn('batches', 'total_pig_weight_at_sale')) {
                $table->decimal('total_pig_weight_at_sale', 10, 2)->nullable()->after('total_pig_weight')
                    ->comment('Total weight of pigs at time of sale (kg)');
            }

            if (!Schema::hasColumn('batches', 'average_weight_per_pig_at_sale')) {
                $table->decimal('average_weight_per_pig_at_sale', 10, 2)->nullable()->after('average_weight_per_pig')
                    ->comment('Average weight per pig at time of sale (kg)');
            }

            // Track weight gain/loss during raising period
            if (!Schema::hasColumn('batches', 'total_weight_gain')) {
                $table->decimal('total_weight_gain', 10, 2)->nullable()->after('total_pig_weight_at_sale')
                    ->comment('Total weight gain from entry to sale (kg)');
            }

            if (!Schema::hasColumn('batches', 'avg_weight_gain_per_pig')) {
                $table->decimal('avg_weight_gain_per_pig', 10, 2)->nullable()->after('average_weight_per_pig_at_sale')
                    ->comment('Average weight gain per pig (kg)');
            }

            // Track FCR (Feed Conversion Ratio) or other metrics
            if (!Schema::hasColumn('batches', 'raising_days')) {
                $table->unsignedSmallInteger('raising_days')->nullable()->after('end_date')
                    ->comment('Days from entry to sale');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            if (Schema::hasColumn('batches', 'total_pig_weight_at_sale')) {
                $table->dropColumn('total_pig_weight_at_sale');
            }
            if (Schema::hasColumn('batches', 'average_weight_per_pig_at_sale')) {
                $table->dropColumn('average_weight_per_pig_at_sale');
            }
            if (Schema::hasColumn('batches', 'total_weight_gain')) {
                $table->dropColumn('total_weight_gain');
            }
            if (Schema::hasColumn('batches', 'avg_weight_gain_per_pig')) {
                $table->dropColumn('avg_weight_gain_per_pig');
            }
            if (Schema::hasColumn('batches', 'raising_days')) {
                $table->dropColumn('raising_days');
            }
        });
    }
};
