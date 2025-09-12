<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PigEntryRecord extends Model
{
    use HasFactory;

    protected $table = 'pig_entry_records'; // ชื่อตาราง

    protected $fillable = [
        'farm_id',
        'batch_id',

        'pig_entry_date',
        'total_pig_amount',
        'total_pig_weight',
        'total_pig_price',
        'receipt_file',
        'note',
    ];

    // ------------ Relationships ------------ //

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function costs()
    {
        return $this->hasMany(Cost::class, 'batch_id', 'batch_id');
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

}
