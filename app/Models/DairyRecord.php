<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DairyRecord extends Model
{
    use HasFactory;

    protected $table = 'dairy_records'; // ชื่อตาราง

    protected $fillable = [
        'farm_id',
        'batch_id',
        'barn_id',
        'pen_id',

        'record_date',
        'food_type',
        'food_amount',
        'medicine_name',
        'dosage',
        'dead_pigs',
        'cause',
        'note',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function barn()
    {
        return $this->belongsTo(Barn::class);
    }

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }
}
