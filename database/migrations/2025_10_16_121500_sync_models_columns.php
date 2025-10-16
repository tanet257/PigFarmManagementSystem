<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add missing columns for models in a safe, idempotent way.
     *
     * @return void
     */
    public function up(): void
    {
        // farms
        if (Schema::hasTable('farms')) {
            Schema::table('farms', function (Blueprint $table) {
                if (!Schema::hasColumn('farms', 'farm_name')) {
                    $table->string('farm_name')->unique()->nullable();
                }
                if (!Schema::hasColumn('farms', 'barn_capacity')) {
                    $table->unsignedInteger('barn_capacity')->nullable();
                }
            });
        }

        // barns
        if (Schema::hasTable('barns')) {
            Schema::table('barns', function (Blueprint $table) {
                if (!Schema::hasColumn('barns', 'farm_id')) {
                    $table->unsignedBigInteger('farm_id')->nullable();
                }
                if (!Schema::hasColumn('barns', 'barn_code')) {
                    $table->string('barn_code')->unique()->nullable();
                }
                if (!Schema::hasColumn('barns', 'pig_capacity')) {
                    $table->unsignedInteger('pig_capacity')->nullable();
                }
                if (!Schema::hasColumn('barns', 'pen_capacity')) {
                    $table->unsignedInteger('pen_capacity')->nullable();
                }
                if (!Schema::hasColumn('barns', 'note')) {
                    $table->text('note')->nullable();
                }
            });
        }

        // pens
        if (Schema::hasTable('pens')) {
            Schema::table('pens', function (Blueprint $table) {
                if (!Schema::hasColumn('pens', 'barn_id')) {
                    $table->unsignedBigInteger('barn_id')->nullable();
                }
                if (!Schema::hasColumn('pens', 'pen_code')) {
                    $table->string('pen_code')->unique()->nullable();
                }
                if (!Schema::hasColumn('pens', 'pig_capacity')) {
                    $table->unsignedInteger('pig_capacity')->nullable();
                }
                if (!Schema::hasColumn('pens', 'status')) {
                    $table->string('status')->nullable();
                }
                if (!Schema::hasColumn('pens', 'note')) {
                    $table->text('note')->nullable();
                }
            });
        }

        // batches
        if (Schema::hasTable('batches')) {
            Schema::table('batches', function (Blueprint $table) {
                if (!Schema::hasColumn('batches', 'barn_id')) {
                    $table->unsignedBigInteger('barn_id')->nullable();
                }
                if (!Schema::hasColumn('batches', 'pen_id')) {
                    $table->unsignedBigInteger('pen_id')->nullable();
                }
                if (!Schema::hasColumn('batches', 'farm_id')) {
                    $table->unsignedBigInteger('farm_id')->nullable();
                }
                if (!Schema::hasColumn('batches', 'batch_code')) {
                    $table->string('batch_code')->unique()->nullable();
                }
                if (!Schema::hasColumn('batches', 'total_pig_weight')) {
                    $table->unsignedDecimal('total_pig_weight', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('batches', 'total_pig_amount')) {
                    $table->unsignedDecimal('total_pig_amount', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('batches', 'total_pig_price')) {
                    $table->unsignedDecimal('total_pig_price', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('batches', 'total_deaths')) {
                    $table->integer('total_deaths')->default(0);
                }
                if (!Schema::hasColumn('batches', 'status')) {
                    $table->string('status')->nullable();
                }
                if (!Schema::hasColumn('batches', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('batches', 'start_date')) {
                    $table->dateTime('start_date')->nullable();
                }
                if (!Schema::hasColumn('batches', 'end_date')) {
                    $table->dateTime('end_date')->nullable();
                }
            });
        }

        // batch_treatments
        if (Schema::hasTable('batch_treatments')) {
            Schema::table('batch_treatments', function (Blueprint $table) {
                if (!Schema::hasColumn('batch_treatments', 'pen_id')) {
                    $table->unsignedBigInteger('pen_id')->nullable();
                }
                if (!Schema::hasColumn('batch_treatments', 'batch_id')) {
                    $table->unsignedBigInteger('batch_id')->nullable();
                }
                if (!Schema::hasColumn('batch_treatments', 'dairy_record_id')) {
                    $table->unsignedBigInteger('dairy_record_id')->nullable();
                }
                if (!Schema::hasColumn('batch_treatments', 'medicine_name')) {
                    $table->string('medicine_name')->nullable();
                }
                if (!Schema::hasColumn('batch_treatments', 'medicine_code')) {
                    $table->string('medicine_code')->nullable();
                }
                if (!Schema::hasColumn('batch_treatments', 'quantity')) {
                    $table->decimal('quantity', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('batch_treatments', 'unit')) {
                    $table->string('unit')->nullable();
                }
                if (!Schema::hasColumn('batch_treatments', 'status')) {
                    $table->string('status')->nullable();
                }
                if (!Schema::hasColumn('batch_treatments', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('batch_treatments', 'date')) {
                    $table->dateTime('date')->nullable();
                }
            });
        }

        // batch_pen_allocations
        if (Schema::hasTable('batch_pen_allocations')) {
            Schema::table('batch_pen_allocations', function (Blueprint $table) {
                if (!Schema::hasColumn('batch_pen_allocations', 'batch_id')) {
                    $table->unsignedBigInteger('batch_id')->nullable();
                }
                if (!Schema::hasColumn('batch_pen_allocations', 'barn_id')) {
                    $table->unsignedBigInteger('barn_id')->nullable();
                }
                if (!Schema::hasColumn('batch_pen_allocations', 'pen_id')) {
                    $table->unsignedBigInteger('pen_id')->nullable();
                }
                if (!Schema::hasColumn('batch_pen_allocations', 'pig_amount')) {
                    $table->integer('pig_amount')->nullable();
                }
                if (!Schema::hasColumn('batch_pen_allocations', 'move_date')) {
                    $table->dateTime('move_date')->nullable();
                }
                if (!Schema::hasColumn('batch_pen_allocations', 'note')) {
                    $table->text('note')->nullable();
                }
            });
        }

        // dairy_records (plural) - ensure fields
        if (Schema::hasTable('dairy_records')) {
            Schema::table('dairy_records', function (Blueprint $table) {
                if (!Schema::hasColumn('dairy_records', 'batch_id')) {
                    $table->unsignedBigInteger('batch_id')->nullable();
                }
                if (!Schema::hasColumn('dairy_records', 'barn_id')) {
                    $table->unsignedBigInteger('barn_id')->nullable();
                }
                if (!Schema::hasColumn('dairy_records', 'pen_id')) {
                    $table->unsignedBigInteger('pen_id')->nullable();
                }
                if (!Schema::hasColumn('dairy_records', 'date')) {
                    $table->dateTime('date')->nullable();
                }
                if (!Schema::hasColumn('dairy_records', 'note')) {
                    $table->text('note')->nullable();
                }
            });
        }

        // dairy_storehouse_uses
        if (Schema::hasTable('dairy_storehouse_uses')) {
            Schema::table('dairy_storehouse_uses', function (Blueprint $table) {
                if (!Schema::hasColumn('dairy_storehouse_uses', 'dairy_record_id')) {
                    $table->unsignedBigInteger('dairy_record_id')->nullable();
                }
                if (!Schema::hasColumn('dairy_storehouse_uses', 'storehouse_id')) {
                    $table->unsignedBigInteger('storehouse_id')->nullable();
                }
                if (!Schema::hasColumn('dairy_storehouse_uses', 'barn_id')) {
                    $table->unsignedBigInteger('barn_id')->nullable();
                }
                if (!Schema::hasColumn('dairy_storehouse_uses', 'quantity')) {
                    $table->decimal('quantity', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('dairy_storehouse_uses', 'date')) {
                    $table->dateTime('date')->nullable();
                }
                if (!Schema::hasColumn('dairy_storehouse_uses', 'note')) {
                    $table->text('note')->nullable();
                }
            });
        }

        // storehouses
        if (Schema::hasTable('storehouses')) {
            Schema::table('storehouses', function (Blueprint $table) {
                if (!Schema::hasColumn('storehouses', 'farm_id')) {
                    $table->unsignedBigInteger('farm_id')->nullable();
                }
                if (!Schema::hasColumn('storehouses', 'batch_id')) {
                    $table->unsignedBigInteger('batch_id')->nullable();
                }
                if (!Schema::hasColumn('storehouses', 'item_type')) {
                    $table->string('item_type')->nullable();
                }
                if (!Schema::hasColumn('storehouses', 'item_code')) {
                    $table->string('item_code')->unique()->nullable();
                }
                if (!Schema::hasColumn('storehouses', 'item_name')) {
                    $table->string('item_name')->nullable();
                }
                if (!Schema::hasColumn('storehouses', 'stock')) {
                    $table->decimal('stock', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('storehouses', 'min_quantity')) {
                    $table->decimal('min_quantity', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('storehouses', 'unit')) {
                    $table->string('unit')->nullable();
                }
                if (!Schema::hasColumn('storehouses', 'status')) {
                    $table->string('status')->nullable();
                }
                if (!Schema::hasColumn('storehouses', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('storehouses', 'date')) {
                    $table->dateTime('date')->nullable();
                }
            });
        }

        // inventory_movements
        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('inventory_movements', 'storehouse_id')) {
                    $table->unsignedBigInteger('storehouse_id')->nullable();
                }
                if (!Schema::hasColumn('inventory_movements', 'batch_id')) {
                    $table->unsignedBigInteger('batch_id')->nullable();
                }
                if (!Schema::hasColumn('inventory_movements', 'barn_id')) {
                    $table->unsignedBigInteger('barn_id')->nullable();
                }
                if (!Schema::hasColumn('inventory_movements', 'change_type')) {
                    $table->string('change_type')->nullable();
                }
                if (!Schema::hasColumn('inventory_movements', 'quantity')) {
                    $table->decimal('quantity', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('inventory_movements', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('inventory_movements', 'date')) {
                    $table->dateTime('date')->nullable();
                }
            });
        }

        // costs
        if (Schema::hasTable('costs')) {
            Schema::table('costs', function (Blueprint $table) {
                if (!Schema::hasColumn('costs', 'farm_id')) {
                    $table->unsignedBigInteger('farm_id')->nullable();
                }
                if (!Schema::hasColumn('costs', 'batch_id')) {
                    $table->unsignedBigInteger('batch_id')->nullable();
                }
                if (!Schema::hasColumn('costs', 'storehouse_id')) {
                    $table->unsignedBigInteger('storehouse_id')->nullable();
                }
                if (!Schema::hasColumn('costs', 'cost_type')) {
                    $table->string('cost_type')->nullable();
                }
                if (!Schema::hasColumn('costs', 'item_code')) {
                    $table->string('item_code')->nullable();
                }
                if (!Schema::hasColumn('costs', 'quantity')) {
                    $table->decimal('quantity', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('costs', 'unit')) {
                    $table->string('unit')->nullable();
                }
                if (!Schema::hasColumn('costs', 'price_per_unit')) {
                    $table->decimal('price_per_unit', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('costs', 'transport_cost')) {
                    $table->decimal('transport_cost', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('costs', 'total_price')) {
                    $table->decimal('total_price', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('costs', 'receipt_file')) {
                    $table->string('receipt_file')->nullable();
                }
                if (!Schema::hasColumn('costs', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('costs', 'date')) {
                    $table->dateTime('date')->nullable();
                }
            });
        }

        // pig_entry_records
        if (Schema::hasTable('pig_entry_records')) {
            Schema::table('pig_entry_records', function (Blueprint $table) {
                if (!Schema::hasColumn('pig_entry_records', 'farm_id')) {
                    $table->unsignedBigInteger('farm_id')->nullable();
                }
                if (!Schema::hasColumn('pig_entry_records', 'batch_id')) {
                    $table->unsignedBigInteger('batch_id')->nullable();
                }
                if (!Schema::hasColumn('pig_entry_records', 'pig_entry_date')) {
                    $table->dateTime('pig_entry_date')->nullable();
                }
                if (!Schema::hasColumn('pig_entry_records', 'total_pig_amount')) {
                    $table->integer('total_pig_amount')->nullable();
                }
                if (!Schema::hasColumn('pig_entry_records', 'total_pig_weight')) {
                    $table->decimal('total_pig_weight', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('pig_entry_records', 'total_pig_price')) {
                    $table->decimal('total_pig_price', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('pig_entry_records', 'note')) {
                    $table->text('note')->nullable();
                }
            });
        }

        // pig_deaths
        if (Schema::hasTable('pig_deaths')) {
            Schema::table('pig_deaths', function (Blueprint $table) {
                if (!Schema::hasColumn('pig_deaths', 'batch_id')) {
                    $table->unsignedBigInteger('batch_id')->nullable();
                }
                if (!Schema::hasColumn('pig_deaths', 'pen_id')) {
                    $table->unsignedBigInteger('pen_id')->nullable();
                }
                if (!Schema::hasColumn('pig_deaths', 'dairy_record_id')) {
                    $table->unsignedBigInteger('dairy_record_id')->nullable();
                }
                if (!Schema::hasColumn('pig_deaths', 'quantity')) {
                    $table->integer('quantity')->nullable();
                }
                if (!Schema::hasColumn('pig_deaths', 'cause')) {
                    $table->string('cause')->nullable();
                }
                if (!Schema::hasColumn('pig_deaths', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('pig_deaths', 'date')) {
                    $table->dateTime('date')->nullable();
                }
            });
        }

        // pig_sales (pig_sales)
        if (Schema::hasTable('pig_sales')) {
            Schema::table('pig_sales', function (Blueprint $table) {
                $cols = [
                    'sale_number',
                    'farm_id',
                    'batch_id',
                    'pen_id',
                    'customer_id',
                    'pig_loss_id',
                    'sell_date',
                    'quantity',
                    'total_weight',
                    'estimated_weight',
                    'actual_weight',
                    'avg_weight_per_pig',
                    'price_per_kg',
                    'price_per_pig',
                    'cpf_reference_price',
                    'cpf_reference_date',
                    'total_price',
                    'discount',
                    'shipping_cost',
                    'net_total',
                    'payment_method',
                    'payment_term',
                    'payment_status',
                    'paid_amount',
                    'balance',
                    'due_date',
                    'paid_date',
                    'invoice_number',
                    'receipt_number',
                    'receipt_file',
                    'buyer_name',
                    'note',
                    'date',
                    'created_by',
                    'approved_by',
                    'approved_at',
                    'status',
                    'rejected_by',
                    'rejected_at',
                    'rejection_reason'
                ];
                foreach ($cols as $c) {
                    if (!Schema::hasColumn('pig_sales', $c)) {
                        // add reasonable defaults based on name
                        if (str_contains($c, 'date') || str_contains($c, 'at')) {
                            $table->dateTime($c)->nullable();
                        } elseif (str_contains($c, 'price') || str_contains($c, 'total') || str_contains($c, 'cost') || str_contains($c, 'amount') || str_contains($c, 'weight') || str_contains($c, 'paid') || str_contains($c, 'balance') || str_contains($c, 'discount') || str_contains($c, 'shipping')) {
                            $table->decimal($c, 14, 2)->nullable();
                        } elseif (in_array($c, ['quantity'])) {
                            $table->integer($c)->nullable();
                        } elseif (in_array($c, ['created_by', 'approved_by', 'rejected_by', 'customer_id', 'farm_id', 'batch_id', 'pen_id', 'pig_loss_id'])) {
                            $table->unsignedBigInteger($c)->nullable();
                        } else {
                            $table->string($c)->nullable();
                        }
                    }
                }
            });
        }

        // pig_sale_details
        if (Schema::hasTable('pig_sell_details') || Schema::hasTable('pig_sale_details')) {
            $name = Schema::hasTable('pig_sell_details') ? 'pig_sell_details' : 'pig_sale_details';
            Schema::table($name, function (Blueprint $table) use ($name) {
                if (!Schema::hasColumn($name, 'pig_sale_id') && !Schema::hasColumn($name, 'pig_sell_id')) {
                    $table->unsignedBigInteger('pig_sale_id')->nullable();
                }
                if (!Schema::hasColumn($name, 'pen_id')) {
                    $table->unsignedBigInteger('pen_id')->nullable();
                }
                if (!Schema::hasColumn($name, 'quantity')) {
                    $table->integer('quantity')->nullable();
                }
            });
        }

        // customers
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'customer_code')) {
                    $table->string('customer_code')->unique()->nullable();
                }
                if (!Schema::hasColumn('customers', 'customer_name')) {
                    $table->string('customer_name')->nullable();
                }
                if (!Schema::hasColumn('customers', 'customer_type')) {
                    $table->string('customer_type')->nullable();
                }
                if (!Schema::hasColumn('customers', 'phone')) {
                    $table->string('phone')->nullable();
                }
                if (!Schema::hasColumn('customers', 'line_id')) {
                    $table->string('line_id')->nullable();
                }
                if (!Schema::hasColumn('customers', 'address')) {
                    $table->text('address')->nullable();
                }
                if (!Schema::hasColumn('customers', 'tax_id')) {
                    $table->string('tax_id')->nullable();
                }
                if (!Schema::hasColumn('customers', 'credit_days')) {
                    $table->integer('credit_days')->nullable();
                }
                if (!Schema::hasColumn('customers', 'credit_limit')) {
                    $table->decimal('credit_limit', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('customers', 'total_purchased')) {
                    $table->decimal('total_purchased', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('customers', 'total_outstanding')) {
                    $table->decimal('total_outstanding', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('customers', 'total_orders')) {
                    $table->integer('total_orders')->nullable();
                }
                if (!Schema::hasColumn('customers', 'last_purchase_date')) {
                    $table->date('last_purchase_date')->nullable();
                }
                if (!Schema::hasColumn('customers', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
            });
        }

        // payments
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'pig_sell_id') && !Schema::hasColumn('payments', 'pig_sale_id')) {
                    $table->unsignedBigInteger('pig_sell_id')->nullable();
                }
                if (!Schema::hasColumn('payments', 'payment_number')) {
                    $table->string('payment_number')->nullable();
                }
                if (!Schema::hasColumn('payments', 'payment_date')) {
                    $table->dateTime('payment_date')->nullable();
                }
                if (!Schema::hasColumn('payments', 'amount')) {
                    $table->decimal('amount', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('payments', 'payment_method')) {
                    $table->string('payment_method')->nullable();
                }
            });
        }

        // notifications
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('notifications', 'type')) {
                    $table->string('type')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'related_user_id')) {
                    $table->unsignedBigInteger('related_user_id')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'title')) {
                    $table->string('title')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'message')) {
                    $table->text('message')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'url')) {
                    $table->string('url')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'is_read')) {
                    $table->boolean('is_read')->default(false);
                }
                if (!Schema::hasColumn('notifications', 'read_at')) {
                    $table->dateTime('read_at')->nullable();
                }
            });
        }

        // roles, permissions, role_user, role_permission handled by existing migrations

        // users: add commonly used columns if missing
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $maybe = [
                    'phone' => 'string',
                    'address' => 'text',
                    'status' => 'string',
                    'approved_by' => 'unsignedBigInteger',
                    'approved_at' => 'dateTime',
                    'rejection_reason' => 'text',
                ];
                foreach ($maybe as $col => $type) {
                    if (!Schema::hasColumn('users', $col)) {
                        switch ($type) {
                            case 'string':
                                $table->string($col)->nullable();
                                break;
                            case 'text':
                                $table->text($col)->nullable();
                                break;
                            case 'unsignedBigInteger':
                                $table->unsignedBigInteger($col)->nullable();
                                break;
                            case 'dateTime':
                                $table->dateTime($col)->nullable();
                                break;
                        }
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     * This migration is additive-only (we do not remove columns on rollback to avoid data loss).
     *
     * @return void
     */
    public function down(): void
    {
        // No destructive rollback: manual cleanup required if needed.
    }
};
