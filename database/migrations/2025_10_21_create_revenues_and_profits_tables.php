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
        // ตาราง revenues - เก็บรายได้จากการขายหมู
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('farm_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('pig_sale_id')->nullable();
            $table->unsignedBigInteger('pig_entry_record_id')->nullable();

            // Revenue details
            $table->string('revenue_type')->comment('pig_sale, other_income'); // ประเภทรายได้
            $table->decimal('quantity', 12, 2)->nullable()->comment('จำนวนหมู/หน่วยที่ขาย');
            $table->decimal('unit_price', 12, 2)->nullable()->comment('ราคาต่อหน่วย');
            $table->decimal('total_revenue', 14, 2)->comment('รายได้รวม');
            $table->decimal('discount', 14, 2)->default(0)->comment('ส่วนลด');
            $table->decimal('net_revenue', 14, 2)->comment('รายได้สุทธิ (หลังหักส่วนลด)');
            $table->string('payment_status')->default('pending')->comment('pending, partial, completed');
            $table->dateTime('revenue_date')->comment('วันที่มีรายได้');
            $table->dateTime('payment_received_date')->nullable()->comment('วันที่ได้รับเงิน');
            $table->text('note')->nullable();

            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('pig_sale_id')->references('id')->on('pig_sales')->onDelete('cascade');
            $table->foreign('pig_entry_record_id')->references('id')->on('pig_entry_records')->onDelete('cascade');

            $table->timestamps();
        });

        // ตาราง profits - เก็บกำไรจากแต่ละรุ่น/คณะ
        Schema::create('profits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('farm_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();

            // Profit calculation
            $table->decimal('total_revenue', 14, 2)->default(0)->comment('รายได้รวมจากการขาย');
            $table->decimal('total_cost', 14, 2)->default(0)->comment('ต้นทุนรวม (feed + transport + medicine + อื่นๆ)');
            $table->decimal('gross_profit', 14, 2)->default(0)->comment('กำไรขั้นต้น (รายได้ - ต้นทุน)');
            $table->decimal('profit_margin_percent', 8, 2)->default(0)->comment('เปอร์เซ็นต์กำไร');

            // Cost breakdown
            $table->decimal('feed_cost', 14, 2)->default(0)->comment('ค่าอาหาร');
            $table->decimal('medicine_cost', 14, 2)->default(0)->comment('ค่ายา/วัคซีน');
            $table->decimal('transport_cost', 14, 2)->default(0)->comment('ค่าขนส่ง');
            $table->decimal('labor_cost', 14, 2)->default(0)->comment('ค่าแรงงาน');
            $table->decimal('utility_cost', 14, 2)->default(0)->comment('ค่ากระแสไฟ/น้ำ');
            $table->decimal('other_cost', 14, 2)->default(0)->comment('ค่าใช้สอยอื่นๆ');

            // Additional info
            $table->integer('total_pig_sold')->default(0)->comment('จำนวนหมูทั้งหมดที่ขาย');
            $table->integer('total_pig_dead')->default(0)->comment('จำนวนหมูที่ตาย');
            $table->decimal('profit_per_pig', 12, 2)->default(0)->comment('กำไรต่อตัวหมู');

            // Period
            $table->dateTime('period_start')->nullable()->comment('วันเริ่มต้นของคณะ');
            $table->dateTime('period_end')->nullable()->comment('วันสิ้นสุดของคณะ');
            $table->integer('days_in_farm')->default(0)->comment('จำนวนวันที่เลี้ยง');

            // Status
            $table->string('status')->default('incomplete')->comment('incomplete, completed, closed');
            $table->text('note')->nullable();

            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');

            $table->timestamps();
        });

        // ตาราง profit_details - รายละเอียดต้นทุนแต่ละรายการ
        Schema::create('profit_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profit_id')->nullable();
            $table->unsignedBigInteger('cost_id')->nullable();

            // Detail info
            $table->string('cost_category')->comment('feed, medicine, transport, labor, utility, other');
            $table->string('item_name')->nullable()->comment('ชื่อรายการค่าใช้สอย');
            $table->decimal('amount', 14, 2)->comment('จำนวนเงิน');
            $table->text('note')->nullable();

            $table->foreign('profit_id')->references('id')->on('profits')->onDelete('cascade');
            $table->foreign('cost_id')->references('id')->on('costs')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_details');
        Schema::dropIfExists('profits');
        Schema::dropIfExists('revenues');
    }
};
