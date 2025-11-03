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
        'cost_id',
        'batch_treatment_id',
        'change_type', // in หรือ out
        'quantity',
        'quantity_unit',
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

    public function cost()
    {
        return $this->belongsTo(Cost::class);
    }

    public function batchTreatment()
    {
        return $this->belongsTo(BatchTreatment::class, 'batch_treatment_id');
    }
}
