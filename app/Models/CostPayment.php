<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_id',
        'cost_type',  // ✅ NEW: for auto-approval logic (feed/medicine auto-approve)
        'amount',
        'status',
        'approved_date',
        'approved_by',
        'rejected_at',  // ✅ CHANGED from cancelled_at
        'rejected_by',  // ✅ CHANGED from cancelled_by
        'reason',
        'action_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_date' => 'datetime',
        'rejected_at' => 'datetime',  // ✅ CHANGED from cancelled_at
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

    public function rejecter()  // ✅ CHANGED from canceller
    {
        return $this->belongsTo(User::class, 'rejected_by');
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

