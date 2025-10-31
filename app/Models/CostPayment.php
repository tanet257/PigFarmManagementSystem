<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cost_id',
        'amount',
        'status',
        'approved_date',
        'approved_by',
        'payment_method',
        'payment_date',
        'reference_number',
        'bank_name',
        'receipt_file',
        'note',
        'reason',
        'action_type',
        'rejected_at',
        'rejected_by',
        'reject_reason',
        'recorded_by',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_date' => 'datetime',
        'payment_date' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ------------ Relationships ------------ //

    public function cost()
    {
        return $this->belongsTo(Cost::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // ------------ Scopes ------------ //

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}

