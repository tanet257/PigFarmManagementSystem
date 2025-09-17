<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'storehouse_id',
        'change_type', // in หรือ out
        'quantity',
        'note',
        'date',
    ];

    public function storehouse()
    {
        return $this->belongsTo(StoreHouse::class);
    }
}
