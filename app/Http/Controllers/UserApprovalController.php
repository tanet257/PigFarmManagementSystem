<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserApprovalController extends Controller
{
    /**
     * แสดงรายการผู้ใช้ที่รออนุมัติ
     */
    public function index()
    {
        $pendingUsers = User::where('status', 'pending')
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $approvedUsers = User::where('status', 'approved')
            ->with(['roles', 'approvedBy'])
            ->orderBy('approved_at', 'desc')
            ->paginate(15);

        $rejectedUsers = User::where('status', 'rejected')
            ->with(['roles', 'approvedBy'])
            ->orderBy('approved_at', 'desc')
            ->paginate(15);

        $roles = Role::all();

        return view('admin.user-approval.index', compact('pendingUsers', 'approvedUsers', 'rejectedUsers', 'roles'));
    }

    /**
     * อนุมัติผู้ใช้
     */
    public function approve(Request $request, User $user)
    {
        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // กำหนด roles
        $user->roles()->sync($request->role_ids);

        return redirect()->back()->with('success', 'อนุมัติผู้ใช้ ' . $user->name . ' เรียบร้อยแล้ว');
    }

    /**
     * ปฏิเสธผู้ใช้
     */
    public function reject(Request $request, User $user)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $user->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'ปฏิเสธผู้ใช้ ' . $user->name . ' เรียบร้อยแล้ว');
    }

    /**
     * แก้ไข role ของผู้ใช้ที่อนุมัติแล้ว
     */
    public function updateRoles(Request $request, User $user)
    {
        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user->roles()->sync($request->role_ids);

        return redirect()->back()->with('success', 'อัพเดท Role ของ ' . $user->name . ' เรียบร้อยแล้ว');
    }

    /**
     * เปิดใช้งานผู้ใช้ที่ถูกปฏิเสธ (เปลี่ยนเป็น pending)
     */
    public function reopen(User $user)
    {
        $user->update([
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        return redirect()->back()->with('success', 'เปิดใช้งานบัญชีของ ' . $user->name . ' เรียบร้อยแล้ว (รอการอนุมัติใหม่)');
    }

    /**
     * ระงับผู้ใช้ (เปลี่ยนจาก approved เป็น pending)
     */
    public function suspend(User $user)
    {
        $user->update([
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'ระงับบัญชีของ ' . $user->name . ' เรียบร้อยแล้ว');
    }
}
