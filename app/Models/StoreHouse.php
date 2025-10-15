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
        'stock',
        'min_quantity',
        'unit',
        'status',

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

    public function costs()
    {
        return $this->hasMany(Cost::class, 'item_code', 'item_code');
    }

    public function latestCost()
    {
        return $this->hasOne(Cost::class, 'item_code', 'item_code')->latestOfMany('updated_at');
    }
}
