@extends('layouts.admin')

@section('title', 'อนุมัติการชำระเงินค่าใช้จ่าย')

@section('content')
    <div class="container-fluid py-4 notifications-page">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fa fa-bell mr-2 me-2"></i>การแจ้งเตือน</h2>
            <div>
                <form method="POST" action="{{ route('notifications.mark_all_as_read') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check-double"></i> ทำเครื่องหมายว่าอ่านทั้งหมด
                    </button>
                </form>
                <form method="POST" action="{{ route('notifications.clear_read') }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('ต้องการลบแจ้งเตือนที่อ่านแล้วทั้งหมดใช่หรือไม่?')">
                        <i class="fa fa-trash"></i> ลบที่อ่านแล้ว
                    </button>
                </form>
            </div>
        </div>


        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- สรุปแจ้งเตือน -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-status-summary">
                        <h5 class="card-title text-center"><i class="fa fa-bell"></i> ทั้งหมด</h5>
                        <h2 class="mb-0 text-center">{{ $notifications->total() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-status-summary">
                        <h5 class="card-title text-center"><i class="fa fa-envelope"></i> ยังไม่อ่าน</h5>
                        <h2 class="mb-0 text-center">{{ $unreadCount }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-status-summary">
                        <h5 class="card-title text-center"><i class="fa fa-envelope-open"></i> อ่านแล้ว</h5>
                        <h2 class="mb-0 text-center">{{ $notifications->total() - $unreadCount }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายการแจ้งเตือน -->

        <div class="">
            @if ($notifications->count() > 0)
                <div class="list-group notification-list">
                    @foreach ($notifications as $notification)
                        <div
                            class="list-group-item notification-item {{ $notification->is_read ? 'notification-read' : 'notification-unread' }}"
                            style="position: relative; padding-right: 30px;">
                            {{-- 🔴 Unread Indicator Badge (มุมขวาบน) --}}
                            @if (!$notification->is_read)
                                <span style="position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background-color: #dc3545; border-radius: 50%; display: inline-block;" title="ยังไม่ได้อ่าน"></span>
                            @endif

                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-start w-100">
                                    @php
                                        $rawType = $notification->type ?? ($notification->data['type'] ?? null ?? '');
                                        $typeMap = [
                                            'payment' => ['การชำระเงิน', '<i class="bi bi-credit-card-2-front"></i>'],
                                            'approval' => ['รออนุมัติ', '<i class="bi bi-check2-square"></i>'],
                                            'stock' => ['คลังสินค้า', '<i class="bi bi-box-seam"></i>'],
                                            'pig' => ['ข้อมูลสุกร', '<i class="bi bi-piggy-bank"></i>'],
                                            'user' => ['ผู้ใช้งาน', '<i class="bi bi-person"></i>'],
                                            'system' => ['ระบบ', '<i class="bi bi-gear"></i>'],
                                        ];

                                        $matchedType = null;
                                        if (isset($typeMap[$rawType])) {
                                            $matchedType = $rawType;
                                        } else {
                                            $rt = strtolower($rawType);
                                            if (
                                                str_contains($rt, 'approve') ||
                                                str_contains($rt, 'approval') ||
                                                str_contains($rt, 'auth')
                                            ) {
                                                $matchedType = 'approval';
                                            } elseif (str_contains($rt, 'pay') || str_contains($rt, 'payment')) {
                                                $matchedType = 'payment';
                                            } elseif (str_contains($rt, 'stock') || str_contains($rt, 'inventory')) {
                                                $matchedType = 'stock';
                                            } elseif (str_contains($rt, 'pig') || str_contains($rt, 'sow')) {
                                                $matchedType = 'pig';
                                            } elseif (str_contains($rt, 'user')) {
                                                $matchedType = 'user';
                                            } elseif (str_contains($rt, 'system')) {
                                                $matchedType = 'system';
                                            }
                                        }

                                        $displayType = $matchedType ? $typeMap[$matchedType][0] : 'ทั่วไป';
                                        $icon = $matchedType ? $typeMap[$matchedType][1] : '<i class="bi bi-bell"></i>';
                                        $typeClass = $matchedType ? 'type-' . $matchedType : 'type-default';
                                    @endphp

                                    {{-- Icon ด้านซ้าย --}}
                                    <div class="notification-icon me-3">
                                        {!! $icon !!}
                                    </div>

                                    {{-- Content ส่วนหลัก --}}
                                    <div class="notification-content me-3">
                                        {{-- บรรทัดที่ 1: หัวข้อและป้าย "ใหม่" --}}
                                        <div class="d-flex align-items-center mb-2">
                                            <h6 class="mb-0 me-2">{{ $notification->title }}</h6>
                                            @if (!$notification->is_read)
                                                <span class="badge bg-danger rounded-pill">ใหม่</span>
                                            @endif
                                        </div>

                                        {{-- บรรทัดที่ 2: เนื้อหาข้อความ --}}
                                        <p class="mb-2 text-muted small message-clamp">{!! \Illuminate\Support\Str::limit(strip_tags($notification->message), 120) !!}</p>

                                        {{-- บรรทัดที่ 3: เวลาและประเภท --}}
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="time-badge">
                                                <i class="bi bi-clock"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>

                                        </div>

                                    </div>
                                </div>
                                <div class="notification-actions d-flex gap-2">
                                    {{-- 🔄 Smart Navigation: นำทางตามประเภทแจ้งเตือน --}}
                                    @php
                                        $routeMap = [
                                            // ============ ผู้ใช้งาน ============
                                            'user_registered' => 'user_management.index',
                                            'user_approved' => 'user_management.index',
                                            'user_rejected' => 'user_management.index',
                                            'user_registration_cancelled' => 'user_management.index',
                                            'user_role_updated' => 'user_management.index',

                                            // ============ การรับเข้าหมู ============
                                            'pig_entry_recorded' => 'pig_entry_records.index',
                                            'pig_entry_payment_approved' => 'pig_entry_records.index',
                                            'payment_recorded_pig_entry' => 'cost_payment_approvals.index',

                                            // ============ การขายหมู ============
                                            'pig_sale' => 'payment_approvals.index',
                                            'pig_sale_approved' => 'pig_entry_records.index',
                                            'pig_sale_rejected' => 'pig_entry_records.index',
                                            'pig_sale_cancelled' => 'pig_entry_records.index',
                                            'pig_sale_cancel_request' => 'payment_approvals.index',
                                            'pig_sale_cancel_approved' => 'pig_entry_records.index',
                                            'pig_sale_cancel_rejected' => 'pig_entry_records.index',
                                            'pig_sale_status_changed' => 'pig_entry_records.index',
                                            'payment_recorded_pig_sale' => 'payment_approvals.index',
                                            'payment_approved' => 'payment_approvals.index',
                                            'payment_rejected' => 'payment_approvals.index',

                                            // ============ ต้นทุน / ค่าใช้จ่าย ============
                                            'cost_pending_approval' => 'cost_payment_approvals.index',
                                            'cost_approved' => 'cost_payment_approvals.index',
                                            'cost_rejected' => 'cost_payment_approvals.index',
                                            'cost_payment_cancelled' => 'cost_payment_approvals.index',
                                            'cost_payment_approved' => 'cost_payment_approvals.index',
                                            'cost_payment_rejected' => 'cost_payment_approvals.index',
                                            'payment_recorded' => 'cost_payment_approvals.index',

                                            // ============ หมูตาย ============
                                            'pig_death' => 'pig_entry_records.index',

                                            // ============ การรักษา ============
                                            'batch_treatment' => 'treatments.index',

                                            // ============ คลังสินค้า ============
                                            'inventory_movement' => 'inventory_movements.index',
                                            'stock_low' => 'storehouse_records.index',

                                            // ============ ระบบ ============
                                            'batch_deleted' => 'batch.index',
                                            'cancel_pig_sale' => 'payment_approvals.index',
                                            'system_alert' => 'dashboard',
                                            'system_maintenance' => 'dashboard',
                                        ];
                                        $notificationType = $notification->type ?? null;
                                        $targetRoute = $routeMap[$notificationType] ?? 'notifications.index';
                                        $targetTitle = match($targetRoute) {
                                            'user_management.index' => 'ไปที่จัดการผู้ใช้',
                                            'pig_entry_records.index' => 'ไปที่บันทึกการเข้าสุกร',
                                            'cost_payment_approvals.index' => 'ไปที่อนุมัติต้นทุน',
                                            'payment_approvals.index' => 'ไปที่การอนุมัติชำระเงิน',
                                            'inventory_movements.index' => 'ไปที่การเคลื่อนไหวสินค้า',
                                            'storehouse_records.index' => 'ไปที่บันทึกคลังสินค้า',
                                            'treatments.index' => 'ไปที่บันทึกการรักษา',
                                            'batch.index' => 'ไปที่รุ่นหมู',
                                            'dashboard' => 'ไปที่แดชบอร์ด',
                                            default => 'ดูรายละเอียด'
                                        };
                                    @endphp

                                    <a href="{{ route('notifications.mark_and_navigate', $notification->id) }}"
                                        class="btn btn-sm btn-primary px-3" title="{{ $targetTitle }}">
                                        <i class="fa fa-arrow-right"></i>
                                        {{ $targetTitle }}
                                    </a>

                                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบแจ้งเตือน"
                                            onclick="return confirm('ต้องการลบแจ้งเตือนนี้ใช่หรือไม่?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4 d-flex justify-content-center">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fa fa-bell-slash fa-5x text-muted mb-3"></i>
                    <h4 class="text-muted">ไม่มีการแจ้งเตือน</h4>
                </div>
            @endif
        </div>

    </div>

@endsection
