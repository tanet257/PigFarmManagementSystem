<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class DairyTreatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'farm_id',
        'batch_id',
        'barn_id',
        'pen_id',
        'treatment_name',
        'disease_description',
        'treatment_type',
        'affected_pigs_count',
        'start_date',
        'end_date',
        'duration_days',
        'day1_dosage',
        'daily_dosage',
        'dosage_notes',
        'medicine_name',
        'medicine_batch_number',
        'medicine_expiry_date',
        'storehouse_id',
        'unit_cost',
        'total_cost',
        'status',
        'stop_reason',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'medicine_expiry_date' => 'date',
        'day1_dosage' => 'decimal:2',
        'daily_dosage' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    protected $appends = ['days_remaining', 'total_dosage_used', 'is_expired'];

    // ==================== Relationships ====================

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function barn()
    {
        return $this->belongsTo(Barn::class);
    }

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

    public function storehouse()
    {
        return $this->belongsTo(Storehouse::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==================== Accessors ====================

    /**
     * จำนวนวันที่เหลือของการรักษา
     */
    public function getDaysRemainingAttribute()
    {
        if ($this->status === 'completed' || $this->status === 'stopped') {
            return 0;
        }

        if (!$this->start_date) {
            return $this->duration_days;
        }

        $today = Carbon::today();
        $startDate = Carbon::parse($this->start_date);
        $endDate = $this->end_date ? Carbon::parse($this->end_date) : $startDate->clone()->addDays($this->duration_days);

        $daysRemaining = $endDate->diffInDays($today, false);
        return max(0, $daysRemaining);
    }

    /**
     * ปริมาณยาที่ใช้ทั้งหมด (คำนวณ)
     */
    public function getTotalDosageUsedAttribute()
    {
        if (!$this->start_date) {
            return 0;
        }

        $daysElapsed = Carbon::today()->diffInDays(Carbon::parse($this->start_date));

        if ($daysElapsed === 0) {
            return $this->day1_dosage;
        }

        // วันแรก + วันต่อมา
        $total = $this->day1_dosage;
        $total += ($daysElapsed * $this->daily_dosage);

        return round($total, 2);
    }

    /**
     * ตรวจสอบว่ายาหมดอายุหรือไม่
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->medicine_expiry_date) {
            return false;
        }

        return Carbon::parse($this->medicine_expiry_date)->isPast();
    }

    // ==================== Mutators ====================

    /**
     * คำนวณ total_cost ตอนบันทึก
     */
    public function setTotalCostAttribute($value)
    {
        if (is_null($value) && $this->unit_cost && $this->duration_days) {
            $this->attributes['total_cost'] = $this->unit_cost * $this->duration_days;
        } else {
            $this->attributes['total_cost'] = $value;
        }
    }

    /**
     * คำนวณ end_date ถ้าไม่ได้ระบุ
     */
    public function setEndDateAttribute($value)
    {
        if (is_null($value) && $this->start_date) {
            $this->attributes['end_date'] = Carbon::parse($this->start_date)
                ->addDays($this->duration_days - 1)->toDateString();
        } else {
            $this->attributes['end_date'] = $value;
        }
    }

    // ==================== Methods ====================

    /**
     * เปลี่ยน status เป็น ongoing (เมื่อเริ่มให้ยา)
     */
    public function markAsOngoing()
    {
        $this->update([
            'status' => 'ongoing',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays($this->duration_days - 1),
        ]);

        return $this;
    }

    /**
     * เปลี่ยน status เป็น completed (เมื่อจบรักษา)
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'end_date' => Carbon::today(),
        ]);

        return $this;
    }

    /**
     * หยุดการรักษา
     */
    public function stop($reason = null)
    {
        $this->update([
            'status' => 'stopped',
            'stop_reason' => $reason,
            'end_date' => Carbon::today(),
        ]);

        return $this;
    }

    /**
     * สารสัตวแพทย์ที่ยังไม่เริ่ม
     */
    public static function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * สารสัตวแพทย์ที่กำลังใช้
     */
    public static function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    /**
     * สารสัตวแพทย์ที่จบแล้ว
     */
    public static function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * ยาที่หมดอายุ
     */
    public static function scopeExpired($query)
    {
        return $query->where('medicine_expiry_date', '<', Carbon::today());
    }

    /**
     * ตรวจสอบสถานะ
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'รอเริ่มรักษา',
            'ongoing' => 'กำลังรักษา',
            'completed' => 'จบการรักษา',
            'stopped' => 'หยุดการรักษา',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * ตรวจสอบประเภท
     */
    public function getTypeLabelAttribute()
    {
        $types = [
            'medicine' => 'ยาการรักษา',
            'vaccine' => 'วัคซีน',
            'treatment' => 'การรักษา',
        ];

        return $types[$this->treatment_type] ?? $this->treatment_type;
    }
};
