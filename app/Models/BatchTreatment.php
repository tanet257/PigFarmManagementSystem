<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTreatment extends Model
{
    use HasFactory;

    protected $table = 'batch_treatments'; // ชื่อตาราง

    protected $fillable = [
        'pen_id',
        'batch_id',
        'farm_id',
        'dairy_record_id',
        'medicine_name',
        'medicine_code',
        'disease_name',
        'quantity',
        'status',
        'treatment_status',
        'treatment_level',
        'treatment_start_date',
        'planned_start_date',
        'actual_start_date',
        'planned_duration',
        'treatment_end_date',
        'actual_end_date',
        'effective_date',
        'dosage',
        'frequency',
        'attachment_url',
        'note',
        'date',
    ];

    protected $casts = [
        'treatment_start_date' => 'date',
        'duration_days' => 'integer',
    ];

    //---------------relation ship------------------------//

    public function pen()
    {
        return $this->belongsTo(Pen::class, 'pen_id', 'id'); // pen_id ใน batch_treatments ชี้ไป id ของ pens
    }


    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function dairy_record()
    {
        return $this->belongsTo(DairyRecord::class);
    }

    public function storehouse()
    {
        return $this->belongsTo(Storehouse::class, 'medicine_code', 'item_code');
    }

    /**
     * Get all daily treatment logs for this treatment
     */
    public function dailyLogs()
    {
        return $this->hasMany(DailyTreatmentLog::class, 'batch_treatment_id');
    }

    //---------------Accessors & Methods------------------------//

    /**
     * คำนวณจำนวนวันรักษาจากวันที่เริ่มและสิ้นสุด
     */
    public function calculateDurationDays()
    {
        if ($this->treatment_start_date && $this->treatment_end_date) {
            return \Carbon\Carbon::parse($this->treatment_start_date)
                ->diffInDays(\Carbon\Carbon::parse($this->treatment_end_date)) + 1;
        }
        return 0;
    }

    /**
     * คำนวณจำนวนวันรักษาจากจำนวน daily logs
     */
    public function calculateDurationFromDailyLogs()
    {
        return $this->dailyLogs()->count();
    }

    /**
     * อัพเดตสถานะเป็น ongoing
     */
    public function markAsOngoing()
    {
        $this->update(['treatment_status' => 'ongoing']);
        return $this;
    }

    /**
     * อัพเดตสถานะเป็น completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'treatment_status' => 'completed',
            'treatment_end_date' => now()->toDateString(),
        ]);
        return $this;
    }

    /**
     * อัพเดตสถานะเป็น stopped (หยุดการรักษา)
     */
    public function markAsStopped()
    {
        $this->update(['treatment_status' => 'stopped']);
        return $this;
    }

    /**
     * เช็คว่าการรักษายังไม่เริ่มหรือ
     */
    public function isPending()
    {
        return $this->treatment_status === 'pending';
    }

    /**
     * เช็คว่าการรักษากำลังดำเนินการหรือ
     */
    public function isOngoing()
    {
        return $this->treatment_status === 'ongoing';
    }

    /**
     * เช็คว่าการรักษาเสร็จแล้วหรือ
     */
    public function isCompleted()
    {
        return $this->treatment_status === 'completed';
    }
}
