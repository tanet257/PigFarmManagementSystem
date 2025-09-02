<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\PigLoss;

return new class extends Migration {
    public function up() {
        Schema::table('pig_sells', function (Blueprint $table) {
            // drop pig_death_id
            $table->dropForeign(['pig_death_id']);
            $table->dropColumn('pig_death_id');
            // เพิ่ม FK pig_loss_id
            $table->foreignIdFor(PigLoss::class)->nullable()->after('batch_id')->constrained()->onDelete('set null');
        });
    }

    public function down() {
        Schema::table('pig_sells', function (Blueprint $table) {
            $table->dropForeign(['pig_loss_id']);
            $table->dropColumn('pig_loss_id');

            $table->string('sell_type')->change();
        });
    }
};
