<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feeding extends Model
{
    use HasFactory;

    protected $table = 'feedings'; // ชื่อตาราง

    protected $fillable = [
        'farm_id',
        'batch_id',

        'feed_type',
        'quantity',
        'unit',
        'price_per_unit',
        'total',
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


}
