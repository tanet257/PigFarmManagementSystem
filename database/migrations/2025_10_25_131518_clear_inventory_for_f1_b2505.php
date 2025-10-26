<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ✅ ลบ InventoryMovement ของ batch f1-b2505
        $batch = \App\Models\Batch::where('batch_code', 'f1-b2505')->first();
        if ($batch) {
            \App\Models\InventoryMovement::where('batch_id', $batch->id)->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ✅ ไม่ต้องคืน - เป็นการลบข้อมูล
    }
};
