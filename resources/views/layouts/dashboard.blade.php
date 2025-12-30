<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Clinic System</title>
    
    <!-- CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/fontawesome-local.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/davinci.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    @yield('styles')
    
    <!-- jQuery -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar glass-effect">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <i class="fas fa-heartbeat pulse-anim"></i>
                <span class="brand-text" data-i18n="appTitle">Clinic System</span>
            </a>
        </div>

        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-home"></i></div>
                    <span data-i18n="dashboard">Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patients.index') }}" class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-user-injured"></i></div>
                    <span data-i18n="patients">Patients</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-calendar-check"></i></div>
                    <span data-i18n="appointments">Appts</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('doctors.index') }}" class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-user-md"></i></div>
                    <span data-i18n="doctors">Doctors</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <div class="icon-box"><i class="fas fa-cog"></i></div>
                    <span data-i18n="settings">Settings</span>
                </a>
            </li>
        </ul>

        <ul class="nav flex-column mt-auto sidebar-footer">
            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div class="icon-box"><i class="fas fa-sign-out-alt"></i></div>
                    <span data-i18n="logout">Logout</span>
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
                        <h4 class="mb-0 fw-bold header-title" data-i18n="@yield('page-i18n', 'dashboard')">@yield('page-title', 'Dashboard')</h4>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <div class="search-container d-none d-md-flex">
                        <i class="fas fa-search"></i>
                        <input type="text" class="glass-input" data-i18n-placeholder="searchPlaceholder" placeholder="Search...">
                    </div>

                    <div class="header-actions">
                        <button class="btn-icon-glass notification-btn">
                            <i class="far fa-bell"></i>
                            <span class="badge-dot"></span>
                        </button>

                        <button class="btn-icon-glass theme-toggle" id="themeToggle">
                            <i class="fas fa-moon"></i>
                        </button>

                        <button class="lang-toggle-glass" id="langToggle">
                            <span id="langToggleText">Ar</span>
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
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/utils.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/layout.js') }}"></script>
    @yield('scripts')
</body>

</html>
