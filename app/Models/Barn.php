<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barn extends Model
{
    use HasFactory;

    protected $table = 'barns'; // ชื่อตาราง

    protected $fillable = [
        'farm_id',


        'barn_code',
        'pig_capacity',
        'pen_capacity',
        'note'
    ];
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function farm()
{
    return $this->belongsTo(Farm::class);
}
    public function pens()
    {
        return $this->hasMany(Pen::class, 'barn_id', 'id');
    }
}
