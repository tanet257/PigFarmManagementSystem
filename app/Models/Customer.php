<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'customer_name',
        'customer_type',
        'phone',
        'line_id',
        'address',
        'tax_id',
        'branch',
        'credit_days',
        'credit_limit',
        'total_purchased',
        'total_outstanding',
        'total_orders',
        'last_purchase_date',
        'note',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_days' => 'integer',
        'credit_limit' => 'decimal:2',
        'total_purchased' => 'decimal:2',
        'total_outstanding' => 'decimal:2',
        'total_orders' => 'integer',
        'last_purchase_date' => 'date',
    ];

    // ------------ Relationships ------------ //

    public function pigSales()
    {
        return $this->hasMany(PigSale::class);
    }

    // ------------ Scopes ------------ //

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBrokers($query)
    {
        return $query->where('customer_type', 'นายหน้า');
    }

    // ------------ Methods ------------ //

    /**
     * ตรวจสอบว่ายังมีวงเงินเครดิตเหลือหรือไม่
     */
    public function hasAvailableCredit($amount = 0)
    {
        $available = $this->credit_limit - $this->total_outstanding;
        return $available >= $amount;
    }

    /**
     * อัพเดทสถิติการซื้อ
     */
    public function updatePurchaseStats($amount)
    {
        $this->increment('total_orders');
        $this->increment('total_purchased', $amount);
        $this->update(['last_purchase_date' => now()]);
    }

    /**
     * เพิ่มยอดค้างชำระ
     */
    public function addOutstanding($amount)
    {
        $this->increment('total_outstanding', $amount);
    }

    /**
     * ลดยอดค้างชำระ
     */
    public function reduceOutstanding($amount)
    {
        $this->decrement('total_outstanding', $amount);
    }

    /**
     * สร้างรหัสลูกค้าอัตโนมัติ
     */
    public static function generateCustomerCode()
    {
        $lastCustomer = self::latest('id')->first();
        $number = $lastCustomer ? (int) substr($lastCustomer->customer_code, -3) + 1 : 1;
        return 'CUST-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
