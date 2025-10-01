<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pen extends Model
{
    use HasFactory;

    protected $table = 'pens'; // ชื่อตาราง

    protected $fillable = [
        'barn_id',

        'pen_code',
        'pig_capacity',
        'status',

        'note',
        'date'
    ];

    // ------------ Relationships ------------ //

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
    public function barn()
    {
        return $this->belongsTo(Barn::class);
    }

    //ใช้เพื่อให้เรียกดูได้ง่ายว่า batch นี่อยู่เล้าไหนคอกไหน
    public function batchPenAllocations()
{
    return $this->hasMany(BatchPenAllocation::class, 'pen_id');
}


}
