<?php

// database/migrations/xxxx_xx_xx_add_total_deaths_to_batches_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->integer('total_deaths')->default(0);
        });


    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('total_deaths');
        });

    }
};

