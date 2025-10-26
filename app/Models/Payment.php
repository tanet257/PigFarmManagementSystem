<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pig_sale_id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'bank_name',
        'receipt_file',
        'note',
        'status',
        'recorded_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // ------------ Relationships ------------ //

    public function pigSale()
    {
        return $this->belongsTo(PigSale::class, 'pig_sale_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approvedByUser()
    {
        // approved_by is stored as user name (varchar) in database
        return $this->belongsTo(User::class, 'approved_by', 'name');
    }

    public function rejectedByUser()
    {
        // rejected_by is stored as user name (varchar) in database
        return $this->belongsTo(User::class, 'rejected_by', 'name');
    }

    // ------------ Methods ------------ //

    /**
     * สร้างเลขที่การชำระอัตโนมัติ
     */
    public static function generatePaymentNumber()
    {
        $lastPayment = self::latest('id')->first();
        $number = $lastPayment ? (int) substr($lastPayment->payment_number, -3) + 1 : 1;
        return 'PAY-' . date('Y') . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
