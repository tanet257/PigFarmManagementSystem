<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * แสดงรายการแจ้งเตือนทั้งหมด
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = Notification::where('user_id', Auth::id())
            ->unread()
            ->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * ดึงข้อมูลแจ้งเตือนสำหรับแสดงใน header dropdown
     */
    public function getRecent()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $unreadCount = Notification::where('user_id', Auth::id())
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * ทำเครื่องหมายว่าอ่านแล้ว
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        // ถ้ามี URL ให้ redirect ไป
        if ($notification->url) {
            return redirect($notification->url);
        }

        return redirect()->back()->with('success', 'ทำเครื่องหมายว่าอ่านแล้ว');
    }

    /**
     * ทำเครื่องหมายว่าอ่านแล้วทั้งหมด
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->back()->with('success', 'ทำเครื่องหมายว่าอ่านแล้วทั้งหมด');
    }

    /**
     * ลบแจ้งเตือน
     */
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->delete();

        return redirect()->back()->with('success', 'ลบแจ้งเตือนสำเร็จ');
    }

    /**
     * ลบแจ้งเตือนที่อ่านแล้วทั้งหมด
     */
    public function clearRead()
    {
        Notification::where('user_id', Auth::id())
            ->read()
            ->delete();

        return redirect()->back()->with('success', 'ลบแจ้งเตือนที่อ่านแล้วทั้งหมดสำเร็จ');
    }
}
