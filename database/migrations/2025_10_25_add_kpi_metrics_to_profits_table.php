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
        Schema::table('profits', function (Blueprint $table) {
            // ADG = Average Daily Gain (กก./ตัว/วัน)
            if (!Schema::hasColumn('profits', 'adg')) {
                $table->decimal('adg', 8, 3)->default(0)->after('profit_per_pig')->comment('ADG - Average Daily Gain (kg/head/day)');
            }

            // FCR = Feed Conversion Ratio (กก.อาหาร/กก.เพิ่ม)
            if (!Schema::hasColumn('profits', 'fcr')) {
                $table->decimal('fcr', 8, 3)->default(0)->after('adg')->comment('FCR - Feed Conversion Ratio (kg feed/kg gain)');
            }

            // FCG = Feed Cost per kg Gain (บาท/กก.เพิ่ม)
            if (!Schema::hasColumn('profits', 'fcg')) {
                $table->decimal('fcg', 10, 2)->default(0)->after('fcr')->comment('FCG - Feed Cost per kg Gain (baht/kg gain)');
            }

            // เก็บ starting weight และ ending weight สำหรับ calculation
            if (!Schema::hasColumn('profits', 'starting_avg_weight')) {
                $table->decimal('starting_avg_weight', 8, 2)->default(0)->after('fcg')->comment('Starting average weight per pig (kg)');
            }

            if (!Schema::hasColumn('profits', 'ending_avg_weight')) {
                $table->decimal('ending_avg_weight', 8, 2)->default(0)->after('starting_avg_weight')->comment('Ending average weight per pig (kg)');
            }

            // Total feed consumed (กระสอบ/กก.)
            if (!Schema::hasColumn('profits', 'total_feed_bags')) {
                $table->integer('total_feed_bags')->default(0)->after('ending_avg_weight')->comment('Total feed bags consumed');
            }

            if (!Schema::hasColumn('profits', 'total_feed_kg')) {
                $table->decimal('total_feed_kg', 12, 2)->default(0)->after('total_feed_bags')->comment('Total feed in kg consumed');
            }

            // Total weight gained
            if (!Schema::hasColumn('profits', 'total_weight_gained')) {
                $table->decimal('total_weight_gained', 12, 2)->default(0)->after('total_feed_kg')->comment('Total weight gained by all pigs (kg)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profits', function (Blueprint $table) {
            $table->dropColumn([
                'adg',
                'fcr',
                'fcg',
                'starting_avg_weight',
                'ending_avg_weight',
                'total_feed_bags',
                'total_feed_kg',
                'total_weight_gained',
            ]);
        });
    }
};
