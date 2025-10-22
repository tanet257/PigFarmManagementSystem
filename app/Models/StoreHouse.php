<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreHouse extends Model
{
    use HasFactory;

    protected $table = 'storehouses'; // ชื่อตาราง

    protected $fillable = [
        'farm_id',
        'item_type',
        'item_code',
        'item_name',
        'stock',
        'min_quantity',
        'unit',
        'status',
        'note',
        'date',
        'source',
        'created_by',
        'updated_by',
        'reason',
        'cancelled_at',
        'cancelled_by',
        'cost_id',
    ];

    // ------------ Relationships ------------ //

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function costs()
    {
        return $this->hasMany(Cost::class, 'item_code', 'item_code');
    }

    public function latestCost()
    {
        return $this->hasOne(Cost::class, 'item_code', 'item_code')->latestOfMany('updated_at');
    }

    public function auditLogs()
    {
        return $this->hasMany(StoreHouseAuditLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function cost()
    {
        return $this->belongsTo(Cost::class);
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
