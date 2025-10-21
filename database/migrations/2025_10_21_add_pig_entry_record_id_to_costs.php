<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            if (!Schema::hasColumn('costs', 'pig_entry_record_id')) {
                $table->unsignedBigInteger('pig_entry_record_id')->nullable()
                    ->comment('Reference to pig entry record for payment tracking')
                    ->after('batch_id');

                // Add foreign key
                $table->foreign('pig_entry_record_id')
                    ->references('id')
                    ->on('pig_entry_records')
                    ->onDelete('cascade');
            }

            // Add payment-related columns if they don't exist
            if (!Schema::hasColumn('costs', 'payment_method')) {
                $table->string('payment_method')->nullable()->comment('เงินสด, โอนเงิน')->after('receipt_file');
            }
            if (!Schema::hasColumn('costs', 'paid_date')) {
                $table->dateTime('paid_date')->nullable()->comment('วันที่ชำระเงิน')->after('payment_method');
            }
            if (!Schema::hasColumn('costs', 'payment_status')) {
                $table->string('payment_status')->default('pending')->comment('pending, completed')->after('paid_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costs', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('costs', 'pig_entry_record_id')) {
                $table->dropForeign(['pig_entry_record_id']);
                $table->dropColumn('pig_entry_record_id');
            }

            if (Schema::hasColumn('costs', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('costs', 'paid_date')) {
                $table->dropColumn('paid_date');
            }
            if (Schema::hasColumn('costs', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
