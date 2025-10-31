<header class="header">
    <nav class="navbar navbar-expand-lg">

        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div class="navbar-header">
                <!-- Navbar Header -->
                <a href="{{ route('dashboard') }}" class="navbar-brand">
                    <div class="brand-text brand-big visible text-uppercase"><strong
                            class="text-primary">Pig</strong><strong>Farm</strong></div>
                    <div class="brand-text brand-sm"><strong class="text-primary">P</strong><strong>F</strong></div>
                </a>
                <!-- Sidebar Toggle Btn-->
                <button class="sidebar-toggle"><i class="fa fa-long-arrow-left"></i></button>
            </div>
            <div class="right-menu list-inline no-margin-bottom">

                <!-- Notifications -->
                <div class="list-inline-item">
                    <div class="dropdown">
                        <a id="navbarDropdownNotifications" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link notifications-toggle">
                            <i class="bi bi-bell"></i>
                            @auth
                                @php
                                    $unreadCount = Auth::user()->unreadNotificationsCount();
                                @endphp
                                @if ($unreadCount > 0)
                                    <span class="badge dashbg-2">{{ $unreadCount }}</span>
                                @endif
                            @else
                                <span class="badge dashbg-2">0</span>
                            @endauth
                        </a>
                        @auth
                            <div aria-labelledby="navbarDropdownNotifications"
                                class="dropdown-menu notifications-list shadow-lg"
                                style="min-width: 340px; border-radius: 12px; border: none; overflow: hidden;">
                                <div class="d-flex justify-content-between align-items-center py-3 px-4"
                                    style="background: linear-gradient(135deg, #FF5B22 0%, #FF9130 100%); border-bottom: 1px solid #FF9130;">
                                    <h6 class="mb-0 fw-bold text-light ">
                                        <i class="bi bi-bell me-2"></i>การแจ้งเตือน
                                    </h6>
                                    @php
                                        $unreadCount = Auth::user()->unreadNotificationsCount();
                                    @endphp
                                    @if ($unreadCount > 0)
                                        <span class="badge bg-danger">{{ $unreadCount }}</span>
                                    @endif
                                </div>

                                @php
                                    $recentNotifications = Auth::user()->userNotifications()->latest()->take(5)->get();
                                @endphp

                                <div class="notifications-scroll"
                                    style="max-height: 400px; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none;">
                                    @forelse($recentNotifications as $notification)
                                        <a href="{{ route('notifications.mark_and_navigate', $notification->id) }}"
                                            class="dropdown-item px-4 py-3 {{ !$notification->is_read ? 'bg-white' : 'bg-white' }}"
                                            style="text-decoration: none; transition: all 0.2s ease; border-bottom: 1px solid #e9ecef; display: block;">
                                            <div class="d-flex gap-3 align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <strong class="text-light">{{ $notification->title }}</strong>
                                                        @if (!$notification->is_read)
                                                            <span class="badge bg-danger" style="font-size: 0.65rem;">NEW</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-light mb-2 small" style="font-size: 0.85rem;">
                                                        {{ Str::limit($notification->message, 60) }}</p>
                                                    <small
                                                        class="text-light">{{ $notification->created_at->diffForHumans() }}</small>
                                                </div>
                                                <div>
                                                    @if ($notification->type == 'user_registered')
                                                        <span class="badge bg-info" style="font-size: 0.7rem;">ใหม่</span>
                                                    @elseif($notification->type == 'user_approved')
                                                        <span class="badge bg-success"
                                                            style="font-size: 0.7rem;">อนุมัติ</span>
                                                    @elseif($notification->type == 'user_rejected')
                                                        <span class="badge bg-danger"
                                                            style="font-size: 0.7rem;">ปฏิเสธ</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="dropdown-item text-center py-5">
                                            <i class="bi bi-inbox text-secondary" style="font-size: 2rem;"></i>
                                            <p class="text-secondary mt-2 small">ไม่มีการแจ้งเตือน</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="dropdown-divider m-0"></div>
                                <a href="{{ route('notifications.index') }}"
                                    class="dropdown-item text-center py-3 fw-semibold small"
                                    style="text-decoration: none; color: #0d6efd; background: white; transition: background-color 0.2s;">
                                    ดูการแจ้งเตือนทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
                <!-- Notifications end-->

                <!-- Log out               -->
                <div class="list-inline-item dropdown">
                    <button class="btn btn-primary dropdown-toggle d-flex align-items-center" type="button"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ asset('assets/imgs/Orphie2.png') }}" alt="User" class="rounded-circle me-2"
                            width="32" height="32">
                        <span class="fw-semibold">{{ Auth::user()->name ?? 'ผู้ใช้' }}</span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown"
                        style="border-radius: 12px; min-width: 180px;">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-white"
                                    style="background-color: #273F4F; border-radius: 8px;">
                                    <i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ
                                </button>
                            </form>
                        </li>
                    </ul>

                </div>

            </div>
        </div>
    </nav>
</header>

<style>
    /* ซ่อน scrollbar แต่ยังคงความสามารถในการ scroll */
    .notifications-scroll::-webkit-scrollbar {
        display: none;
    }
</style>
