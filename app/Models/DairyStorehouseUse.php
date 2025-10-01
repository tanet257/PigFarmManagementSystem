<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DairyStorehouseUse extends Model
{
    use HasFactory;

    protected $table = 'dairy_storehouse_uses';

    protected $fillable = [
        'dairy_record_id',
        'storehouse_id',
        'barn_id',
        'quantity',
        'date',
        'note',
    ];

    protected $dates = ['date'];

    // ----------------- Relationships -----------------

    public function dairyRecord()
    {
        return $this->belongsTo(DairyRecord::class, 'dairy_record_id');
    }

    public function storehouse()
    {
        return $this->belongsTo(Storehouse::class, 'storehouse_id');
    }

    public function barn()
    {
        return $this->belongsTo(Barn::class, 'barn_id');
    }
}
