<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pig_sell_id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'bank_name',
        'receipt_file',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // ------------ Relationships ------------ //

    public function pigSale()
    {
        return $this->belongsTo(PigSale::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
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
