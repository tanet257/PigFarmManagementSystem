<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTreatmentLog extends Model
{
    use HasFactory;

    protected $table = 'daily_treatment_logs';

    protected $fillable = [
        'batch_treatment_id',
        'treatment_date',
        'quantity_given',
        'unit',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'quantity_given' => 'decimal:2',
    ];

    /**
     * Get the batch treatment that owns this daily log
     */
    public function batchTreatment()
    {
        return $this->belongsTo(BatchTreatment::class, 'batch_treatment_id');
    }

    /**
     * Get the user who recorded this log
     */
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
