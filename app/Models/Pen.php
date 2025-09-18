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

}
