@extends('layouts.admin')

@section('title', 'จัดการผู้ใช้')

@section('content')
    <div class="container my-5">
        <div class="card-header">
            <h1 class="text-center">จัดการผู้ใช้</h1>
        </div>
        <div class="py-2"></div>

        {{-- Status Summary --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center bg-warning text-white">
                    <div class="card-status-summary">
                        <h3>{{ $pendingCount }}</h3>
                        <p class="mb-0"><i class="bi bi-clock"></i> รอการอนุมัติ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-success text-white">
                    <div class="card-status-summary">
                        <h3>{{ $approvedCount }}</h3>
                        <p class="mb-0"><i class="bi bi-check-circle"></i> อนุมัติแล้ว</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center bg-danger text-white">
                    <div class="card-status-summary">
                        <h3>{{ $rejectedCount }}</h3>
                        <p class="mb-0"><i class="bi bi-x-circle"></i> ปฏิเสธแล้ว</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toolbar --}}

        <div class="card-custom-secondary mb-3">
            <form method="GET" action="{{ route('user_management.index') }}"
                class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">
                <!-- Date Filter (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dateFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-calendar-event"></i>
                        @if (request('selected_date') == 'today')
                            วันนี้
                        @elseif(request('selected_date') == 'this_week')
                            สัปดาห์นี้
                        @elseif(request('selected_date') == 'this_month')
                            เดือนนี้
                        @elseif(request('selected_date') == 'this_year')
                            ปีนี้
                        @else
                            วันที่ทั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('selected_date') == '' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->except('selected_date'), [])) }}">วันที่ทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'today' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->all(), ['selected_date' => 'today'])) }}">วันนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_week' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->all(), ['selected_date' => 'this_week'])) }}">สัปดาห์นี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_month' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->all(), ['selected_date' => 'this_month'])) }}">เดือนนี้</a>
                        </li>
                        <li><a class="dropdown-item {{ request('selected_date') == 'this_year' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->all(), ['selected_date' => 'this_year'])) }}">ปีนี้</a>
                        </li>
                    </ul>
                </div>

                <!-- Status Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="statusFilterBtn"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-funnel"></i>
                        @if (request('status') == 'pending')
                            รอการอนุมัติ
                        @elseif(request('status') == 'approved')
                            อนุมัติแล้ว
                        @elseif(request('status') == 'rejected')
                            ปฏิเสธแล้ว
                        @else
                            สถานะทั้งหมด
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('status') == '' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->except('status'), [])) }}">สถานะทั้งหมด</a>
                        </li>
                        <li><a class="dropdown-item {{ request('status') == 'pending' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->all(), ['status' => 'pending'])) }}">รอการอนุมัติ</a>
                        </li>
                        <li><a class="dropdown-item {{ request('status') == 'approved' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->all(), ['status' => 'approved'])) }}">อนุมัติแล้ว</a>
                        </li>
                        <li><a class="dropdown-item {{ request('status') == 'rejected' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->all(), ['status' => 'rejected'])) }}">ปฏิเสธแล้ว</a>
                        </li>
                    </ul>
                </div>

                <!-- Sort Dropdown (Orange) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down"></i>
                        @if (request('sort_by') == 'name')
                            ชื่อ
                        @elseif(request('sort_by') == 'created_at')
                            วันที่สมัคร
                        @else
                            เรียงตาม
                        @endif
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request('sort_by') == 'name' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->all(), ['sort_by' => 'name', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}">ชื่อ</a>
                        </li>
                        <li><a class="dropdown-item {{ request('sort_by') == 'created_at' ? 'active' : '' }}"
                                href="{{ route('user_management.index', array_merge(request()->all(), ['sort_by' => 'created_at', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}">วันที่สมัคร</a>
                        </li>
                    </ul>
                </div>

                <!-- Per Page -->
                @include('components.per-page-dropdown')

                {{-- Export Section --}}
                <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
                    <div class="d-flex gap-2 align-items-center">
                        <label class="text-nowrap small mb-0" style="min-width: 100px;">
                            <i class="bi bi-calendar-range"></i> ช่วงวันที่:
                        </label>
                        <input type="date" id="exportDateFrom" class="form-control form-control-sm"
                            style="width: 140px;">
                        <span class="text-nowrap small">ถึง</span>
                        <input type="date" id="exportDateTo" class="form-control form-control-sm" style="width: 140px;">
                    </div>
                    <button type="button" class="btn btn-sm btn-success" id="exportCsvBtn">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                    </button>
                </div>
            </form>
        </div>

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
                                <td>
                                    <div class="d-flex gap-1 flex-wrap justify-content-start align-items-stretch">

                                        @if ($user->status == 'pending')
                                            {{-- ปุ่มอนุมัติ --}}
                                            <button type="button" class="btn btn-sm btn-success btn-equal"
                                                data-bs-toggle="modal" data-bs-target="#approveModal{{ $user->id }}">
                                                <i class="bi bi-check-circle"></i> อนุมัติ
                                            </button>

                                            {{-- ปุ่มปฏิเสธ --}}
                                            <button type="button" class="btn btn-sm btn-danger btn-equal"
                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $user->id }}">
                                                <i class="bi bi-x-circle"></i> ปฏิเสธ
                                            </button>
                                        @elseif ($user->status == 'approved')
                                            {{-- ปุ่มจัดการ Role --}}
                                            <button type="button" class="btn btn-sm btn-primary btn-equal"
                                                data-bs-toggle="modal"
                                                data-bs-target="#updateRoleModal{{ $user->id }}">
                                                <i class="bi bi-person-badge"></i> จัดการ Role
                                            </button>

                                            {{-- ปุ่มยกเลิกลงทะเบียน --}}
                                            @if ($user->hasCancellationRequest())
                                                <div class="btn-group" role="group">
                                                    <form
                                                        action="{{ route('user_management.approve_cancel', $user->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-warning btn-equal"
                                                            title="อนุมัติการยกเลิก">
                                                            <i class="bi bi-check"></i> อนุมัติยกเลิก
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('user_management.reject_cancel', $user->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-secondary btn-equal"
                                                            title="ปฏิเสธการยกเลิก">
                                                            <i class="bi bi-x"></i> ปฏิเสธยกเลิก
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        @elseif ($user->status == 'cancelled')
                                            <span class="badge bg-danger align-self-center"><i class="bi bi-ban"></i>
                                                ปิดใช้งาน</span>
                                        @endif

                                        {{-- ปุ่มดูรายละเอียด --}}
                                        <button type="button" class="btn btn-sm btn-info btn-equal"
                                            data-bs-toggle="modal" data-bs-target="#viewModal{{ $user->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        {{-- ปุ่มลบ --}}
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('user_management.destroy', $user->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('ต้องการลบผู้ใช้นี้หรือไม่?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger btn-equal">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>


                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-danger">ไม่มีข้อมูลผู้ใช้</td>
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
                                        <input class="form-check-input" type="radio" name="role_ids[]"
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
                            <button type="submit" class="btn btn-success" onclick="return validateRoleSelection(this)">
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
                                        <input class="form-check-input" type="radio" name="role_ids[]"
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
                            <button type="submit" class="btn btn-primary"
                                onclick="return validateUpdateRoleSelection(this)">
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


    @push('scripts')
        <script>
            /**
             * Validate role selection for approval
             */
            function validateRoleSelection(button) {
                const form = button.closest('form');
                const selectedRole = form.querySelector('input[name="role_ids[]"]:checked');

                if (!selectedRole) {
                    showSnackbar('กรุณาเลือก Role ก่อนอนุมัติ', 'warning');
                    return false;
                }

                return true;
            }

            /**
             * Validate role selection for update
             */
            function validateUpdateRoleSelection(button) {
                const form = button.closest('form');
                const selectedRole = form.querySelector('input[name="role_ids[]"]:checked');

                if (!selectedRole) {
                    showSnackbar('กรุณาเลือก Role ก่อนบันทึก', 'warning');
                    return false;
                }

                return true;
            }

            /**
             * Show Snackbar notification
             */
            function showSnackbar(message, type = 'info') {
                const snackbar = document.createElement('div');
                snackbar.className = `alert alert-${type} alert-dismissible fade show`;
                snackbar.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    z-index: 9999;
                    min-width: 300px;
                    max-width: 500px;
                `;
                snackbar.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                document.body.appendChild(snackbar);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    snackbar.remove();
                }, 5000);
            }

            /**
             * Export CSV with date filter
             */
            document.addEventListener('DOMContentLoaded', function() {
                const exportBtn = document.getElementById('exportCsvBtn');
                if (exportBtn) {
                    exportBtn.addEventListener('click', function() {
                        console.log('📥 [User Management] Exporting CSV');
                        const dateFrom = document.getElementById('exportDateFrom').value;
                        const dateTo = document.getElementById('exportDateTo').value;

                        let url = `{{ route('user_management.export.csv') }}`;
                        const params = new URLSearchParams();

                        if (dateFrom) params.set('export_date_from', dateFrom);
                        if (dateTo) params.set('export_date_to', dateTo);

                        if (params.toString()) {
                            url += '?' + params.toString();
                        }

                        window.location.href = url;
                    });
                }
            });
        </script>
    @endpush
@endsection
