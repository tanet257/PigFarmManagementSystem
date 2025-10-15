@extends('layouts.admin')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fa fa-bell mr-2"></i>การแจ้งเตือน</h2>
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
        <div class="card">
            <div class="card-body">
                @if ($notifications->count() > 0)
                    <div class="list-group">
                        @foreach ($notifications as $notification)
                            <div class="list-group-item {{ $notification->is_read ? 'bg-tertiary' : 'bg-secondary' }}">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="mr-2">
                                                @if ($notification->type == 'user_registered')
                                                    <i class="fa fa-user-plus text-primary fa-2x"></i>
                                                @elseif($notification->type == 'user_approved')
                                                    <i class="fa fa-check-circle text-success fa-2x"></i>
                                                @elseif($notification->type == 'user_rejected')
                                                    <i class="fa fa-times-circle text-danger fa-2x"></i>
                                                @else
                                                    <i class="fa fa-bell text-info fa-2x"></i>
                                                @endif
                                            </span>
                                            <div>
                                                <h5 class="mb-1">{{ $notification->title }}</h5>
                                                <p class="mb-1 text-muted">{{ $notification->message }}</p>
                                                <small class="text-muted">
                                                    <i class="fa fa-clock"></i>
                                                    {{ $notification->created_at->diffForHumans() }}
                                                    @if ($notification->is_read)
                                                        <span class="badge badge-success ml-2">อ่านแล้ว</span>
                                                    @else
                                                        <span class="badge badge-danger ml-2">ใหม่</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        @if (!$notification->is_read)
                                            <form method="POST"
                                                action="{{ route('notifications.mark_as_read', $notification->id) }}"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary"
                                                    title="ทำเครื่องหมายว่าอ่าน">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if ($notification->url)
                                            <a href="{{ $notification->url }}" class="btn btn-sm btn-info"
                                                title="ไปยังหน้า">
                                                <i class="fa fa-external-link"></i>
                                            </a>
                                        @endif
                                        <form method="POST"
                                            action="{{ route('notifications.destroy', $notification->id) }}"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="ลบ"
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
    </div>

@endsection
