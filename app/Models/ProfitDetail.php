<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'profit_id',
        'cost_id',
        'cost_category',
        'item_name',
        'amount',
        'note',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    // ความสัมพันธ์
    public function profit()
    {
        return $this->belongsTo(Profit::class);
    }

    public function cost()
    {
        return $this->belongsTo(Cost::class);
    }
}
