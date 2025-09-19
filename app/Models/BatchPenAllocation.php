<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchPenAllocation extends Model
{
    use HasFactory;

    protected $table = 'batch_pen_allocations';

    protected $fillable = [
        'batch_id',
        'barn_id',
        'pen_id',
        'pig_amount',
        'move_date',
        'note',
    ];

    // ----------- Relationships ----------- //

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
