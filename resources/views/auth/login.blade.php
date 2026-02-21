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

<body>

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
                        <span class="input-group-text   border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="username" name="username" 
                            value="{{ old('username') }}" required
                            data-i18n-placeholder="usernamePlaceholder" placeholder="Enter your username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label" data-i18n="passwordLabel">Password</label>
                    <div class="input-group">
                        <span class="input-group-text   border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" required
                            data-i18n-placeholder="passwordPlaceholder" placeholder="********">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2" data-i18n="loginBtn">Login</button>
            </form>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        // Translations
        const translations = {
            en: {
                appTitle: 'Clinic System - Login',
                loginTitle: 'Welcome Back',
                loginSubtitle: 'Sign in to access your dashboard',
                usernameLabel: 'USERNAME',
                usernamePlaceholder: 'Enter your username',
                passwordLabel: 'PASSWORD',
                passwordPlaceholder: '********',
                loginBtn: 'Login'
            },
            ar: {
                appTitle: 'نظام العيادة - تسجيل الدخول',
                loginTitle: 'مرحباً بعودتك',
                loginSubtitle: 'سجل دخولك للوصول إلى لوحة التحكم',
                usernameLabel: 'اسم المستخدم',
                usernamePlaceholder: 'أدخل اسم المستخدم',
                passwordLabel: 'كلمة المرور',
                passwordPlaceholder: '********',
                loginBtn: 'تسجيل الدخول'
            }
        };

        function applyTranslations(lang) {
            const t = translations[lang] || translations.en;
            
            // Translate text content
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (t[key]) {
                    if (el.tagName === 'TITLE') {
                        el.textContent = t[key];
                    } else {
                        el.innerHTML = t[key];
                    }
                }
            });
            
            // Translate placeholders
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (t[key]) {
                    el.placeholder = t[key];
                }
            });
        }

        function toggleLanguage() {
            const html = document.documentElement;
            const isArabic = html.lang === 'ar';
            const newLang = isArabic ? 'en' : 'ar';
            
            html.lang = newLang;
            html.dir = newLang === 'ar' ? 'rtl' : 'ltr';
            document.getElementById('langToggleText').textContent = newLang === 'ar' ? 'English' : 'العربية';
            localStorage.setItem('clinic_lang', newLang);
            
            applyTranslations(newLang);
        }
        
        // Apply saved language on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedLang = localStorage.getItem('clinic_lang') || 'en';
            document.documentElement.lang = savedLang;
            document.documentElement.dir = savedLang === 'ar' ? 'rtl' : 'ltr';
            document.getElementById('langToggleText').textContent = savedLang === 'ar' ? 'English' : 'العربية';
            applyTranslations(savedLang);
        });
    </script>
</body>

</html>

