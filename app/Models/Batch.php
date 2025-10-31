<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PigSale;
use App\Models\DairyStorehouseUse;
use App\Models\Cost;
use App\Models\CostPayment;
use App\Helpers\PaymentApprovalHelper;
use App\Helpers\NotificationHelper;

class Batch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'batches';

    protected $fillable = [
        'farm_id',
        'batch_code',
        'total_pig_weight',
        'total_pig_weight_at_sale',
        'total_pig_amount',
        'current_quantity',
        'average_weight_per_pig',
        'average_weight_per_pig_at_sale',
        'average_price_per_pig',
        'total_pig_price',
        'total_death',
        'status',
        'note',
        'start_date',
        'end_date',
        'total_weight_gain',
        'avg_weight_gain_per_pig',
        'raising_days'
    ];

    protected $casts = [
        'current_quantity' => 'integer',
        'average_weight_per_pig' => 'float',
        'average_weight_per_pig_at_sale' => 'float',
        'average_price_per_pig' => 'float',
        'total_death' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_weight_gain' => 'float',
        'avg_weight_gain_per_pig' => 'float',
        'raising_days' => 'integer',
    ];

    // Relation กับ Farm
    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id');
    }

    // Relation กับ PigEntryRecord
    public function pig_entry_records()
    {
        return $this->hasMany(PigEntryRecord::class, 'batch_id');
    }

    /**
     * Compatibility helper: returns pig sales for this batch.
     * Uses the existing PigSale model in the app.
     */
    public function pig_sells()
    {
        return $this->hasMany(PigSale::class, 'batch_id');
    }

    public function costs()
    {
        return $this->hasMany(Cost::class, 'batch_id', 'id');
    }

    //ใช้เพื่อให้เรียกดูได้ง่ายว่า batch นี่อยู่เล้าไหนคอกไหน
    public function allocations()
    {
        return $this->hasMany(BatchPenAllocation::class, 'batch_id');
    }

    public function batchPenAllocations()
    {
        return $this->hasMany(BatchPenAllocation::class, 'batch_id');
    }

    public function inventory_movements()
    {
        return $this->hasMany(InventoryMovement::class, 'batch_id', 'id');
    }

    public function dairy_storehouse_uses()
    {
        return $this->hasMany(DairyStorehouseUse::class, 'batch_id');
    }

    public function pig_deaths()
    {
        return $this->hasMany(PigDeath::class);
    }

    public function batch_metric()
    {
        return $this->hasOne(BatchMetric::class, 'batch_id');
    }

    /**
     * Combined creation: สร้าง Batch + PigEntry + update status พร้อมกัน
     *
     * @param array $batchData Batch creation data
     * @param array $entryData PigEntry creation data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function createWithPigEntry(array $batchData, array $entryData)
    {
        return DB::transaction(function () use ($batchData, $entryData) {
            // 1. สร้าง Batch (status: raising - เลี้ยงได้เลย)
            $batchData['status'] = 'raising'; // ✅ สามารถเลี้ยงได้เลยตอนบันทึก
            $batchData['start_date'] = $entryData['pig_entry_date'] ?? now();
            
            $batch = static::create($batchData);

            // 2. สร้าง PigEntryRecord
            $entry = $batch->pig_entry_records()->create($entryData);

            // 3. บันทึกข้อมูล entry ลงใน batch
            $batch->update([
                'total_pig_amount' => $entry->total_pig_amount,
                'current_quantity' => $entry->total_pig_amount, // ✅ SET current_quantity
                'total_pig_weight' => $entry->total_pig_weight,
                'average_weight_per_pig' => $entry->average_weight_per_pig,
                'average_price_per_pig' => $entry->average_price_per_pig,
                'total_pig_price' => $entry->total_pig_price,
            ]);

            // 4. สร้าง BatchMetric
            BatchMetric::updateOrCreate(
                ['batch_id' => $batch->id],
                [
                    'total_pigs_received' => $entry->total_pig_amount,
                    'total_weight_received' => $entry->total_pig_weight,
                    'average_weight' => $entry->average_weight_per_pig,
                    'current_mortality_rate' => 0,
                    'last_updated' => now(),
                ]
            );

            // 5. ✅ NEW: สร้าง Cost record สำหรับลูกหมู (pending approval) - ใช้ helper
            $costResult = PaymentApprovalHelper::createPigletCostPaymentPending(
                $batch->farm_id,
                $batch->id,
                $entry->total_pig_amount,
                $entry->total_pig_price,
                $entry->average_price_per_pig,
                $batch->batch_code,
                $entry->id
            );

            if (!$costResult['success']) {
                Log::warning('Failed to create piglet cost: ' . $costResult['message']);
            } else {
                // ✅ Update PigEntryRecord total_cost with piglet cost
                $entry->update(['total_cost' => $entry->total_pig_price]);
            }

            // 6. ส่ง notification ให้ admins ว่ามี batch ใหม่รอ approve cost payment
            Log::info("Batch created with pig entry", [
                'batch_id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'total_pigs' => $entry->total_pig_amount,
                'cost_result' => $costResult,
                'status' => $batch->status,
                'note' => 'Pending cost payment approval',
            ]);

            // ✅ Notify admins about new batch - remind to approve cost payment
            NotificationHelper::notifyAdminsPigEntryRecorded($entry, auth()->user() ?? \App\Models\User::find(1));

            return [
                'batch' => $batch,
                'entry' => $entry->fresh(),
            ];
        });
    }

    /**
     * Boot method to handle cascading deletes
     */
    protected static function boot()
    {
        parent::boot();

        // ลบ batch_pen_allocations และปรับ current_quantity ให้เป็น 0 ก่อนลบ
        static::deleting(function ($batch) {
            // อัปเดต current_quantity ให้เป็น 0 ก่อนลบ
            BatchPenAllocation::where('batch_id', $batch->id)
                ->update(['current_quantity' => 0]);

            BatchPenAllocation::where('batch_id', $batch->id)
                ->update(['allocated_pigs' => 0]);

            // ลบ batch_pen_allocations
            BatchPenAllocation::where('batch_id', $batch->id)->delete();
        });

        // ลบ costs
        static::deleting(function ($batch) {
            // ใช้ soft delete สำหรับ costs เพื่อเก็บข้อมูล
            // Cost::where('batch_id', $batch->id)->delete();
            
            // เพื่อให้ costs ถูก soft delete เมื่อ batch ถูก soft delete
            // (จัดการในระดับ BatchRestoreHelper แทน)
        });

        // ลบ pig_entry_records
        static::deleting(function ($batch) {
            PigEntryRecord::where('batch_id', $batch->id)->delete();
        });

        // ลบ pig_sells
        static::deleting(function ($batch) {
            PigSale::where('batch_id', $batch->id)->delete();
        });

        // ลบ inventory_movements
        static::deleting(function ($batch) {
            InventoryMovement::where('batch_id', $batch->id)->delete();
        });

        // ลบ pig_deaths
        static::deleting(function ($batch) {
            PigDeath::where('batch_id', $batch->id)->delete();
        });
    }

    /**
     * คำนวณน้ำหนักหมูและอัปเดตข้อมูล
     * เรียกใช้เมื่อบันทึกน้ำหนักตอนจับขาย
     */
    public function recordSaleWeight($totalWeightAtSale, $averageWeightAtSale)
    {
        // คำนวณ weight gain
        $totalWeightGain = $totalWeightAtSale - ($this->total_pig_weight ?? 0);
        $avgWeightGain = $averageWeightAtSale - ($this->average_weight_per_pig ?? 0);

        // คำนวณจำนวนวันในการเลี้ยง
        $raisingDays = 0;
        if ($this->start_date && $this->end_date) {
            $raisingDays = $this->start_date->diffInDays($this->end_date);
        }

        // อัปเดต
        $this->update([
            'total_pig_weight_at_sale' => $totalWeightAtSale,
            'average_weight_per_pig_at_sale' => $averageWeightAtSale,
            'total_weight_gain' => $totalWeightGain,
            'avg_weight_gain_per_pig' => $avgWeightGain,
            'raising_days' => $raisingDays,
        ]);

        Log::info('Batch weight recorded', [
            'batch_id' => $this->id,
            'total_weight_at_sale' => $totalWeightAtSale,
            'average_weight_at_sale' => $averageWeightAtSale,
            'total_weight_gain' => $totalWeightGain,
            'avg_weight_gain_per_pig' => $avgWeightGain,
            'raising_days' => $raisingDays,
        ]);
    }

    /**
     * ได้ข้อมูลสรุปการเลี้ยงหมู
     */
    public function getSummary()
    {
        return [
            'entry_weight' => $this->total_pig_weight ?? 0,
            'entry_avg_weight' => $this->average_weight_per_pig ?? 0,
            'sale_weight' => $this->total_pig_weight_at_sale ?? 0,
            'sale_avg_weight' => $this->average_weight_per_pig_at_sale ?? 0,
            'weight_gain' => $this->total_weight_gain ?? 0,
            'avg_gain_per_pig' => $this->avg_weight_gain_per_pig ?? 0,
            'raising_days' => $this->raising_days ?? 0,
            'daily_gain_per_pig' => $this->raising_days > 0
                ? round(($this->avg_weight_gain_per_pig ?? 0) / $this->raising_days, 2)
                : 0,
        ];
    }

    /**
     * รวมน้ำหนักหมูจากการขายทั้งหมด (บางครั้งขายทีละส่วน)
     *
     * ตัวอย่าง: ขาย 200 ตัว x 90kg + ขาย 150 ตัว x 92kg = รวมน้ำหนัก 200+150=350 ตัว
     */
    public function getTotalSaleWeight()
    {
        $sales = $this->pig_sells()
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->get();

        return [
            'total_quantity_sold' => $sales->sum('quantity') ?? 0,
            'total_weight_sold' => $sales->sum('total_weight') ?? 0,
            'total_sales_count' => $sales->count(),
            'avg_weight_all_sales' => $sales->count() > 0
                ? round(($sales->sum('total_weight') ?? 0) / ($sales->sum('quantity') ?? 1), 2)
                : 0,
            'sales_detail' => $sales->map(function ($sale) {
                return [
                    'date' => $sale->date,
                    'quantity' => $sale->quantity,
                    'total_weight' => $sale->total_weight,
                    'avg_weight' => $sale->avg_weight_per_pig,
                    'price_per_kg' => $sale->price_per_kg,
                    'total_price' => $sale->total_price,
                ];
            })->toArray(),
        ];
    }

    /**
     * คำนวณและอัปเดตน้ำหนักตอนขายจากผลรวมของการขายทั้งหมด
     * เรียกใช้หลังจากบันทึกการขายแต่ละครั้ง
     */
    public function calculateTotalSaleWeight()
    {
        $saleData = $this->getTotalSaleWeight();

        $totalWeightSold = $saleData['total_weight_sold'];
        $totalQtySold = $saleData['total_quantity_sold'];
        $avgWeightAtSale = $totalQtySold > 0
            ? round($totalWeightSold / $totalQtySold, 2)
            : 0;

        // คำนวณ weight gain จากการขายสุดท้าย
        $totalWeightGain = $totalWeightSold - ($this->total_pig_weight ?? 0);
        $avgWeightGain = $avgWeightAtSale - ($this->average_weight_per_pig ?? 0);

        // คำนวณจำนวนวันในการเลี้ยง (จากเข้า ถึง ขายสุดท้าย)
        $raisingDays = 0;
        $lastSale = $this->pig_sells()
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->latest('date')
            ->first();

        if ($this->start_date && $lastSale && $lastSale->date) {
            $raisingDays = \Carbon\Carbon::parse($this->start_date)
                ->diffInDays(\Carbon\Carbon::parse($lastSale->date));
        }

        // อัปเดต batch
        $this->update([
            'total_pig_weight_at_sale' => $totalWeightSold,
            'average_weight_per_pig_at_sale' => $avgWeightAtSale,
            'total_weight_gain' => $totalWeightGain,
            'avg_weight_gain_per_pig' => $avgWeightGain,
            'raising_days' => $raisingDays,
            'end_date' => $lastSale ? $lastSale->date : $this->end_date,
        ]);

        Log::info('Batch total sale weight calculated', [
            'batch_id' => $this->id,
            'total_qty_sold' => $totalQtySold,
            'total_weight_sold' => $totalWeightSold,
            'avg_weight_at_sale' => $avgWeightAtSale,
            'total_weight_gain' => $totalWeightGain,
            'raising_days' => $raisingDays,
        ]);

        return $saleData;
    }

    /**
     * สรุปการเลี้ยงเทียบกับการขายสะสม
     */
    public function getCompleteSummary()
    {
        $saleData = $this->getTotalSaleWeight();

        return [
            // ข้อมูลตอนเข้า
            'entry' => [
                'quantity' => $this->total_pig_amount ?? 0,
                'total_weight' => $this->total_pig_weight ?? 0,
                'avg_weight' => $this->average_weight_per_pig ?? 0,
                'date' => $this->start_date,
            ],

            // ข้อมูลตอนขาย (สะสม)
            'sales' => [
                'total_quantity_sold' => $saleData['total_quantity_sold'],
                'total_weight_sold' => $saleData['total_weight_sold'],
                'avg_weight_per_pig' => $saleData['avg_weight_all_sales'],
                'sales_count' => $saleData['total_sales_count'],
            ],

            // สเตทัส
            'status_info' => [
                'qty_remaining' => ($this->total_pig_amount ?? 0) - ($saleData['total_quantity_sold'] ?? 0),
                'total_death' => $this->total_death ?? 0,
                'current_status' => $this->status,
            ],

            // ผลลัพธ์
            'performance' => [
                'weight_gain_per_pig' => $this->avg_weight_gain_per_pig ?? 0,
                'daily_gain_per_pig' => $this->raising_days > 0
                    ? round(($this->avg_weight_gain_per_pig ?? 0) / $this->raising_days, 2)
                    : 0,
                'raising_days' => $this->raising_days ?? 0,
            ],
        ];
    }
}




