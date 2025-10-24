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
        'date',
        'status',      // ✅ NEW: recorded, sold, disposed
        'recorded_by', // ✅ NEW: user_id ของคนที่บันทึก
    ];

    // ------------ Relationships ------------ //


    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function pen()
    {
        return $this->belongsTo(Pen::class, 'pen_id', 'id');
    }

    public function dairy_record()
    {
        return $this->belongsTo(DairyRecord::class);
    }

    // ✅ NEW: ใครที่บันทึก
    public function recordedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }

}
