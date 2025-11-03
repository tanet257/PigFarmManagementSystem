<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTreatmentDetail extends Model
{
    use HasFactory;

    protected $table = 'batch_treatment_details';

    protected $fillable = [
        'batch_treatment_id',
        'pen_id',
        'barn_id',
        'treatment_date',
        'quantity_used',
        'unit',
        'note',
        'applied_by',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'quantity_used' => 'decimal:2',
    ];

    /**
     * Relationship: Belongs to BatchTreatment
     */
    public function batchTreatment()
    {
        return $this->belongsTo(BatchTreatment::class, 'batch_treatment_id');
    }

    /**
     * Relationship: Belongs to Pen
     */
    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

    /**
     * Relationship: Belongs to Barn
     */
    public function barn()
    {
        return $this->belongsTo(Barn::class);
    }
}
