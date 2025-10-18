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
        if (! Schema::hasTable('batches')) {
            return;
        }

        Schema::table('batches', function (Blueprint $table) {
            // add new columns if missing
            if (! Schema::hasColumn('batches', 'current_quantity')) {
                $table->unsignedInteger('current_quantity')->default(0)->after('total_pig_amount');
            }

            if (! Schema::hasColumn('batches', 'average_weight_per_pig')) {
                $table->unsignedDecimal('average_weight_per_pig', 10, 3)->nullable()->after('total_pig_weight');
            }

            if (! Schema::hasColumn('batches', 'average_price_per_pig')) {
                $table->unsignedDecimal('average_price_per_pig', 12, 2)->nullable()->after('total_pig_price');
            }

            // add new total_death and migrate data from total_deaths if present
            if (! Schema::hasColumn('batches', 'total_death')) {
                $table->integer('total_death')->default(0)->after('note');
            }
        });

        // If old column total_deaths exists, copy values then drop it
        if (Schema::hasColumn('batches', 'total_deaths')) {
            // copy data
            DB::statement('UPDATE `batches` SET `total_death` = COALESCE(`total_deaths`, 0)');

            Schema::table('batches', function (Blueprint $table) {
                // drop foreign constraints first if any mention total_deaths (none expected)
                $table->dropColumn('total_deaths');
            });
        }

        // Drop barn_id and pen_id (if present) - drop foreign keys then columns
        Schema::table('batches', function (Blueprint $table) {
            if (Schema::hasColumn('batches', 'barn_id')) {
                try {
                    $table->dropForeign(['barn_id']);
                } catch (\Exception $e) {
                    // ignore if constraint not present
                }
                $table->dropColumn('barn_id');
            }

            if (Schema::hasColumn('batches', 'pen_id')) {
                try {
                    $table->dropForeign(['pen_id']);
                } catch (\Exception $e) {
                    // ignore
                }
                $table->dropColumn('pen_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('batches')) {
            return;
        }

        Schema::table('batches', function (Blueprint $table) {
            // re-add barn_id and pen_id as nullable unsignedBigInteger (no fk)
            if (! Schema::hasColumn('batches', 'barn_id')) {
                $table->unsignedBigInteger('barn_id')->nullable()->after('farm_id');
            }
            if (! Schema::hasColumn('batches', 'pen_id')) {
                $table->unsignedBigInteger('pen_id')->nullable()->after('barn_id');
            }

            // restore total_deaths if missing
            if (! Schema::hasColumn('batches', 'total_deaths')) {
                $table->integer('total_deaths')->default(0)->after('note');
            }

            // remove new columns
            if (Schema::hasColumn('batches', 'current_quantity')) {
                $table->dropColumn('current_quantity');
            }
            if (Schema::hasColumn('batches', 'average_weight_per_pig')) {
                $table->dropColumn('average_weight_per_pig');
            }
            if (Schema::hasColumn('batches', 'average_price_per_pig')) {
                $table->dropColumn('average_price_per_pig');
            }

            if (Schema::hasColumn('batches', 'total_death')) {
                // copy back values
                DB::statement('UPDATE `batches` SET `total_deaths` = COALESCE(`total_death`, 0)');
                $table->dropColumn('total_death');
            }
        });
    }
};
