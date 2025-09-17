<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTreatment extends Model
{
    use HasFactory;

    protected $table = 'batch_treatments'; // ชื่อตาราง

    protected $fillable = [
        'barn_id',
        'pen_id',
        'batch_id',
        'farm_id',

        'medicine_name',
        'medicine_code',
        'quantity',
        'unit',
        'status',
        'note',
        'date',
    ];

    //---------------relation ship------------------------//
    public function barn()
    {
        return $this->belongsTo(Barn::class);
    }

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }


}
