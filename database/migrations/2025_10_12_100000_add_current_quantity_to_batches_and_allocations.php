<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // เพิ่มคอลัมน์ current_quantity ในตาราง batches
        if (!Schema::hasColumn('batches', 'current_quantity')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->integer('current_quantity')->nullable()->after('total_pig_amount')
                    ->comment('จำนวนหมูปัจจุบัน (หลังหักขาย/ตาย/คัดทิ้ง)');
            });

            // Copy ค่าจาก total_pig_amount ไปเป็น current_quantity
            DB::statement('UPDATE batches SET current_quantity = total_pig_amount WHERE current_quantity IS NULL');
        }

        // เพิ่มคอลัมน์ current_quantity ในตาราง batch_pen_allocations
        if (!Schema::hasColumn('batch_pen_allocations', 'current_quantity')) {
            Schema::table('batch_pen_allocations', function (Blueprint $table) {
                $table->integer('current_quantity')->nullable()->after('allocated_pigs')
                    ->comment('จำนวนหมูปัจจุบันในเล้า-คอก');
            });

            // Copy ค่าจาก allocated_pigs ไปเป็น current_quantity
            DB::statement('UPDATE batch_pen_allocations SET current_quantity = allocated_pigs WHERE current_quantity IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            if (Schema::hasColumn('batches', 'current_quantity')) {
                $table->dropColumn('current_quantity');
            }
        });

        Schema::table('batch_pen_allocations', function (Blueprint $table) {
            if (Schema::hasColumn('batch_pen_allocations', 'current_quantity')) {
                $table->dropColumn('current_quantity');
            }
        });
    }
};
