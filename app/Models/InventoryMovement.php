<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'storehouse_id',
        'batch_id',
        'barn_id',
        'change_type', // in หรือ out
        'quantity',
        'note',
        'date',
    ];

    //เป็นของ storehouse และ batch แต่ละรุ่น
    public function storehouse()
    {
        return $this->belongsTo(StoreHouse::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function barn()
    {
        return $this->belongsTo(Barn::class);
    }
}
