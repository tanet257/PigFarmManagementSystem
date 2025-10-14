<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PigSale extends Model
{
    use HasFactory;

    protected $table = 'pig_sells'; // ชื่อตาราง (ยังใช้ pig_sells ในฐานข้อมูล)

    protected $fillable = [
        'farm_id',
        'batch_id',
        'pig_death_id',

        'sell_type',
        'quantity',
        'total_weight',
        'price_per_kg',
        'total_price',
        'buyer',

        'note',
        'date'
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

    public function pigDeath()
    {
        return $this->belongsTo(PigDeath::class);
    }
}
