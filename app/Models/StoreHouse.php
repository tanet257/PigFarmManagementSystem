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
        'base_unit',
        'conversion_rate',
        'quantity_per_unit',
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

    // ------------ Unit Conversion Methods ------------ //

    /**
     * แปลงจำนวน ml เป็นหน่วยสต็อก (เช่น ขวด, ถัง)
     *
     * @param float $mlAmount จำนวน ml
     * @return float จำนวนในหน่วยสต็อก
     */
    public function convertMlToStockUnit($mlAmount)
    {
        if (!$this->conversion_rate || !$this->base_unit) {
            return $mlAmount; // ถ้าไม่มี conversion rate ให้ return เท่าเดิม
        }

        // ถ้า base_unit เป็น ml ให้แปลงโดยหาร conversion_rate
        if (strtolower($this->base_unit) === 'ml') {
            return $mlAmount / $this->conversion_rate;
        }

        return $mlAmount;
    }

    /**
     * ดึงข้อมูล conversion มาแสดง (เช่น "1 ขวด = 500 ml")
     *
     * @return string|null
     */
    public function getConversionDisplayAttribute()
    {
        if ($this->conversion_rate && $this->base_unit && $this->unit) {
            return "1 {$this->unit} = {$this->conversion_rate} {$this->base_unit}";
        }
        return null;
    }
}
