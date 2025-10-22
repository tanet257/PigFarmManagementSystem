<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreHouseAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_house_id',
        'action',
        'change_type',
        'old_quantity',
        'new_quantity',
        'old_price',
        'new_price',
        'user_id',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ------------ Relationships ------------ //

    public function storeHouse()
    {
        return $this->belongsTo(StoreHouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ------------ Scopes ------------ //

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
