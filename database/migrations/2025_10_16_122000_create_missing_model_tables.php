<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create missing tables for models in a safe, idempotent way.
     */
    public function up(): void
    {
        // farms
        if (!Schema::hasTable('farms')) {
            Schema::create('farms', function (Blueprint $table) {
                $table->id();
                $table->string('farm_name')->unique();
                $table->unsignedInteger('barn_capacity')->nullable();
                $table->timestamps();
            });
        }

        // barns
        if (!Schema::hasTable('barns')) {
            Schema::create('barns', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->string('barn_code')->unique()->nullable();
                $table->unsignedInteger('pig_capacity')->nullable();
                $table->unsignedInteger('pen_capacity')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // pens
        if (!Schema::hasTable('pens')) {
            Schema::create('pens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('barn_id')->nullable();
                $table->string('pen_code')->unique()->nullable();
                $table->unsignedInteger('pig_capacity')->nullable();
                $table->string('status')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // batches
        if (!Schema::hasTable('batches')) {
            Schema::create('batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('barn_id')->nullable();
                $table->unsignedBigInteger('pen_id')->nullable();
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->string('batch_code')->unique()->nullable();
                $table->unsignedDecimal('total_pig_weight', 10, 2)->nullable();
                $table->unsignedDecimal('total_pig_amount', 10, 2)->nullable();
                $table->unsignedDecimal('total_pig_price', 12, 2)->nullable();
                $table->integer('total_deaths')->default(0);
                $table->string('status')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->timestamps();
            });
        }

        // batch_treatments
        if (!Schema::hasTable('batch_treatments')) {
            Schema::create('batch_treatments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pen_id')->nullable();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->unsignedBigInteger('dairy_record_id')->nullable();
                $table->string('medicine_name')->nullable();
                $table->string('medicine_code')->nullable();
                $table->decimal('quantity', 12, 2)->nullable();
                $table->string('unit')->nullable();
                $table->string('status')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('date')->nullable();
                $table->timestamps();
            });
        }

        // batch_pen_allocations
        if (!Schema::hasTable('batch_pen_allocations')) {
            Schema::create('batch_pen_allocations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->unsignedBigInteger('barn_id')->nullable();
                $table->unsignedBigInteger('pen_id')->nullable();
                $table->integer('pig_amount')->nullable();
                $table->dateTime('move_date')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // dairy_records (plural)
        if (!Schema::hasTable('dairy_records')) {
            Schema::create('dairy_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->unsignedBigInteger('barn_id')->nullable();
                $table->unsignedBigInteger('pen_id')->nullable();
                $table->dateTime('date')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // dairy_storehouse_uses
        if (!Schema::hasTable('dairy_storehouse_uses')) {
            Schema::create('dairy_storehouse_uses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dairy_record_id')->nullable();
                $table->unsignedBigInteger('storehouse_id')->nullable();
                $table->unsignedBigInteger('barn_id')->nullable();
                $table->decimal('quantity', 12, 2)->nullable();
                $table->dateTime('date')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // storehouses
        if (!Schema::hasTable('storehouses')) {
            Schema::create('storehouses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->string('item_type')->nullable();
                $table->string('item_code')->unique()->nullable();
                $table->string('item_name')->nullable();
                $table->decimal('stock', 12, 2)->default(0);
                $table->decimal('min_quantity', 12, 2)->nullable();
                $table->string('unit')->nullable();
                $table->string('status')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('date')->nullable();
                $table->timestamps();
            });
        }

        // inventory_movements
        if (!Schema::hasTable('inventory_movements')) {
            Schema::create('inventory_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('storehouse_id')->nullable();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->unsignedBigInteger('barn_id')->nullable();
                $table->string('change_type')->nullable();
                $table->decimal('quantity', 12, 2)->nullable();
                $table->text('note')->nullable();
                $table->dateTime('date')->nullable();
                $table->timestamps();
            });
        }

        // costs
        if (!Schema::hasTable('costs')) {
            Schema::create('costs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->unsignedBigInteger('storehouse_id')->nullable();
                $table->string('cost_type')->nullable();
                $table->string('item_code')->nullable();
                $table->decimal('quantity', 12, 2)->nullable();
                $table->string('unit')->nullable();
                $table->decimal('price_per_unit', 12, 2)->nullable();
                $table->decimal('transport_cost', 12, 2)->nullable();
                $table->decimal('total_price', 14, 2)->nullable();
                $table->string('receipt_file')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('date')->nullable();
                $table->timestamps();
            });
        }

        // pig_entry_records
        if (!Schema::hasTable('pig_entry_records')) {
            Schema::create('pig_entry_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->dateTime('pig_entry_date')->nullable();
                $table->integer('quantity')->nullable();
                $table->decimal('total_weight', 12, 2)->nullable();
                $table->decimal('excess_weight', 12, 2)->default(0);
                $table->decimal('excess_weight_cost', 12, 2)->default(0);
                $table->decimal('price_per_pig', 12, 2)->default(0);
                $table->decimal('transport_cost', 12, 2)->default(0);
                $table->string('receipt_file')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // pig_deaths
        if (!Schema::hasTable('pig_deaths')) {
            Schema::create('pig_deaths', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->unsignedBigInteger('pen_id')->nullable();
                $table->unsignedBigInteger('dairy_record_id')->nullable();
                $table->integer('quantity')->nullable();
                $table->string('cause')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('date')->nullable();
                $table->timestamps();
            });
        }

        // pig_sales
        if (!Schema::hasTable('pig_sales')) {
            Schema::create('pig_sales', function (Blueprint $table) {
                $table->id();
                $table->string('sale_number')->nullable();
                $table->unsignedBigInteger('farm_id')->nullable();
                $table->unsignedBigInteger('batch_id')->nullable();
                $table->unsignedBigInteger('pen_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('pig_loss_id')->nullable();
                $table->dateTime('sell_date')->nullable();
                $table->integer('quantity')->nullable();
                $table->decimal('total_weight', 14, 2)->nullable();
                $table->decimal('estimated_weight', 14, 2)->nullable();
                $table->decimal('actual_weight', 14, 2)->nullable();
                $table->decimal('avg_weight_per_pig', 12, 2)->nullable();
                $table->decimal('price_per_kg', 12, 2)->nullable();
                $table->decimal('price_per_pig', 12, 2)->nullable();
                $table->decimal('cpf_reference_price', 12, 2)->nullable();
                $table->dateTime('cpf_reference_date')->nullable();
                $table->decimal('total_price', 14, 2)->nullable();
                $table->decimal('discount', 14, 2)->nullable();
                $table->decimal('shipping_cost', 14, 2)->nullable();
                $table->decimal('net_total', 14, 2)->nullable();
                $table->string('payment_method')->nullable();
                $table->string('payment_term')->nullable();
                $table->string('payment_status')->nullable();
                $table->decimal('paid_amount', 14, 2)->nullable();
                $table->decimal('balance', 14, 2)->nullable();
                $table->dateTime('due_date')->nullable();
                $table->dateTime('paid_date')->nullable();
                $table->string('invoice_number')->nullable();
                $table->string('receipt_number')->nullable();
                $table->string('receipt_file')->nullable();
                $table->string('buyer_name')->nullable();
                $table->text('note')->nullable();
                $table->dateTime('date')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('rejected_by')->nullable();
                $table->dateTime('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
            });
        }

        // pig_sale_details
        if (!Schema::hasTable('pig_sale_details') && !Schema::hasTable('pig_sell_details')) {
            Schema::create('pig_sale_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pig_sale_id')->nullable();
                $table->unsignedBigInteger('pen_id')->nullable();
                $table->integer('quantity')->nullable();
                $table->timestamps();
            });
        }

        // customers
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('customer_code')->unique()->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_type')->nullable();
                $table->string('phone')->nullable();
                $table->string('line_id')->nullable();
                $table->text('address')->nullable();
                $table->string('tax_id')->nullable();
                $table->string('branch')->nullable();
                $table->integer('credit_days')->nullable();
                $table->decimal('credit_limit', 14, 2)->nullable();
                $table->decimal('total_purchased', 14, 2)->nullable();
                $table->decimal('total_outstanding', 14, 2)->nullable();
                $table->integer('total_orders')->nullable();
                $table->date('last_purchase_date')->nullable();
                $table->text('note')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // payments
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pig_sale_id')->nullable();
                $table->string('payment_number')->nullable();
                $table->dateTime('payment_date')->nullable();
                $table->decimal('amount', 14, 2)->nullable();
                $table->string('payment_method')->nullable();
                $table->string('reference_number')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('receipt_file')->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->timestamps();
            });
        }

        // notifications
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('related_user_id')->nullable();
                $table->string('title')->nullable();
                $table->text('message')->nullable();
                $table->string('url')->nullable();
                $table->boolean('is_read')->default(false);
                $table->dateTime('read_at')->nullable();
                $table->timestamps();
            });
        }

        // roles, permissions, role_user, role_permission: usually provided by other migrations

        // users: add basic users table if missing
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     * Add-only migration: we intentionally do not remove created tables on rollback to avoid data loss.
     */
    public function down(): void
    {
        // No destructive rollback
    }
};
