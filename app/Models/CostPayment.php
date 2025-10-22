<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_id',
        'amount',
        'status',
        'approved_date',
        'approved_by',
        'reason',
        'action_type',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_date' => 'datetime',
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

