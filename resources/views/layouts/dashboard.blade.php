<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('messages.dashboard')) - {{ __('messages.appTitle') }}</title>

    <!-- Theme Init: prevent flash -->
    <script>!function(){try{var t=localStorage.getItem('clinic_theme');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}}();</script>
    <!-- Locale Init: sync server locale to localStorage for JS -->
    <script>!function(){try{var s='{{ app()->getLocale() }}';if(s){localStorage.setItem('clinic_lang',s)}}catch(e){}}();</script>
    <!-- CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}?v={{ filemtime(public_path('vendor/bootstrap/css/bootstrap.min.css')) }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}?v={{ filemtime(public_path('vendor/fontawesome/css/all.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/fontawesome-local.css') }}?v={{ filemtime(public_path('vendor/fontawesome/css/fontawesome-local.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/davinci.css') }}?v={{ filemtime(public_path('css/davinci.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}?v={{ filemtime(public_path('css/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <!-- Local Fonts (self-hosted, zero CLS) -->
    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans/plus-jakarta-sans-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/tajawal/tajawal-arabic-400.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/tajawal/tajawal-arabic-700.woff2') }}" as="font" type="font/woff2" crossorigin>
    <style>
        @font-face { font-family:'Plus Jakarta Sans'; src:url('{{ asset('fonts/plus-jakarta-sans/plus-jakarta-sans-latin.woff2') }}') format('woff2'); font-weight:400 800; font-display:block; unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD; }
        @font-face { font-family:'Tajawal'; src:url('{{ asset('fonts/tajawal/tajawal-arabic-400.woff2') }}') format('woff2'); font-weight:400; font-display:block; unicode-range:U+0600-06FF,U+0750-077F,U+0870-088E,U+0890-0891,U+0897-08E1,U+08E3-08FF,U+200C-200E,U+2010-2011,U+204F,U+2E41,U+FB50-FDFF,U+FE70-FE74,U+FE76-FEFC,U+102E0-102FB,U+10E60-10E7E,U+10EC2-10EC4,U+10EFC-10EFF,U+1EE00-1EE03,U+1EE05-1EE1F,U+1EE21-1EE22,U+1EE24,U+1EE27,U+1EE29-1EE32,U+1EE34-1EE37,U+1EE39,U+1EE3B,U+1EE42,U+1EE47,U+1EE49,U+1EE4B,U+1EE4D-1EE4F,U+1EE51-1EE52,U+1EE54,U+1EE57,U+1EE59,U+1EE5B,U+1EE5D,U+1EE5F,U+1EE61-1EE62,U+1EE64,U+1EE67-1EE6A,U+1EE6C-1EE72,U+1EE74-1EE77,U+1EE79-1EE7C,U+1EE7E,U+1EE80-1EE89,U+1EE8B-1EE9B,U+1EEA1-1EEA3,U+1EEA5-1EEA9,U+1EEAB-1EEBB,U+1EEF0-1EEF1; }
        @font-face { font-family:'Tajawal'; src:url('{{ asset('fonts/tajawal/tajawal-latin-400.woff2') }}') format('woff2'); font-weight:400; font-display:block; unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD; }
        @font-face { font-family:'Tajawal'; src:url('{{ asset('fonts/tajawal/tajawal-arabic-500.woff2') }}') format('woff2'); font-weight:500; font-display:block; unicode-range:U+0600-06FF,U+0750-077F,U+0870-088E,U+0890-0891,U+0897-08E1,U+08E3-08FF,U+200C-200E,U+2010-2011,U+204F,U+2E41,U+FB50-FDFF,U+FE70-FE74,U+FE76-FEFC,U+102E0-102FB,U+10E60-10E7E,U+10EC2-10EC4,U+10EFC-10EFF,U+1EE00-1EE03,U+1EE05-1EE1F,U+1EE21-1EE22,U+1EE24,U+1EE27,U+1EE29-1EE32,U+1EE34-1EE37,U+1EE39,U+1EE3B,U+1EE42,U+1EE47,U+1EE49,U+1EE4B,U+1EE4D-1EE4F,U+1EE51-1EE52,U+1EE54,U+1EE57,U+1EE59,U+1EE5B,U+1EE5D,U+1EE5F,U+1EE61-1EE62,U+1EE64,U+1EE67-1EE6A,U+1EE6C-1EE72,U+1EE74-1EE77,U+1EE79-1EE7C,U+1EE7E,U+1EE80-1EE89,U+1EE8B-1EE9B,U+1EEA1-1EEA3,U+1EEA5-1EEA9,U+1EEAB-1EEBB,U+1EEF0-1EEF1; }
        @font-face { font-family:'Tajawal'; src:url('{{ asset('fonts/tajawal/tajawal-latin-500.woff2') }}') format('woff2'); font-weight:500; font-display:block; unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD; }
        @font-face { font-family:'Tajawal'; src:url('{{ asset('fonts/tajawal/tajawal-arabic-700.woff2') }}') format('woff2'); font-weight:700; font-display:block; unicode-range:U+0600-06FF,U+0750-077F,U+0870-088E,U+0890-0891,U+0897-08E1,U+08E3-08FF,U+200C-200E,U+2010-2011,U+204F,U+2E41,U+FB50-FDFF,U+FE70-FE74,U+FE76-FEFC,U+102E0-102FB,U+10E60-10E7E,U+10EC2-10EC4,U+10EFC-10EFF,U+1EE00-1EE03,U+1EE05-1EE1F,U+1EE21-1EE22,U+1EE24,U+1EE27,U+1EE29-1EE32,U+1EE34-1EE37,U+1EE39,U+1EE3B,U+1EE42,U+1EE47,U+1EE49,U+1EE4B,U+1EE4D-1EE4F,U+1EE51-1EE52,U+1EE54,U+1EE57,U+1EE59,U+1EE5B,U+1EE5D,U+1EE5F,U+1EE61-1EE62,U+1EE64,U+1EE67-1EE6A,U+1EE6C-1EE72,U+1EE74-1EE77,U+1EE79-1EE7C,U+1EE7E,U+1EE80-1EE89,U+1EE8B-1EE9B,U+1EEA1-1EEA3,U+1EEA5-1EEA9,U+1EEAB-1EEBB,U+1EEF0-1EEF1; }
        @font-face { font-family:'Tajawal'; src:url('{{ asset('fonts/tajawal/tajawal-latin-700.woff2') }}') format('woff2'); font-weight:700; font-display:block; unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD; }
        @font-face { font-family:'Tajawal'; src:url('{{ asset('fonts/tajawal/tajawal-arabic-800.woff2') }}') format('woff2'); font-weight:800; font-display:block; unicode-range:U+0600-06FF,U+0750-077F,U+0870-088E,U+0890-0891,U+0897-08E1,U+08E3-08FF,U+200C-200E,U+2010-2011,U+204F,U+2E41,U+FB50-FDFF,U+FE70-FE74,U+FE76-FEFC,U+102E0-102FB,U+10E60-10E7E,U+10EC2-10EC4,U+10EFC-10EFF,U+1EE00-1EE03,U+1EE05-1EE1F,U+1EE21-1EE22,U+1EE24,U+1EE27,U+1EE29-1EE32,U+1EE34-1EE37,U+1EE39,U+1EE3B,U+1EE42,U+1EE47,U+1EE49,U+1EE4B,U+1EE4D-1EE4F,U+1EE51-1EE52,U+1EE54,U+1EE57,U+1EE59,U+1EE5B,U+1EE5D,U+1EE5F,U+1EE61-1EE62,U+1EE64,U+1EE67-1EE6A,U+1EE6C-1EE72,U+1EE74-1EE77,U+1EE79-1EE7C,U+1EE7E,U+1EE80-1EE89,U+1EE8B-1EE9B,U+1EEA1-1EEA3,U+1EEA5-1EEA9,U+1EEAB-1EEBB,U+1EEF0-1EEF1; }
        @font-face { font-family:'Tajawal'; src:url('{{ asset('fonts/tajawal/tajawal-latin-800.woff2') }}') format('woff2'); font-weight:800; font-display:block; unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD; }
    </style>
    
    @yield('styles')
    
    <!-- jQuery -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}?v={{ filemtime(public_path('vendor/jquery/jquery.min.js')) }}"></script>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <i class="fas fa-heartbeat pulse-anim"></i>
                <span class="brand-text" data-i18n="appTitle">{{ __('messages.appTitle') }}</span>
            </a>
        </div>

        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-home"></i></div>
                    <span data-i18n="dashboard">{{ __('messages.dashboard') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patients.index') }}" class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-user-injured"></i></div>
                    <span data-i18n="patients">{{ __('messages.patients') }}</span>
                </a>
            </li>
            @if(in_array(auth()->user()->role, ['admin', 'receptionist']))
            <li class="nav-item">
                <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.index') || request()->routeIs('appointments.show') || request()->routeIs('appointments.create') || request()->routeIs('appointments.edit') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-calendar-check"></i></div>
                    <span data-i18n="appointments">{{ __('messages.appointments') }}</span>
                </a>
            </li>
            @endif

            @unless(auth()->user()->hasRole('nurse'))
            <li class="nav-item">
                <a href="{{ route('appointments.calendar') }}" class="nav-link {{ request()->routeIs('appointments.calendar') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-calendar-alt"></i></div>
                    <span data-i18n="calendar">{{ __('messages.calendar') }}</span>
                </a>
            </li>
            @endunless
            @if(in_array(auth()->user()->role, ['admin', 'nurse']))
            <li class="nav-item">
                <a href="{{ route('doctors.index') }}" class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-user-md"></i></div>
                    <span data-i18n="doctors">{{ __('messages.doctors') }}</span>
                </a>
            </li>
            @endif
            @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-briefcase-medical"></i></div>
                    <span data-i18n="services">{{ __('messages.services') }}</span>
                </a>
            </li>
            @endif
            @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-chart-line"></i></div>
                    <span data-i18n="reports">{{ __('messages.reports') }}</span>
                </a>
            </li>
            @endif
            @if(in_array(auth()->user()->role, ['admin', 'receptionist']))
            <li class="nav-item">
                <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-file-invoice-dollar"></i></div>
                    <span data-i18n="invoices">{{ __('messages.invoices') }}</span>
                </a>
            </li>
            @endif
            @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-cog"></i></div>
                    <span data-i18n="settings">{{ __('messages.settings') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-users-cog"></i></div>
                    <span data-i18n="users">{{ __('messages.users') }}</span>
                </a>
            </li>
            @endif
        </ul>

        <ul class="nav flex-column mt-auto sidebar-footer">
            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div class="icon-box"><i class="fas fa-sign-out-alt"></i></div>
                    <span data-i18n="logout">{{ __('messages.logout') }}</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <div class="top-header">
            <div class="header-content glass-effect">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-primary d-lg-none menu-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <div id="page-title-container">
                        <h4 class="mb-0 fw-bold header-title" data-i18n="@yield('page-i18n', 'dashboard')">@yield('page-title', __('messages.dashboard'))</h4>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <div class="search-container d-none d-md-flex">
                        <i class="fas fa-search"></i>
                        <input type="text" class="glass-input" data-i18n-placeholder="searchPlaceholder" placeholder="Search..." aria-label="Search">
                    </div>

                    <div class="header-actions">
                        <!-- Notification Dropdown -->
                        <div class="dropdown">
                            <button class="btn-icon-glass notification-btn position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                                <i class="far fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notificationBadge">
                                    0
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-0 notification-dropdown-menu" aria-labelledby="notificationDropdown">
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold" data-i18n="notifications">{{ __('messages.notifications') }}</h6>
                                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" id="markAllReadForm">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0" data-i18n="markAllRead">{{ __('messages.markAllRead') }}</button>
                                    </form>
                                </div>
                                <div id="notificationList">
                                    <div class="text-center p-4">
                                        <div class="spinner-border text-primary spinner-border-sm" role="status">
                                            <span class="visually-hidden" data-i18n="loading">{{ __('messages.loading') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn-icon-glass theme-toggle" id="themeToggle" aria-label="Toggle theme">
                            <i class="fas fa-moon"></i>
                        </button>

                        <button class="lang-toggle-glass" id="langToggle" aria-label="Switch language">
                            <span id="langToggleText">{{ app()->getLocale() == 'ar' ? 'English' : 'العربية' }}</span>
                        </button>

                        <div class="user-profile-glass">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=random" alt="User" class="avatar-img">
                            <div class="user-info d-none d-lg-block">
                                <span class="user-name">{{ Auth::user()->name ?? 'User' }}</span>
                                <span class="user-role">{{ Auth::user()->role ?? 'Staff' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">


            @include('partials.flash-toast')

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}?v={{ filemtime(public_path('vendor/bootstrap/js/bootstrap.bundle.min.js')) }}"></script>
    
    <!-- Custom JS with cache busting -->
    <script src="{{ asset('js/api.js') }}?v={{ filemtime(public_path('js/api.js')) }}"></script>
    <script src="{{ asset('js/utils.js') }}?v={{ filemtime(public_path('js/utils.js')) }}"></script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
    <script src="{{ asset('js/layout.js') }}?v={{ filemtime(public_path('js/layout.js')) }}"></script>
    <script src="{{ asset('js/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard.js')) }}"></script>
    <script src="{{ asset('js/appointments.js') }}?v={{ filemtime(public_path('js/appointments.js')) }}"></script>
    <script src="{{ asset('js/patients.js') }}?v={{ filemtime(public_path('js/patients.js')) }}"></script>
    <script src="{{ asset('js/doctors.js') }}?v={{ filemtime(public_path('js/doctors.js')) }}"></script>
    
    <!-- Notification Scripts -->
    <script>
        $(document).ready(function() {
            let lastFetchTime = new Date().toISOString();
            let liveFeedInterval = null;
            let isPolling = false;

            function updateBadge(count) {
                if (count > 0) {
                    $('#notificationBadge').text(count).removeClass('d-none');
                } else {
                    $('#notificationBadge').addClass('d-none');
                }
            }

            var audioCtx = null;

            function unlockAudioCtx() {
                if (audioCtx) return;
                try {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    if (audioCtx.state === 'suspended') audioCtx.resume();
                } catch (e) {}
            }

            function playNotificationChime() {
                try {
                    if (!audioCtx || audioCtx.state === 'closed') {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    if (audioCtx.state === 'suspended') audioCtx.resume();
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.frequency.value = 880;
                    osc.type = 'sine';
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.3);
                    return true;
                } catch (e) {
                    console.warn('Chime failed:', e);
                    return false;
                }
            }

            // Expose for console debugging
            window.playNotificationChime = playNotificationChime;
            window.unlockAudioCtx = unlockAudioCtx;

            // Unlock AudioContext on first user interaction (click/touch/keydown)
            $(document).on('click touchstart keydown', unlockAudioCtx);

            function pollLiveFeed() {
                if (isPolling) return;
                isPolling = true;
                var retries = 0;
                function doFetch() {
                $.getJSON("{{ route('notifications.live-feed') }}?since=" + encodeURIComponent(lastFetchTime), function(resp) {
                    updateBadge(resp.count);

                    if (resp.new && resp.new.length > 0) {
                        resp.new.forEach(function(n) {
                            var toastType = n.type === 'system' ? 'info' : 'success';
                            if (window.toast && typeof window.toast.show === 'function') {
                                window.toast.show(n.message, toastType, n.title);
                            }
                            // Trigger clinical board refresh when a notification references an appointment
                            if (typeof window.refreshClinicalBoard === 'function') {
                                if (n.has_appointment) {
                                    window.refreshClinicalBoard();
                                }
                            }
                            @if(auth()->user()->isNurse())
                            if (typeof window.refreshTriageBoard === 'function') {
                                if (n.has_appointment) {
                                    window.refreshTriageBoard();
                                }
                            }
                            @endif
                            @if(auth()->user()->isReceptionist())
                            if (typeof window.refreshReceptionBoard === 'function') {
                                if (n.has_appointment) {
                                    window.refreshReceptionBoard();
                                }
                            }
                            @endif
                        });
                            playNotificationChime();
                            lastFetchTime = new Date().toISOString();
                    }
                }).fail(function() {
                    retries++;
                    if (retries < 3) { setTimeout(doFetch, 2000 * retries); return; }
                }).always(function() { isPolling = false; });
                }
                doFetch();
            }

            // Fetch notifications on dropdown open
            $('#notificationDropdown').on('show.bs.dropdown', function () {
                $('#notificationList').html('<div class="text-center p-4"><div class="spinner-border text-primary spinner-border-sm" role="status"></div></div>');
                $.get("{{ route('notifications.latest') }}", function(data) {
                    $('#notificationList').html(data.html);
                    updateBadge(data.count);
                });
            });

            // Initial fetch + poll every 15 seconds
            pollLiveFeed();
            liveFeedInterval = setInterval(pollLiveFeed, 15000);

            // Clear interval on page unload to prevent memory leaks
            $(window).on('beforeunload', function() {
                if (liveFeedInterval) clearInterval(liveFeedInterval);
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
