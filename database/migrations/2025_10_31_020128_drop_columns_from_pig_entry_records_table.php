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
        Schema::table('pig_entry_records', function (Blueprint $table) {
            $table->dropColumn([
                'quantity',
                'total_weight',
                'receipt_file',
                'payment_method',
                'payment_term',
                'paid_amount',
                'balance',
                'due_date',
                'paid_date',
                'receipt_number',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pig_entry_records', function (Blueprint $table) {
            //
        });
    }
};
