@extends('layouts.admin_index')

@section('title', 'จัดการผู้ใช้งาน')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">จัดการผู้ใช้งาน (User Management)</h1>
        </div>
        <div class="py-2"></div>

        {{-- Status Summary --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <h3>{{ $pendingCount }}</h3>
                        <p class="mb-0"><i class="bi bi-clock"></i> รอการอนุมัติ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h3>{{ $approvedCount }}</h3>
                        <p class="mb-0"><i class="bi bi-check-circle"></i> อนุมัติแล้ว</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-danger text-white">
                    <div class="card-body">
                        <h3>{{ $rejectedCount }}</h3>
                        <p class="mb-0"><i class="bi bi-x-circle"></i> ปฏิเสธแล้ว</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toolbar --}}
        <form method="GET" action="{{ route('user_management.index') }}">
            <div class="toolbar-card">
                <select name="status" onchange="this.form.submit()">
                    <option value="">สถานะทั้งหมด</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอการอนุมัติ</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ปฏิเสธแล้ว</option>
                </select>

                <input type="text" name="search" placeholder="ค้นหาชื่อหรืออีเมล..." value="{{ request('search') }}">

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-card mt-3 table-responsive">
            <div class="table-wrapper">
                <table class="table table-primary">
                    <thead>
                        <tr class="text-white">
                            <th class="text-center">ID</th>
                            <th class="text-center">ชื่อ</th>
                            <th class="text-center">อีเมล</th>
                            <th class="text-center">เบอร์โทร</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-center">ลงทะเบียนเมื่อ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="text-center">{{ $user->id }}</td>
                                <td class="text-center">{{ $user->name }}</td>
                                <td class="text-center">{{ $user->email }}</td>
                                <td class="text-center">{{ $user->phone ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($user->roles->count() > 0)
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-info">{{ $role->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-secondary">ไม่มี Role</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($user->status == 'pending')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> รอการอนุมัติ
                                        </span>
                                    @elseif ($user->status == 'approved')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> อนุมัติแล้ว
                                        </span>
                                        @if ($user->approvedBy)
                                            <small class="text-muted d-block">
                                                โดย: {{ $user->approvedBy->name }}
                                            </small>
                                        @endif
                                    @elseif ($user->status == 'rejected')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> ปฏิเสธแล้ว
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-center">
                                    @if ($user->status == 'pending')
                                        {{-- ปุ่มอนุมัติ --}}
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $user->id }}">
                                            <i class="bi bi-check-circle"></i> อนุมัติ
                                        </button>

                                        {{-- ปุ่มปฏิเสธ --}}
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $user->id }}">
                                            <i class="bi bi-x-circle"></i> ปฏิเสธ
                                        </button>
                                    @elseif ($user->status == 'approved')
                                        {{-- ปุ่มจัดการ Role --}}
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#updateRoleModal{{ $user->id }}">
                                            <i class="bi bi-person-badge"></i> จัดการ Role
                                        </button>
                                    @endif

                                    {{-- ปุ่มดูรายละเอียด --}}
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#viewModal{{ $user->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- ปุ่มลบ (ถ้าไม่ใช่ตัวเอง) --}}
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('user_management.destroy', $user->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('ต้องการลบผู้ใช้นี้หรือไม่?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-danger">❌ ไม่มีข้อมูลผู้ใช้</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-between mt-3">
            <div>
                แสดง {{ $users->firstItem() ?? 0 }} ถึง {{ $users->lastItem() ?? 0 }} จาก
                {{ $users->total() ?? 0 }} แถว
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @foreach ($users as $user)
        {{-- Approve Modal --}}
        <div class="modal fade" id="approveModal{{ $user->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-check-circle"></i> อนุมัติผู้ใช้
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('user_management.approve', $user->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                คุณกำลังจะอนุมัติผู้ใช้นี้และกำหนด Role ให้
                            </div>

                            <table class="table table-sm table-bordered mb-3">
                                <tr>
                                    <td class="bg-light" width="30%"><strong>ชื่อ:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>อีเมล:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>เบอร์โทร:</strong></td>
                                    <td>{{ $user->phone ?? '-' }}</td>
                                </tr>
                            </table>

                            <div class="mb-3">
                                <label class="form-label"><strong>เลือก Role <span
                                            class="text-danger">*</span></strong></label>
                                @foreach ($roles as $role)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="role_ids[]"
                                            value="{{ $role->id }}"
                                            id="role{{ $role->id }}_{{ $user->id }}">
                                        <label class="form-check-label"
                                            for="role{{ $role->id }}_{{ $user->id }}">
                                            <strong>{{ $role->name }}</strong> - {{ $role->description }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> ยกเลิก
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> ยืนยันอนุมัติ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="rejectModal{{ $user->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-x-circle"></i> ปฏิเสธผู้ใช้
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('user_management.reject', $user->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                คุณกำลังจะปฏิเสธการลงทะเบียนของผู้ใช้นี้
                            </div>

                            <table class="table table-sm table-bordered mb-3">
                                <tr>
                                    <td class="bg-light" width="30%"><strong>ชื่อ:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="bg-light"><strong>อีเมล:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                            </table>

                            <div class="mb-3">
                                <label class="form-label"><strong>เหตุผลที่ปฏิเสธ <span
                                            class="text-danger">*</span></strong></label>
                                <textarea class="form-control" name="rejection_reason" rows="3" required
                                    placeholder="เช่น: ข้อมูลไม่ครบถ้วน, อีเมลไม่ถูกต้อง, ฯลฯ"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> ยกเลิก
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-check-circle"></i> ยืนยันปฏิเสธ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Update Role Modal --}}
        <div class="modal fade" id="updateRoleModal{{ $user->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-person-badge"></i> จัดการ Role
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('user_management.update_roles', $user->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p><strong>ผู้ใช้:</strong> {{ $user->name }} ({{ $user->email }})</p>

                            <div class="mb-3">
                                <label class="form-label"><strong>เลือก Role <span
                                            class="text-danger">*</span></strong></label>
                                @foreach ($roles as $role)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="role_ids[]"
                                            value="{{ $role->id }}"
                                            id="updaterole{{ $role->id }}_{{ $user->id }}"
                                            {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="updaterole{{ $role->id }}_{{ $user->id }}">
                                            <strong>{{ $role->name }}</strong> - {{ $role->description }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> ยกเลิก
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> บันทึก
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- View Modal --}}
        <div class="modal fade" id="viewModal{{ $user->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">รายละเอียดผู้ใช้</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">ข้อมูลส่วนตัว</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>ID:</strong></td>
                                        <td>{{ $user->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ชื่อ:</strong></td>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>อีเมล:</strong></td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>เบอร์โทร:</strong></td>
                                        <td>{{ $user->phone ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ที่อยู่:</strong></td>
                                        <td>{{ $user->address ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">สถานะและ Role</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>สถานะ:</strong></td>
                                        <td>
                                            @if ($user->status == 'pending')
                                                <span class="badge bg-warning">รอการอนุมัติ</span>
                                            @elseif ($user->status == 'approved')
                                                <span class="badge bg-success">อนุมัติแล้ว</span>
                                            @elseif ($user->status == 'rejected')
                                                <span class="badge bg-danger">ปฏิเสธแล้ว</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Role:</strong></td>
                                        <td>
                                            @if ($user->roles->count() > 0)
                                                @foreach ($user->roles as $role)
                                                    <span class="badge bg-info">{{ $role->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">ไม่มี Role</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($user->approvedBy)
                                        <tr>
                                            <td><strong>อนุมัติโดย:</strong></td>
                                            <td>{{ $user->approvedBy->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>อนุมัติเมื่อ:</strong></td>
                                            <td>{{ $user->approved_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endif
                                    @if ($user->rejection_reason)
                                        <tr>
                                            <td><strong>เหตุผลที่ปฏิเสธ:</strong></td>
                                            <td class="text-danger">{{ $user->rejection_reason }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><strong>ลงทะเบียนเมื่อ:</strong></td>
                                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
