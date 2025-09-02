<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batch_treatments', function (Blueprint $table) {
            $table->string('medicine_name')->nullable()->after('farm_id');
            // ใช้ after() ถ้าอยากให้เรียงอยู่หลัง farm_id
        });
    }

    public function down(): void
    {
        Schema::table('batch_treatments', function (Blueprint $table) {
            $table->dropColumn('medicine_name');
        });
    }
};
