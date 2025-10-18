<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('batch_pen_allocations')) {
            Schema::table('batch_pen_allocations', function (Blueprint $table) {
                if (!Schema::hasColumn('batch_pen_allocations', 'allocated_pigs')) {
                    $table->integer('allocated_pigs')->nullable()->after('pig_amount');
                }
                if (!Schema::hasColumn('batch_pen_allocations', 'current_quantity')) {
                    $table->integer('current_quantity')->nullable()->after('allocated_pigs');
                }
            });

            // Populate new columns from existing pig_amount where appropriate
            try {
                DB::table('batch_pen_allocations')->orderBy('id')->chunk(500, function ($rows) {
                    foreach ($rows as $row) {
                        $pigAmount = $row->pig_amount ?? 0;
                        DB::table('batch_pen_allocations')
                            ->where('id', $row->id)
                            ->update([
                                'allocated_pigs' => $pigAmount,
                                'current_quantity' => $pigAmount,
                            ]);
                    }
                });
            } catch (\Exception $e) {
                // If DB update fails for any reason, we don't want the migration to completely block deployments
                // but throwing will surface the issue to the developer. Re-throw for now.
                throw $e;
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('batch_pen_allocations')) {
            Schema::table('batch_pen_allocations', function (Blueprint $table) {
                if (Schema::hasColumn('batch_pen_allocations', 'current_quantity')) {
                    $table->dropColumn('current_quantity');
                }
                if (Schema::hasColumn('batch_pen_allocations', 'allocated_pigs')) {
                    $table->dropColumn('allocated_pigs');
                }
            });
        }
    }
};
