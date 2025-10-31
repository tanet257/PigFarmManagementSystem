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
        Schema::table('cost_payments', function (Blueprint $table) {
            // Add payment method (เงินสด, โอนเงิน, เช็ค)
            $table->string('payment_method')->nullable()->after('cost_id');

            // Add payment_date (when payment was made)
            $table->dateTime('payment_date')->nullable()->after('amount');

            // Add reference_number for bank transfers
            $table->string('reference_number')->nullable()->after('payment_date');

            // Add bank_name for transfers
            $table->string('bank_name')->nullable()->after('reference_number');

            // Add recorded_by to track who recorded the payment
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete()->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cost_payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_date',
                'reference_number',
                'bank_name',
                'recorded_by'
            ]);
        });
    }
};
