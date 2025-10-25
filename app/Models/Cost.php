<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'batch_id',
        'pig_entry_record_id',
        'storehouse_id',
        'cost_type',
        'item_code',
        'quantity',
        'unit',
        'price_per_unit',
        'transport_cost',
        'excess_weight_cost',
        'total_price',
        'receipt_file',
        'note',
        'date',
    ];

    // Cast receipt_file to ensure it's always a string
    protected $casts = [
        'receipt_file' => 'string',
    ];

    // Accessor to ensure receipt_file is always a string
    public function getReceiptFileAttribute($value)
    {
        return (string) $value;
    }

    // ------------ Relationships ------------ //

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function storehouse()
    {
        return $this->belongsTo(StoreHouse::class);
    }

    public function pigEntryRecord()
    {
        return $this->belongsTo(PigEntryRecord::class, 'pig_entry_record_id');
    }

    public function payments()
    {
        return $this->hasMany(CostPayment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(CostPayment::class)->latestOfMany();
    }
}
