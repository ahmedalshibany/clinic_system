<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title data-i18n="appTitle">Clinic System - Login</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/fontawesome-local.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body class="bg-light">

    <button class="lang-toggle" id="langToggle" onclick="toggleLanguage()">
        <i class="fas fa-globe"></i>
        <span id="langToggleText">العربية</span>
    </button>

    <div class="login-container">
        <div class="card login-card fade-in">
            <div class="text-center mb-4">
                <i class="fas fa-heartbeat fa-3x" style="color: var(--secondary);"></i>
                <h2 class="mt-3" data-i18n="loginTitle">Welcome Back</h2>
                <p class="text-muted" data-i18n="loginSubtitle">Sign in to access your dashboard</p>
            </div>

            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('auth.attempt') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label" data-i18n="usernameLabel">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="username" name="username" 
                            value="{{ old('username') }}" required
                            data-i18n-placeholder="usernamePlaceholder" placeholder="Enter your username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label" data-i18n="passwordLabel">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" required
                            placeholder="********">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2" data-i18n="loginBtn">Login</button>
            </form>

            <div class="text-center mt-3 text-muted small">
                <p class="mb-0">Demo: <strong>admin</strong> / <strong>admin123</strong></p>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function toggleLanguage() {
            const html = document.documentElement;
            const isArabic = html.lang === 'ar';
            html.lang = isArabic ? 'en' : 'ar';
            html.dir = isArabic ? 'ltr' : 'rtl';
            document.getElementById('langToggleText').textContent = isArabic ? 'العربية' : 'English';
            localStorage.setItem('clinic_lang', html.lang);
        }
        
        // Apply saved language
        const savedLang = localStorage.getItem('clinic_lang');
        if (savedLang) {
            document.documentElement.lang = savedLang;
            document.documentElement.dir = savedLang === 'ar' ? 'rtl' : 'ltr';
            document.getElementById('langToggleText').textContent = savedLang === 'ar' ? 'English' : 'العربية';
        }
    </script>
</body>

</html>
