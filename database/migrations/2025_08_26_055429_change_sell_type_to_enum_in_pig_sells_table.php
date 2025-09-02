<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ถ้าใช้ MySQL
        DB::statement("ALTER TABLE pig_sells MODIFY COLUMN sell_type ENUM('หมูปกติ','หมูตาย','หมูคัดทิ้ง') NOT NULL DEFAULT 'หมูปกติ'");
    }

    public function down()
    {
        // กลับเป็น string เดิม
        DB::statement("ALTER TABLE pig_sells MODIFY COLUMN sell_type VARCHAR(255) NOT NULL DEFAULT 'normal'");
    }
};
