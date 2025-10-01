<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $table = 'batches';

    protected $fillable = [
        'farm_id',
        'batch_code',
        'total_pig_weight',
        'total_pig_amount',
        'total_pig_price',
        'status',
        'note',
        'start_date',
        'end_date'
    ];

    // Relation กับ Farm
    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id');
    }

    // Relation กับ PigEntryRecord
    public function pig_entry_records()
    {
        return $this->hasMany(PigEntryRecord::class, 'batch_id');
    }

    public function costs()
    {
        return $this->hasMany(Cost::class, 'batch_id', 'id');
    }

    //ใช้เพื่อให้เรียกดูได้ง่ายว่า batch นี่อยู่เล้าไหนคอกไหน
    public function allocations()
{
    return $this->hasMany(BatchPenAllocation::class, 'batch_id');
}
    public function inventory_movements()
{
    return $this->hasMany(InventoryMovement::class, 'batch_id', 'id');
}

}
