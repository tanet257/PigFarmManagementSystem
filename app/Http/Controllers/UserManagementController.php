<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification;

class UserManagementController extends Controller
{
    /**
     * แสดงรายการผู้ใช้ทั้งหมด
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'approvedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);
        $roles = Role::all();

        // นับจำนวนตาม status
        $pendingCount = User::where('status', 'pending')->count();
        $approvedCount = User::where('status', 'approved')->count();
        $rejectedCount = User::where('status', 'rejected')->count();

        return view('admin.user_management.index', compact('users', 'roles', 'pendingCount', 'approvedCount', 'rejectedCount'));
    }

    /**
     * อนุมัติผู้ใช้
     */
    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // ตรวจสอบว่าอนุมัติแล้วหรือยัง
            if ($user->isApproved()) {
                return redirect()->back()->with('error', 'ผู้ใช้นี้ได้รับการอนุมัติแล้ว');
            }

            $validated = $request->validate([
                'role_ids' => 'required|array|min:1',
                'role_ids.*' => 'exists:roles,id',
            ]);

            // ดึงชื่อ role แรก เพื่อใช้เป็น usertype
            $roleIds = $validated['role_ids'];
            $primaryRole = Role::find($roleIds[0]);
            $usertype = $primaryRole ? $primaryRole->name : 'staff';

            // อนุมัติ user
            $user->update([
                'status' => 'approved',
                'usertype' => $usertype,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            // กำหนด roles
            $user->roles()->sync($validated['role_ids']);

            // สร้างแจ้งเตือนให้ผู้ใช้
            NotificationHelper::notifyUserApproved($user, auth()->user());

            // ส่งอีเมล
            NotificationHelper::sendUserApprovedEmail($user, auth()->user());

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'อนุมัติผู้ใช้ ' . $user->name . ' เรียบร้อยแล้ว (Role: ' . $usertype . ')');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User Approval Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ปฏิเสธผู้ใช้
     */
    public function reject(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // ตรวจสอบว่าปฏิเสธแล้วหรือยัง
            if ($user->isRejected()) {
                return redirect()->back()->with('error', 'ผู้ใช้นี้ถูกปฏิเสธแล้ว');
            }

            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            // ปฏิเสธ user
            $user->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            // สร้างแจ้งเตือนให้ผู้ใช้
            NotificationHelper::notifyUserRejected($user, auth()->user(), $validated['rejection_reason']);

            // ส่งอีเมล
            NotificationHelper::sendUserRejectedEmail($user, auth()->user(), $validated['rejection_reason']);

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'ปฏิเสธผู้ใช้ ' . $user->name . ' เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User Rejection Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * อัพเดท roles ของผู้ใช้
     */
    public function updateRoles(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'role_ids' => 'required|array|min:1',
                'role_ids.*' => 'exists:roles,id',
            ]);

            // ดึงชื่อ role แรก เพื่อใช้เป็น usertype
            $roleIds = $validated['role_ids'];
            $primaryRole = Role::find($roleIds[0]);
            $usertype = $primaryRole ? $primaryRole->name : $user->usertype;

            // เก็บ old role สำหรับ email
            $oldRole = $user->usertype;

            // อัพเดท usertype
            $user->update([
                'usertype' => $usertype,
            ]);

            // อัพเดท roles
            $user->roles()->sync($validated['role_ids']);

            // ส่งอีเมล role update
            if ($oldRole !== $usertype) {
                NotificationHelper::sendUserRoleUpdatedEmail($user, auth()->user(), $usertype, $oldRole);
            }

            DB::commit();

            return redirect()->back()->with('success', 'อัพเดท Role ของ ' . $user->name . ' เรียบร้อยแล้ว (New Type: ' . $usertype . ')');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Roles Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ลบผู้ใช้
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // ป้องกันลบตัวเอง
            if ($user->id === auth()->id()) {
                return redirect()->back()->with('error', 'ไม่สามารถลบบัญชีตัวเองได้');
            }

            $userName = $user->name;
            $user->delete();

            return redirect()->route('user_management.index')->with('success', 'ลบผู้ใช้ ' . $userName . ' เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            Log::error('Delete User Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ยกเลิกลงทะเบียนผู้ใช้ (สร้างคำขอ)
     */
    public function requestCancelRegistration(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // ตรวจสอบว่ายกเลิกแล้วหรือยัง
            if ($user->isCancelled()) {
                return redirect()->back()->with('error', 'ผู้ใช้นี้ถูกยกเลิกแล้ว');
            }

            // ตรวจสอบว่ามีคำขอยกเลิกอยู่แล้วหรือไม่
            if ($user->hasCancellationRequest()) {
                return redirect()->back()->with('warning', 'ผู้ใช้นี้มีคำขอยกเลิกลงทะเบียนอยู่แล้ว');
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            // บันทึกข้อมูล cancellation request
            $user->update([
                'cancellation_reason' => $validated['reason'],
                'cancellation_requested_at' => now(),
            ]);

            // สร้างแจ้งเตือน notification
            Notification::create([
                'type' => 'user_registration_cancelled',
                'user_id' => $user->id,
                'related_user_id' => auth()->id(),
                'title' => 'ยกเลิกลงทะเบียน',
                'message' => "ผู้ใช้ {$user->name} ขอยกเลิกลงทะเบียน\nเหตุผล: {$validated['reason']}",
                'url' => route('user_management.index'),
                'is_read' => false,
            ]);

            // ส่ง notification ให้ admin
            $admins = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'type' => 'user_registration_cancelled',
                    'user_id' => $admin->id,
                    'related_user_id' => $user->id,
                    'title' => 'ผู้ใช้ขอยกเลิกลงทะเบียน',
                    'message' => "ผู้ใช้ {$user->name} ({$user->email}) ขอยกเลิกลงทะเบียน\nเหตุผล: {$validated['reason']}",
                    'url' => route('user_management.index'),
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'บันทึกคำขอยกเลิกลงทะเบียนแล้ว ระบบจะแจ้งเตือนแอดมิน');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Request Cancel Registration Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * อนุมัติการยกเลิกลงทะเบียนผู้ใช้
     */
    public function approveCancelRegistration($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // ตรวจสอบว่ามีคำขอยกเลิก
            if (!$user->hasCancellationRequest()) {
                return redirect()->back()->with('error', 'ผู้ใช้นี้ไม่มีคำขอยกเลิกลงทะเบียน');
            }

            // อัพเดท status เป็น cancelled
            $user->update([
                'status' => 'cancelled',
            ]);

            // ส่งอีเมล
            NotificationHelper::sendUserCancelledEmail($user, 'ส่งมอบการอนุมัติ');

            // สร้างแจ้งเตือน
            Notification::create([
                'type' => 'user_registration_cancelled',
                'user_id' => $user->id,
                'title' => 'ยกเลิกลงทะเบียนเรียบร้อยแล้ว',
                'message' => "การยกเลิกลงทะเบียนของคุณได้รับการอนุมัติแล้ว บัญชีของคุณจะถูกปิดใช้งาน",
                'url' => null,
                'is_read' => false,
            ]);

            // ลบการแจ้งเตือน user_registration_cancelled ทั้งหมด เพื่อให้แอดมิน Mark as approved
            Notification::where('related_user_id', $user->id)
                ->where('type', 'user_registration_cancelled')
                ->where('user_id', '!=', $user->id)
                ->update(['approval_status' => 'approved']);

            DB::commit();

            return redirect()->back()->with('success', 'อนุมัติการยกเลิกลงทะเบียน ' . $user->name . ' เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve Cancel Registration Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ปฏิเสธการยกเลิกลงทะเบียนผู้ใช้
     */
    public function rejectCancelRegistration($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // ตรวจสอบว่ามีคำขอยกเลิก
            if (!$user->hasCancellationRequest()) {
                return redirect()->back()->with('error', 'ผู้ใช้นี้ไม่มีคำขอยกเลิกลงทะเบียน');
            }

            // ลบ cancellation request
            $user->update([
                'cancellation_reason' => null,
                'cancellation_requested_at' => null,
            ]);

            // สร้างแจ้งเตือน
            Notification::create([
                'type' => 'user_registration_cancelled',
                'user_id' => $user->id,
                'title' => 'ปฏิเสธการยกเลิกลงทะเบียน',
                'message' => "การยกเลิกลงทะเบียนของคุณถูกปฏิเสธ บัญชีของคุณยังคงใช้งานได้",
                'url' => null,
                'is_read' => false,
            ]);

            // ลบการแจ้งเตือน user_registration_cancelled ทั้งหมด เพื่อให้แอดมิน Mark as rejected
            Notification::where('related_user_id', $user->id)
                ->where('type', 'user_registration_cancelled')
                ->where('user_id', '!=', $user->id)
                ->update(['approval_status' => 'rejected']);

            DB::commit();

            return redirect()->back()->with('success', 'ปฏิเสธการยกเลิกลงทะเบียน ' . $user->name . ' เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reject Cancel Registration Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * ดึงข้อมูล user type options สำหรับ AJAX
     */
    public function getUserTypeOptions()
    {
        try {
            $roles = Role::all(['id', 'name']);

            return response()->json([
                'success' => true,
                'roles' => $roles,
            ]);
        } catch (\Exception $e) {
            Log::error('Get User Type Options Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ดึงข้อมูล roles ของผู้ใช้เฉพาะคน สำหรับ AJAX
     */
    public function getUserRoles($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'usertype' => $user->usertype,
                    'roles' => $user->roles->pluck('id')->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get User Roles Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ Export Users to CSV
     */
    public function exportCsv(Request $request)
    {
        $query = User::query();

        // Apply export-specific date range filter
        if ($request->filled('export_date_from') && $request->filled('export_date_to')) {
            $query->whereBetween('created_at', [$request->export_date_from . ' 00:00:00', $request->export_date_to . ' 23:59:59']);
        }

        $users = $query->get();

        $filename = "จัดการผู้ใช้งาน_" . date('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Thai character support in Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'ชื่อ', 'อีเมล', 'เบอร์โทร', 'ประเภท', 'สถานะ', 'ลงทะเบียนเมื่อ']);
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? '-',
                    $user->usertype ?? '-',
                    $user->status ?? '-',
                    $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '-',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv;charset=utf-8']);
    }
}
