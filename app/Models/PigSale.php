<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PigSale extends Model
{
    use HasFactory;

    protected $table = 'pig_sales'; // ชื่อตารางในฐานข้อมูล

    protected $fillable = [
        // IDs
        'sale_number',
        'farm_id',
        'batch_id',
        'pen_id',
        'customer_id',
        'pig_loss_id',

        // Sale Details
        'date',
        'sell_type',
        'quantity',

        // Weight Information
        'total_weight',
        'estimated_weight',
        'actual_weight',
        'avg_weight_per_pig',

        // Pricing
        'price_per_kg',
        'price_per_pig',
        'cpf_reference_price',
        'cpf_reference_date',
        'total_price',
        'discount',
        'shipping_cost',
        'net_total',

        // Payment Information
        'payment_method',
        'payment_term',
        'due_date',

        // Documents
        'invoice_number',
        'receipt_number',
        'receipt_file',

        // Additional Info
        'buyer_name',
        'note',
        'date',

        // Approval
        'created_by',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'cpf_reference_date' => 'date',
        'due_date' => 'date',
        'total_weight' => 'decimal:2',
        'estimated_weight' => 'decimal:2',
        'actual_weight' => 'decimal:2',
        'avg_weight_per_pig' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'price_per_pig' => 'decimal:2',
        'cpf_reference_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'net_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // ------------ Relationships ------------ //

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pigLoss()
    {
        return $this->belongsTo(PigDeath::class, 'pig_loss_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details()
    {
        return $this->hasMany(PigSaleDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function pigDeath()
    {
        return $this->belongsTo(PigDeath::class);
    }

    /**
     * Boot method to handle cascading deletes
     */
    protected static function boot()
    {
        parent::boot();

        // ลบ pig_sale_details เมื่อลบ pig_sale
        static::deleting(function ($pigSale) {
            PigSaleDetail::where('pig_sale_id', $pigSale->id)->delete();
        });

        // ลบ payments เมื่อลบ pig_sale
        static::deleting(function ($pigSale) {
            Payment::where('pig_sale_id', $pigSale->id)->delete();
        });

        // อัปเดต Batch เมื่อบันทึกการขาย
        static::saved(function ($pigSale) {
            if ($pigSale->batch && $pigSale->status !== 'cancelled' && $pigSale->status !== 'rejected') {
                $pigSale->batch->calculateTotalSaleWeight();
            }
        });

        // อัปเดต Batch เมื่อลบการขาย
        static::deleted(function ($pigSale) {
            if ($pigSale->batch) {
                $pigSale->batch->calculateTotalSaleWeight();
            }
        });
    }
}
