<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $table = 'batches'; // ชื่อตาราง

    protected $fillable = [

        'barn_id',
        'pen_id',
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

    //---------------relation ship------------------------//
    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id');
    }

    public function pig_entry_records()
    {
        return $this->hasMany(PigEntryRecord::class, 'batch_id');
    }
}


