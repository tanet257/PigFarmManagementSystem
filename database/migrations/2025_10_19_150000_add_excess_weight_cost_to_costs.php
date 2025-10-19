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
        Schema::table('costs', function (Blueprint $table) {
            if (!Schema::hasColumn('costs', 'excess_weight_cost')) {
                $table->decimal('excess_weight_cost', 12, 2)->nullable()->after('transport_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            if (Schema::hasColumn('costs', 'excess_weight_cost')) {
                $table->dropColumn('excess_weight_cost');
            }
        });
    }
};
