<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id',
        'batch_id',
        'pig_sale_id',
        'revenue_type',
        'quantity',
        'unit_price',
        'total_revenue',
        'discount',
        'net_revenue',
        'revenue_date',
        'note',
    ];

    protected $casts = [
        'revenue_date' => 'datetime',
    ];

    // ความสัมพันธ์
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function pigSale()
    {
        return $this->belongsTo(PigSale::class);
    }

    public function profit()
    {
        return $this->hasOne(Profit::class);
    }
}
