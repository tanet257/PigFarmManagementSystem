<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PigEntryDetail extends Model
{
    use HasFactory;

    protected $table = 'pig_entry_details';

    protected $fillable = [
        'pig_entry_id',
        'batch_id',
        'barn_id',
        'pen_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Relationships
    public function pigEntry()
    {
        return $this->belongsTo(PigEntryRecord::class, 'pig_entry_id');
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
