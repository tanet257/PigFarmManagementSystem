<header class="header">
    <nav class="navbar navbar-expand-lg">
        <div class="search-panel">
            <div class="search-inner d-flex align-items-center justify-content-center">
                <div class="close-btn">Close <i class="fa fa-close"></i></div>
                <form id="searchForm" action="#">
                    <div class="form-group">
                        <input type="search" name="search" placeholder="What are you searching for...">
                        <button type="submit" class="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div class="navbar-header">
                <!-- Navbar Header--><a href={{ url('admin_index') }} class="navbar-brand">
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
                        <a id="navbarDropdownNotifications" href="#" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link notifications-toggle">
                            <i class="bi bi-bell"></i>
                            @php
                                $unreadCount = Auth::user()->unreadNotificationsCount();
                            @endphp
                            @if ($unreadCount > 0)
                                <span class="badge dashbg-2">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <div aria-labelledby="navbarDropdownNotifications" class="dropdown-menu notifications-list">
                            <h6 class="dropdown-header">
                                <i class="bi bi-bell"></i> การแจ้งเตือน
                                @if ($unreadCount > 0)
                                    <span class="badge bg-danger ms-2">{{ $unreadCount }}</span>
                                @endif
                            </h6>
                            <div class="dropdown-divider"></div>

                            @php
                                $recentNotifications = Auth::user()->userNotifications()->latest()->take(5)->get();
                            @endphp

                            @forelse($recentNotifications as $notification)
                                <a href="{{ $notification->url ?? route('notifications.index') }}"
                                    class="dropdown-item notification-item {{ !$notification->is_read ? 'unread' : '' }}">
                                    <div class="d-flex align-items-start">
                                        <div class="notification-content flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <strong class="me-2">{{ $notification->title }}</strong>
                                                @if ($notification->type == 'user_registered')
                                                    <span class="badge bg-info">ใหม่</span>
                                                @elseif($notification->type == 'user_approved')
                                                    <span class="badge bg-success">อนุมัติ</span>
                                                @elseif($notification->type == 'user_rejected')
                                                    <span class="badge bg-danger">ปฏิเสธ</span>
                                                @endif
                                            </div>
                                            <span
                                                class="d-block text-secondary small">{{ Str::limit($notification->message, 50) }}</span>
                                            <small
                                                class="text-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="dropdown-item text-center text-secondary">
                                    <i class="bi bi-inbox"></i>
                                    <p class="mb-0">ไม่มีการแจ้งเตือน</p>
                                </div>
                            @endforelse

                            <div class="dropdown-divider"></div>
                            <a href="{{ route('notifications.index') }}" class="dropdown-item text-center">
                                <strong>ดูการแจ้งเตือนทั้งหมด <i class="fa fa-angle-right"></i></strong>
                            </a>
                        </div>
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
