<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PigLoss extends Model
{
    use HasFactory;

    protected $table = 'pig_loss'; // ชื่อตาราง

    protected $fillable = [
        'farm_id',
        'batch_id',
        'pen_id',

        'amount',
        'cause',

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

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

}
