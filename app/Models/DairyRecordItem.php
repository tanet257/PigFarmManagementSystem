<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DairyRecordItem extends Model
{
    use HasFactory;

    protected $table = 'dairy_record_items';

    protected $fillable = [
        'dairy_record_id',
        'item_type',
        'storehouse_id',
        'medicine_code',
        'batch_id',
        'pen_id',
        'barn_id',
        'quantity',
        'unit',
        'note',
        'treatment_date',
        'treatment_status',
        'death_date',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'death_date' => 'date',
        'quantity' => 'integer',
    ];

    // ==================== Relationships ====================

    public function dairyRecord()
    {
        return $this->belongsTo(DairyRecord::class, 'dairy_record_id');
    }

    public function storehouse()
    {
        return $this->belongsTo(Storehouse::class, 'storehouse_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function pen()
    {
        return $this->belongsTo(Pen::class, 'pen_id');
    }

    public function barn()
    {
        return $this->belongsTo(Barn::class, 'barn_id');
    }

    // ==================== Inventory Movement ====================

    public function inventoryMovement()
    {
        return $this->hasOne(InventoryMovement::class, 'dairy_record_item_id');
    }

    // ==================== Scopes ====================

    public function scopeFeed($query)
    {
        return $query->where('item_type', 'feed');
    }

    public function scopeMedicine($query)
    {
        return $query->where('item_type', 'medicine');
    }

    public function scopeDeath($query)
    {
        return $query->where('item_type', 'death');
    }

    // ==================== Accessors ====================

    public function getDetailAttribute()
    {
        return match ($this->item_type) {
            'feed' => 'รหัส: ' . ($this->storehouse->item_code ?? '-') . 
                     ', หน่วย: ' . ($this->unit ?? '-') . 
                     ($this->note ? ', ' . $this->note : '') . 
                     ' (' . $this->quantity . ')',
            'medicine' => 'ยา: ' . ($this->medicine_code ?? '-') . 
                         ', หน่วย: ' . ($this->unit ?? '-') . 
                         ', สถานะ: ' . ($this->treatment_status ?? '-') .
                         ($this->note ? ', ' . $this->note : '') . 
                         ' (' . $this->quantity . ')',
            'death' => 'คอก: ' . (optional($this->pen)->pen_code ?? '-') . 
                      ($this->note ? ', ' . $this->note : '') . 
                      ' (' . $this->quantity . ')',
            default => $this->note ?? '-',
        };
    }

    public function getTypeDisplayAttribute()
    {
        return match ($this->item_type) {
            'feed' => 'อาหาร',
            'medicine' => 'ยา',
            'death' => 'หมูตาย',
            default => '-',
        };
    }
}
