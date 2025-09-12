<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreHouse extends Model
{
    use HasFactory;

    protected $table = 'storehouses'; // ชื่อตาราง

    protected $fillable = [
        'farm_id',
        'batch_id',
        'item_type',
        'item_code',
        'item_name',
        'quantity',
        'unit',
        'pig_capacity',
        'status',

        'note',
        'date'
    ];

    // ------------ Relationships ------------ //

    public function batches()
    {
        return $this->hasMany(Batch::class, 'farm_id', 'id');
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
