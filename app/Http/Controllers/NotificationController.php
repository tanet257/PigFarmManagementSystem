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
     * ทำเครื่องหมายว่าอ่านแล้ว (นำทางไปหน้า notifications.index)
     */
    public function markAsReadAndNavigateToNotifications($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        return redirect()->route('notifications.index')->with('success', 'ทำเครื่องหมายว่าอ่านแล้ว');
    }

    /**
     * ทำเครื่องหมายว่าอ่านแล้ว (เฉพาะ mark, ไม่ navigate ไปไหน)
     */
    public function markAsReadOnly($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        return redirect()->back()->with('success', 'ทำเครื่องหมายว่าอ่านแล้ว');
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

        // ✅ Route Map: ประเภทแจ้งเตือน -> หน้า Route (ตามที่มีจริงใน web.php)
        $routeMap = [
            // ============ ผู้ใช้งาน ============
            'user_registered' => 'user_management.index',
            'user_approved' => 'user_management.index',
            'user_rejected' => 'user_management.index',
            'user_registration_cancelled' => 'user_management.index',
            'user_role_updated' => 'user_management.index',

            // ============ การรับเข้าหมู ============
            'pig_entry_recorded' => 'batch.index',          // บันทึกการรับเข้าหมูใหม่
            'pig_entry_payment_approved' => 'batch.index',  // อนุมัติชำระเงินรับเข้า
            'payment_recorded_pig_entry' => 'cost_payment_approvals.index',  // บันทึกการชำระเงินรับเข้า (รอ admin อนุมัติ)

            // ============ การขายหมู ============
            'pig_sale' => 'payment_approvals.index',                    // บันทึกการขายหมู (รออนุมัติ)
            'payment_recorded_pig_sale' => 'payment_approvals.index',   // บันทึกการชำระเงินขาย (รออนุมัติ)
            'payment_approved' => 'payment_approvals.index',            // การชำระเงินได้รับอนุมัติ
            'payment_rejected' => 'payment_approvals.index',            // การชำระเงินถูกปฏิเสธ

            // ============ ต้นทุน / ค่าใช้จ่าย ============
            'cost_pending_approval' => 'cost_payment_approvals.index',  // ต้นทุนรอการอนุมัติ
            'cost_approved' => 'cost_payment_approvals.index',          // ต้นทุนได้รับการอนุมัติ
            'cost_rejected' => 'cost_payment_approvals.index',          // ต้นทุนถูกปฏิเสธ
            'cost_payment_cancelled' => 'cost_payment_approvals.index', // การชำระเงินต้นทุนถูกยกเลิก
            'cost_payment_approved' => 'cost_payment_approvals.index',  // การชำระเงินต้นทุนได้รับการอนุมัติ
            'cost_payment_rejected' => 'cost_payment_approvals.index',  // การชำระเงินต้นทุนถูกปฏิเสธ
            'payment_recorded' => 'cost_payment_approvals.index',       // บันทึกการชำระเงิน (รอ admin อนุมัติ)

            // ============ หมูตาย ============
            'pig_death' => 'dairy_records.index',                   // บันทึกหมูตาย

            // ============ การรักษา ============
            'batch_treatment' => 'treatments.index',                    // บันทึกการรักษา

            // ============ คลังสินค้า ============
            'inventory_movement' => 'inventory_movements.index',        // การเคลื่อนไหวสินค้า
            'stock_low' => 'storehouse_records.index',                  // สินค้าใกล้หมด

            // ============ ระบบ ============
            'batch_deleted' => 'batch.index',                           // ลบรุ่นแล้ว
            'cancel_pig_sale' => 'payment_approvals.index',             // ยกเลิกการขายหมู
            'system_alert' => 'dashboard',                              // แจ้งเตือนระบบ
            'system_maintenance' => 'dashboard',                        // ระบบบำรุงรักษา
        ];

        // ✅ ตรวจสอบว่ามี route map ตรงกับประเภท
        if (isset($routeMap[$notification->type])) {
            return redirect()->route($routeMap[$notification->type]);
        }

        // ✅ ถ้ามี URL โดยตรง ให้ใช้
        if ($notification->url && !str_contains($notification->url, 'notifications')) {
            return redirect($notification->url);
        }

        // ✅ ป้องกัน redirect loop - ถ้าไม่มี route map และไม่มี url ให้ไปหน้า notifications
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
