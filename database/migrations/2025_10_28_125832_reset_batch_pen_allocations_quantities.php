<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ✅ Reset allocated_pigs และ current_quantity เป็น 0 ทุก row
        DB::table('batch_pen_allocations')
            ->update([
                'allocated_pigs'  => 0,
                'current_quantity' => 0,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ❌ ไม่สามารถ restore ได้ (data loss)
        // ลบ migration นี้แล้ว rollback ถ้าต้อง restore
    }
};
