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
        'allocated_pigs',
        'current_quantity',
        'move_date',
        'note',
    ];

    // ----------- Relationships ----------- //

    /*public function batches()
    {
        return $this->belongsTo(Batch::class, 'batch_pen_allocations')
            ->withPivot(['allocated_at', 'deallocated_at'])
            ->withTimestamps();
    }

    public function pens()
    {
        return $this->belongsTo(Pen::class, 'batch_pen_allocations')
            ->withPivot(['allocated_at', 'deallocated_at'])
            ->withTimestamps();
    }*/

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
