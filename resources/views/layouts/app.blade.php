<!DOCTYPE html>
<html lang="id" class="{{ session('theme', 'light') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SAMARA — Sales and Marketing Activity Reporting and Analytics">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SAMARA</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Vite-compiled CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- PWA Web App Manifest & Icons --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1e293b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SAMARA">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/apple-touch-icon.png') }}">

    @stack('styles')
</head>
<body>
{{-- Root Alpine scope --}}
<div class="samara-app-container" x-data="{
    mobileMoreOpen: false,
    userDropdown: false,
    notifDropdown: false
}">

    {{-- =====================================================
         LEFT SIDEBAR NAVIGATION (Desktop)
    ====================================================== --}}
    <aside class="samara-sidebar" aria-label="Sidebar Navigation">
        <div>
            {{-- Brand / Logo --}}
            <a href="{{ route('home') }}" class="sidebar-brand" style="background: #ffffff; padding: 10px 14px; border-radius: var(--radius-md); box-shadow: 0 2px 10px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <img src="{{ asset('images/logo.png') }}" alt="SAMARA - Sales and Marketing Activity Reporting and Analytics" style="width: 100%; max-width: 210px; height: auto; display: block;">
            </a>

            {{-- Vertical Desktop Navigation Menu --}}
            <nav class="sidebar-menu" aria-label="Main Navigation">
                <div class="sidebar-menu-label">Menu Utama</div>

                <a href="{{ route('home') }}"
                   class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}"
                   aria-current="{{ request()->routeIs('home') ? 'page' : 'false' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Home</span>
                </a>

                <a href="{{ route('plans.index') }}"
                   class="sidebar-link {{ request()->routeIs('plans.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Monthly Plan</span>
                </a>

                <a href="{{ route('weekly.index') }}"
                   class="sidebar-link {{ request()->routeIs('weekly.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>My Week</span>
                </a>

                <a href="{{ route('reports.index') }}"
                   class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Report</span>
                </a>

                <a href="{{ route('followups.index') }}"
                   class="sidebar-link {{ request()->routeIs('followups.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    <span>Follow-ups</span>
                </a>

                <a href="{{ route('dashboard.index') }}"
                   class="sidebar-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('customers.index') }}"
                   class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    <span>Customers</span>
                </a>

                @if(auth()->user()->isAdmin() || auth()->user()->isDirector())
                <a href="{{ route('director.review') }}"
                   class="sidebar-link {{ request()->routeIs('director.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Director Review</span>
                </a>
                @endif

                @if(!auth()->user()->isAdmin() && !auth()->user()->isDirector())
                <a href="{{ route('directions.myDirections') }}"
                   class="sidebar-link {{ request()->routeIs('directions.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    <span>Arahan Saya</span>
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                <div class="sidebar-menu-label" style="margin-top: 14px;">Pengaturan Admin</div>
                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 10-16 0"/></svg>
                    <span>Admin Panel</span>
                </a>
                @endif

                {{-- PWA Install App Button (Desktop Sidebar) --}}
                <button type="button"
                        class="pwa-install-trigger sidebar-link"
                        onclick="triggerPwaInstall()"
                        style="display:none; background:var(--primary); color:#fff; font-weight:700; border-radius:var(--radius-sm); margin-top:14px; border:none; width:100%; cursor:pointer;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Install App SAMARA</span>
                </button>
            </nav>
        </div>

        {{-- Sidebar Footer (Controls & User Profile) --}}
        <div class="sidebar-footer">
            <div class="sidebar-footer-controls">
                <button type="button"
                        class="theme-toggle"
                        onclick="samaraToggleTheme()"
                        aria-label="Toggle Dark/Light Mode"
                        title="Toggle Dark/Light Mode">
                    <span id="theme-icon">🌙</span>
                </button>

                <div style="position: relative;" x-data="{ notifOpen: false }">
                    <button type="button"
                            class="notif-bell"
                            @click="notifOpen = !notifOpen"
                            aria-label="Notifikasi"
                            title="Notifikasi">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 01-3.46 0"/>
                        </svg>
                        @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                            <span class="notif-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
                        @endif
                    </button>

                    <div x-show="notifOpen"
                         @click.away="notifOpen = false"
                         class="dropdown-menu"
                         style="bottom: 100%; left: 0; top: auto; min-width: 320px; max-width: 360px; margin-bottom: 8px; padding: 0; overflow: hidden;"
                         x-cloak>
                        <div class="dropdown-header" style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid var(--border); background:var(--surface);">
                            <div style="font-weight: 700; font-size: 13px; color: var(--text); display:flex; align-items:center; gap:6px;">
                                <span>🔔 Notifikasi</span>
                                @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                    <span class="badge badge-gold" style="font-size:10px;">{{ auth()->user()->unreadNotifications->count() }} baru</span>
                                @endif
                            </div>
                            @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                <form action="{{ route('notifications.readAll') }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" style="background:none; border:none; color:var(--primary); font-size:11px; font-weight:600; cursor:pointer; padding:0;">
                                        Tandai semua dibaca
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div style="max-height: 320px; overflow-y: auto;">
                            @if(auth()->check())
                                @forelse(auth()->user()->notifications()->take(6)->get() as $notification)
                                    <div style="padding:12px 16px; border-bottom:1px solid var(--border); background: {{ $notification->unread() ? 'var(--accent-soft)' : 'transparent' }}; transition: background 0.2s;">
                                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px;">
                                            <span style="font-weight:700; font-size:12px; color:var(--primary);">
                                                {{ $notification->data['title'] ?? 'Notifikasi Arahan' }}
                                            </span>
                                            <span style="font-size:10px; color:var(--text-muted);">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div style="font-size:12px; color:var(--text); margin-bottom:6px; line-height:1.4;">
                                            {{ $notification->data['message'] ?? ($notification->data['topic'] ?? '') }}
                                        </div>
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
                                            @if(!empty($notification->data['priority']))
                                                @php
                                                    $pri = $notification->data['priority'];
                                                    $priBadge = match($pri) {
                                                        'CRITICAL' => 'badge-red',
                                                        'HIGH'     => 'badge-gold',
                                                        'LOW'      => 'badge-gray',
                                                        default    => 'badge-navy',
                                                    };
                                                @endphp
                                                <span class="badge {{ $priBadge }}" style="font-size:9px;">
                                                    Prioritas: {{ $pri }}
                                                </span>
                                            @else
                                                <span></span>
                                            @endif
                                            
                                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" style="padding:2px 8px; font-size:10px;">
                                                    Lihat Detail
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div style="padding: 24px 16px; text-align: center; color: var(--text-muted); font-size: 12px;">
                                        🔔 Belum ada notifikasi baru.
                                    </div>
                                @endforelse
                            @else
                                <div style="padding: 24px 16px; text-align: center; color: var(--text-muted); font-size: 12px;">
                                    🔔 Belum ada notifikasi baru.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- User Profile Card --}}
            <div class="sidebar-user-card">
                @if(auth()->user()->profile_picture)
                    <a href="{{ route('profile.edit') }}" title="Edit Profil" style="flex-shrink:0;">
                        <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                             alt="Foto Profil"
                             style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--primary);"
                        >
                    </a>
                @else
                    <a href="{{ route('profile.edit') }}" title="Edit Profil" style="flex-shrink:0;text-decoration:none;">
                        <div class="user-avatar-circle">
                            {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 2)) }}
                        </div>
                    </a>
                @endif
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <a href="{{ route('profile.edit') }}" style="color:inherit;text-decoration:none;">
                            {{ auth()->user()->full_name }}
                        </a>
                    </div>
                    <span class="user-role-tag">
                        {{ auth()->user()->role?->value ?? auth()->user()->role }}
                    </span>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--danger); padding: 4px; display: flex; align-items: center;" title="Keluar (Logout)">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- =====================================================
         MOBILE TOP BAR (< 1024px)
    ====================================================== --}}
    <header class="mobile-top-bar">
        <a href="{{ route('home') }}" class="navbar-brand" style="text-decoration:none; background:#ffffff; padding: 4px 8px; border-radius: 6px;">
            <img src="{{ asset('images/logo.png') }}" alt="SAMARA Logo" style="height: 32px; width: auto; display: block;">
        </a>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button type="button" class="theme-toggle" onclick="samaraToggleTheme()">
                <span id="theme-icon-mobile">🌙</span>
            </button>
            <a href="{{ route('profile.edit') }}" title="Edit Profil" style="display:flex;align-items:center;text-decoration:none;">
                @if(auth()->user()->profile_picture)
                    <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                         alt="Foto Profil"
                         style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid var(--primary);">
                @else
                    <div class="user-avatar-circle" style="width:32px; height:32px; font-size:11px;">
                        {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 2)) }}
                    </div>
                @endif
            </a>
        </div>
    </header>

    {{-- =====================================================
         MAIN CONTENT AREA (Right of Sidebar)
    ====================================================== --}}
    <main class="samara-main-content" id="main-content" role="main">

        {{-- Page Title Bar --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">@yield('page_title', 'Dashboard')</h1>
                <p class="page-subtitle">@yield('page_subtitle', 'Sales and Marketing Activity Reporting and Analytics')</p>
            </div>
            @hasSection('header_actions')
            <div>@yield('header_actions')</div>
            @endif
        </div>

        {{-- Director Direction Alert Banner --}}
        @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
            @php
                $latestDirectorNotif = auth()->user()->unreadNotifications->first();
            @endphp
            @if($latestDirectorNotif)
                <div class="alert alert-warning" style="border-left: 4px solid var(--accent); background: var(--accent-soft); color: var(--primary); display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px;" role="alert">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                        <div>
                            <strong>{{ $latestDirectorNotif->data['title'] ?? 'Arahan Director Baru' }}:</strong>
                            <span>{{ $latestDirectorNotif->data['message'] ?? '' }}</span>
                        </div>
                    </div>
                    <form action="{{ route('notifications.read', $latestDirectorNotif->id) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">Lihat & Response</button>
                    </form>
                </div>
            @endif
        @endif

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" role="alert">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning" role="alert">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <div style="font-weight:700; margin-bottom: 4px;">Terdapat kesalahan input:</div>
                    <ul style="margin-left: 18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Page Content --}}
        @yield('content')
    </main>

    {{-- =====================================================
         MOBILE BOTTOM NAVIGATION
    ====================================================== --}}
    <nav class="mobile-bottom-nav" aria-label="Mobile Navigation">
        <a href="{{ route('home') }}"
           id="mobile-nav-home"
           class="mobile-nav-btn {{ request()->routeIs('home') ? 'active' : '' }}"
           aria-label="Home">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </span>
            <span>Home</span>
        </a>

        <a href="{{ route('weekly.index') }}"
           id="mobile-nav-week"
           class="mobile-nav-btn {{ request()->routeIs('weekly.*') ? 'active' : '' }}"
           aria-label="My Week">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <span>My Week</span>
        </a>

        <a href="{{ route('reports.index') }}"
           id="mobile-nav-report"
           class="mobile-nav-btn {{ request()->routeIs('reports.*') ? 'active' : '' }}"
           aria-label="Report">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
            <span>Report</span>
        </a>

        <button type="button"
                id="mobile-nav-more"
                class="mobile-nav-btn"
                @click="mobileMoreOpen = true"
                aria-label="More Menu"
                aria-expanded="false">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </span>
            <span>More</span>
        </button>
    </nav>

    {{-- =====================================================
         MOBILE MORE BOTTOM SHEET / DRAWER
    ====================================================== --}}
    <div x-show="mobileMoreOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 3000; display: flex; align-items: flex-end;"
         @click.self="mobileMoreOpen = false"
         x-cloak
         aria-modal="true"
         role="dialog"
         aria-label="More Menu">

        <div style="background: var(--surface); width: 100%; border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 20px; max-height: 85vh; overflow-y: auto;"
             @click.away="mobileMoreOpen = false">

            {{-- Drag handle --}}
            <div style="width: 40px; height: 4px; background: var(--border); border-radius: 99px; margin: 0 auto 20px;"></div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div style="background: #ffffff; padding: 6px 10px; border-radius: 6px;">
                    <img src="{{ asset('images/logo.png') }}" alt="SAMARA Logo" style="max-width: 180px; width: 100%; height: auto; display: block;">
                </div>
                <button @click="mobileMoreOpen = false"
                        style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-secondary); padding: 4px;"
                        aria-label="Tutup">✕</button>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <a href="{{ route('plans.index') }}" class="dropdown-item" style="border: 1px solid var(--border); border-radius: var(--radius-sm);" @click="mobileMoreOpen = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Monthly Plan
                </a>

                <a href="{{ route('followups.index') }}" class="dropdown-item" style="border: 1px solid var(--border); border-radius: var(--radius-sm);" @click="mobileMoreOpen = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    Follow-ups
                </a>

                <a href="{{ route('dashboard.index') }}" class="dropdown-item" style="border: 1px solid var(--border); border-radius: var(--radius-sm);" @click="mobileMoreOpen = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard Analitik
                </a>

                <a href="{{ route('directions.myDirections') }}" class="dropdown-item" style="border: 1px solid var(--border); border-radius: var(--radius-sm);" @click="mobileMoreOpen = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Arahan untuk Saya
                </a>

                @if(auth()->user()->isAdmin() || auth()->user()->isDirector())
                <a href="{{ route('director.review') }}" class="dropdown-item" style="border: 1px solid var(--border); border-radius: var(--radius-sm);" @click="mobileMoreOpen = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Director Review
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="dropdown-item" style="border: 1px solid var(--border); border-radius: var(--radius-sm);" @click="mobileMoreOpen = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 10-16 0"/></svg>
                    Admin Panel
                </a>
                @endif

                <div style="height: 1px; background: var(--border); margin: 6px 0;"></div>

                <button type="button" class="dropdown-item" style="border: 1px solid var(--border); border-radius: var(--radius-sm);" onclick="samaraToggleTheme(); mobileMoreOpen = false">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
                    Ganti Tema (Light/Dark)
                </button>

                {{-- PWA Install Trigger (Mobile Sheet) --}}
                <button type="button"
                        class="pwa-install-trigger dropdown-item"
                        onclick="triggerPwaInstall(); mobileMoreOpen = false"
                        style="display:none; border: 1px solid var(--primary); border-radius: var(--radius-sm); background: var(--accent-soft); color: var(--primary); font-weight:700;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Install Aplikasi SAMARA
                </button>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item danger" style="width:100%; border: 1px solid var(--danger-soft); border-radius: var(--radius-sm); background: var(--danger-soft);">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Keluar (Logout)
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>{{-- .samara-app-container --}}

@stack('scripts')

{{-- =====================================================
     THEME TOGGLE SCRIPT
====================================================== --}}
<script>
    // Theme management
    function samaraToggleTheme() {
        const html = document.documentElement;
        const isDark = html.classList.toggle('dark');
        localStorage.setItem('samara_theme', isDark ? 'dark' : 'light');
        updateThemeIcon(isDark);
        fetch('{{ route('home') }}?_theme=' + (isDark ? 'dark' : 'light'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ theme: isDark ? 'dark' : 'light' })
        }).catch(() => {});
    }

    function updateThemeIcon(isDark) {
        const icon = document.getElementById('theme-icon');
        const iconMobile = document.getElementById('theme-icon-mobile');
        if (icon) icon.textContent = isDark ? '☀️' : '🌙';
        if (iconMobile) iconMobile.textContent = isDark ? '☀️' : '🌙';
    }

    (function() {
        const saved = localStorage.getItem('samara_theme');
        if (saved === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (saved === 'light') {
            document.documentElement.classList.remove('dark');
        }
        const isDark = document.documentElement.classList.contains('dark');
        updateThemeIcon(isDark);
    })();

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.4s ease';
                alert.style.opacity = '0';
                setTimeout(function() { alert.remove(); }, 400);
            }, 6000);
        });

        document.querySelectorAll('[data-confirm]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                const msg = btn.getAttribute('data-confirm') || 'Apakah Anda yakin?';
                if (!confirm(msg)) e.preventDefault();
            });
        });

        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn && !form.dataset.allowMultiple) {
                    setTimeout(function() {
                        submitBtn.disabled = true;
                        submitBtn.textContent = form.method.toUpperCase() === 'GET' ? 'Memuat...' : 'Menyimpan...';
                    }, 10);
                }
            });
        });
    });

    // PWA Service Worker & Installation Prompt Script
    let deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        document.querySelectorAll('.pwa-install-trigger').forEach(btn => {
            btn.style.display = 'flex';
        });
    });

    function triggerPwaInstall() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted PWA installation');
                }
                deferredPrompt = null;
                document.querySelectorAll('.pwa-install-trigger').forEach(btn => {
                    btn.style.display = 'none';
                });
            });
        }
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(reg) {
                    console.log('[PWA] Service Worker registered with scope:', reg.scope);
                })
                .catch(function(err) {
                    console.warn('[PWA] Service Worker registration failed:', err);
                });
        });
    }
</script>

</body>
</html>

