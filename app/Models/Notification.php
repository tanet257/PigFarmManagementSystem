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
