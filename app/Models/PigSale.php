<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PigSale extends Model
{
    use HasFactory;

    protected $table = 'pig_sales'; // ชื่อตาราง

    protected $fillable = [
        'customer_id',
        'sale_number',
        'farm_id',
        'batch_id',
        'pen_id',
        'pig_loss_id',
        'sell_date',
        'sell_type',
        'quantity',
        'total_weight',
        'estimated_weight',
        'actual_weight',
        'avg_weight_per_pig',
        'price_per_kg',
        'price_per_pig',
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
        'buyer_name',
        'note',
        'sale_status',
        'receipt_file',
        'date',
        'cpf_reference_price',
        'cpf_reference_date',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'sell_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'cpf_reference_date' => 'date',
        'approved_at' => 'datetime',
        'total_weight' => 'decimal:2',
        'estimated_weight' => 'decimal:2',
        'actual_weight' => 'decimal:2',
        'avg_weight_per_pig' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'net_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'cpf_reference_price' => 'decimal:2',
    ];

    // ------------ Relationships ------------ //

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

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

    public function details()
    {
        return $this->hasMany(PigSellDetail::class, 'pig_sell_id');
    }

    public function barn()
    {
        // ดึง barn ผ่าน pen
        return $this->hasOneThrough(
            Barn::class,
            Pen::class,
            'id', // Foreign key on pens table
            'id', // Foreign key on barns table
            'pen_id', // Local key on pig_sells table
            'barn_id' // Local key on pens table
        );
    }

    public function pigLoss()
    {
        return $this->belongsTo(PigDeath::class, 'pig_loss_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ------------ Methods ------------ //

    /**
     * สร้างเลขที่ใบขายอัตโนมัติ
     */
    public static function generateSaleNumber()
    {
        $lastSale = self::latest('id')->first();
        $number = $lastSale ? (int) substr($lastSale->sale_number, -3) + 1 : 1;
        return 'SELL-' . date('Y') . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * คำนวณราคาสุทธิ (total_price - discount + shipping_cost)
     */
    public function calculateNetTotal()
    {
        return $this->total_price - $this->discount + $this->shipping_cost;
    }

    /**
     * คำนวณยอดคงเหลือ
     */
    public function calculateBalance()
    {
        return $this->net_total - $this->paid_amount;
    }

    /**
     * ตรวจสอบว่าชำระครบแล้วหรือยัง
     */
    public function isPaid()
    {
        return $this->balance <= 0;
    }

    /**
     * ตรวจสอบว่าเกินกำหนดชำระหรือไม่
     */
    public function isOverdue()
    {
        if (!$this->due_date || $this->isPaid()) {
            return false;
        }
        return now()->gt($this->due_date);
    }
}
