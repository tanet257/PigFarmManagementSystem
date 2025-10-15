<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;

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

            // อนุมัติ user
            $user->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            // กำหนด roles
            $user->roles()->sync($validated['role_ids']);

            // สร้างแจ้งเตือนให้ผู้ใช้
            NotificationHelper::notifyUserApproved($user, auth()->user());

            DB::commit();

            return redirect()->route('user_management.index')->with('success', 'อนุมัติผู้ใช้ ' . $user->name . ' เรียบร้อยแล้ว');
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

            // อัพเดท roles
            $user->roles()->sync($validated['role_ids']);

            DB::commit();

            return redirect()->back()->with('success', 'อัพเดท Role ของ ' . $user->name . ' เรียบร้อยแล้ว');
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
}
