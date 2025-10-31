<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DairyRecord extends Model
{
    use HasFactory;

    protected $table = 'dairy_records'; // ชื่อตาราง

    protected $fillable = [
        'batch_id',
        'barn_id',
        'date',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function barn()
    {
        return $this->belongsTo(Barn::class);
    }

    public function pig_deaths()
    {
        return $this->hasMany(PigDeath::class);
    }

    public function batch_treatments()
    {
        return $this->hasMany(BatchTreatment::class);
    }

    public function feed_uses()
    {
        return $this->dairy_storehouse_uses()->where('item_type', 'food');
    }

    public function dairy_storehouse_uses()
    {
        return $this->hasMany(DairyStorehouseUse::class, 'dairy_record_id');
        // สมมติว่า foreign key ใน DairyStorehouseUse คือ dairy_record_id
    }

    public function inventory_movements()
    {
        return $this->hasMany(InventoryMovement::class, 'batch_id', 'batch_id')
            ->where('change_type', 'out');
    }

    // ==================== New Unified Relationships ====================

    public function items()
    {
        return $this->hasMany(DairyRecordItem::class, 'dairy_record_id');
    }

    public function feedItems()
    {
        return $this->items()->feed();
    }

    public function medicineItems()
    {
        return $this->items()->medicine();
    }

    public function deathItems()
    {
        return $this->items()->death();
    }
}
