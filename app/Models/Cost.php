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
}
