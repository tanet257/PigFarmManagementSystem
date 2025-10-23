<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'user_id',
        'related_user_id',
        'title',
        'message',
        'url',
        'is_read',
        'read_at',
        // Payment approval fields
        'related_model',
        'related_model_id',
        'approval_status',
        'approval_notes',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * ผู้รับแจ้งเตือน
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ผู้ใช้ที่เกี่ยวข้อง (ผู้ลงทะเบียนใหม่)
     */

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Pig Entry Record
     */
    public function pigEntry()
    {
        return $this->hasOne(PigEntryRecord::class, 'id', 'related_model_id')
            ->where('related_model', 'PigEntryRecord');
    }

    /**
     * Pig Sale
     */
    public function pigSale()
    {
        return $this->hasOne(PigSale::class, 'id', 'related_model_id')
            ->where('related_model', 'PigSale');
    }

    /**
     * Cost Payment (for PigEntry payment)
     */
    public function costPayment()
    {
        return $this->hasOne(CostPayment::class, 'id', 'related_model_id')
            ->where('related_model', 'CostPayment');
    }

    /**
     * ืำเครื่องหมายว่าอ่านแล้ว
     */

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Scope: แจ้งเตือนที่ยังไม่ได้อ่าน
     */

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: แจ้งเตือนที่อ่านแล้ว
     */

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
}
