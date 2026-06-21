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
    <!-- Greeting Init: set greeting before render -->
    <script>!function(){var g=document.getElementById('dashboard-greeting'),h=(new Date).getHours(),k='goodEvening',i='fa-moon';if(h>=5&&h<12){k='goodMorning';i='fa-sun'}else if(h>=12&&h<17){k='goodAfternoon';i='fa-cloud'}else if((h>=17&&h<=23)||(h>=0&&h<=4)){k='goodEvening';i='fa-moon'}var t=document.getElementById('greeting-icon');if(t)t.className='fas '+i;if(g)g.textContent=k}();</script>
    
    <!-- CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}?v={{ filemtime(public_path('vendor/bootstrap/css/bootstrap.min.css')) }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}?v={{ filemtime(public_path('vendor/fontawesome/css/all.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/fontawesome-local.css') }}?v={{ filemtime(public_path('vendor/fontawesome/css/fontawesome-local.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
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
            <li class="nav-item">
                <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.index') || request()->routeIs('appointments.show') || request()->routeIs('appointments.create') || request()->routeIs('appointments.edit') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-calendar-check"></i></div>
                    <span data-i18n="appointments">{{ __('messages.appointments') }}</span>
                </a>
            </li>
            @unless(auth()->user()->hasRole('nurse'))
            <li class="nav-item">
                <a href="{{ route('appointments.calendar') }}" class="nav-link {{ request()->routeIs('appointments.calendar') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-calendar-alt"></i></div>
                    <span data-i18n="calendar">{{ __('messages.calendar') }}</span>
                </a>
            </li>
            @endunless
            @unless(auth()->user()->hasRole('nurse'))
            <li class="nav-item">
                <a href="{{ route('doctors.index') }}" class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-user-md"></i></div>
                    <span data-i18n="doctors">{{ __('messages.doctors') }}</span>
                </a>
            </li>
            @endunless
            @unless(auth()->user()->hasRole('nurse'))
            <li class="nav-item">
                <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-briefcase-medical"></i></div>
                    <span data-i18n="services">{{ __('messages.services') }}</span>
                </a>
            </li>
            @endunless
            @if(in_array(auth()->user()->role, ['admin', 'doctor']))
            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-chart-line"></i></div>
                    <span data-i18n="reports">{{ __('messages.reports') }}</span>
                </a>
            </li>
            @endif
            @if(in_array(auth()->user()->role, ['admin', 'doctor', 'receptionist']))
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
                        <input type="text" class="glass-input" data-i18n-placeholder="searchPlaceholder" placeholder="Search...">
                    </div>

                    <div class="header-actions">
                        <!-- Notification Dropdown -->
                        <div class="dropdown">
                            <button class="btn-icon-glass notification-btn position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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

                        <button class="btn-icon-glass theme-toggle" id="themeToggle">
                            <i class="fas fa-moon"></i>
                        </button>

                        <button class="lang-toggle-glass" id="langToggle">
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
            // Fetch unread count periodically
            function fetchUnreadCount() {
                $.get("{{ route('notifications.unread-count') }}", function(data) {
                    if (data.count > 0) {
                        $('#notificationBadge').text(data.count).removeClass('d-none');
                    } else {
                        $('#notificationBadge').addClass('d-none');
                    }
                });
            }

            // Fetch notifications on dropdown open
            $('#notificationDropdown').on('show.bs.dropdown', function () {
                $('#notificationList').html('<div class="text-center p-4"><div class="spinner-border text-primary spinner-border-sm" role="status"></div></div>');
                $.get("{{ route('notifications.latest') }}", function(data) {
                    $('#notificationList').html(data.html);
                    // Update badge as well
                    if (data.count > 0) {
                        $('#notificationBadge').text(data.count).removeClass('d-none');
                    } else {
                        $('#notificationBadge').addClass('d-none');
                    }
                });
            });

            // Initial Count Fetch
            fetchUnreadCount();
            // Poll every 30 seconds
            setInterval(fetchUnreadCount, 30000);
        });
    </script>
    @yield('scripts')
</body>
</html>
