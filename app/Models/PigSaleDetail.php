<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PigSaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pig_sale_id',
        'pen_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Relationships
    public function pigSale()
    {
        return $this->belongsTo(PigSale::class, 'pig_sale_id');
    }

    public function pen()
    {
        return $this->belongsTo(Pen::class, 'pen_id');
    }
}
