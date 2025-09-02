<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
       Schema::table('pig_sells', function (Blueprint $table) {
    $table->foreignId('pig_death_id')->nullable()->after('batch_id')->constrained('pig_deaths')->onDelete('set null');
});

    }

    public function down()
    {
        Schema::table('pig_sells', function (Blueprint $table) {
            $table->dropForeign(['pig_death_id']);
            $table->dropColumn('pig_death_id');
        });
    }
};
