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
    public function index(Request $request)
    {
        $query = Notification::where('user_id', Auth::id());

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('message', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Date Filter
        if ($request->filled('selected_date')) {
            $date = \Carbon\Carbon::now();
            switch ($request->selected_date) {
                case 'today':
                    $query->whereDate('created_at', $date);
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [$date->startOfWeek(), $date->copy()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $date->year);
                    break;
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['created_at', 'read_at', 'title'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $notifications = $query->paginate($perPage);

        $unreadCount = Notification::where('user_id', Auth::id())
            ->unread()
            ->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * ดึงจำนวนแจ้งเตือนที่ยังไม่ได้อ่าน
     */
    public function getUnreadCount()
    {
        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unreadCount' => $unreadCount,
        ]);
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
     * ทำเครื่องหมายว่าอ่านแล้วและนำทาง
     */
    public function markAndNavigate($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        // สำหรับ cancel_pig_sale ให้ไป payment_approvals dashboard
        if ($notification->type === 'cancel_pig_sale') {
            return redirect()->route('payment_approvals.index');
        }

        // สำหรับ user_registered, user_approved, user_rejected, user_registration_cancelled
        // ให้ไป user management dashboard (admin only)
        if (in_array($notification->type, ['user_registered', 'user_approved', 'user_rejected', 'user_registration_cancelled'])) {
            return redirect()->route('user_management.index');
        }

        // ป้องกัน redirect loop
        if ($notification->url && !str_contains($notification->url, 'notifications/')) {
            return redirect($notification->url);
        }

        return redirect()->route('notifications.index');
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
