<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'farm_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('farm_id')->nullable()->after('address');
                if (Schema::hasTable('farms')) {
                    $table->foreign('farm_id')->references('id')->on('farms')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'farm_id')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'farm_id')) {
                    $table->dropForeign(['farm_id']);
                    $table->dropColumn('farm_id');
                }
            });
        }
    }
};
