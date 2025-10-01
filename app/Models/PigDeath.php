<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PigDeath extends Model
{
    use HasFactory;

    protected $table = 'pig_deaths'; // ชื่อตาราง

    protected $fillable = [
        'batch_id',
        'pen_id',
        'dairy_record_id',

        'quantity',
        'cause',

        'note',
        'date'
    ];

    // ------------ Relationships ------------ //


    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function pen()
{
    return $this->belongsTo(Pen::class, 'pen_id', 'id'); // pen_id ใน batch_treatments ชี้ไป id ของ pens
}


    public function dairy_record()
    {
        return $this->belongsTo(DairyRecord::class);
    }

}
