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
                            class="list-group-item notification-item {{ $notification->is_read ? 'notification-read' : 'notification-unread' }}">
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
                                    <a href="{{ route('notifications.mark_and_navigate', $notification->id) }}"
                                        class="btn btn-sm btn-primary px-3" title="ไปที่หน้าแจ้งเตือน">
                                        <i class="fa fa-arrow-right"></i>
                                        ดูรายละเอียด
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
