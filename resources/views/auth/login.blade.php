<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title data-i18n="appTitle">Clinic System</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/fontawesome-local.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
</head>

<body class="bg-light">

    <button class="lang-toggle" id="langToggle">
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

            <div id="loginAlert" class="alert d-none mb-3" role="alert"></div>

            <form id="loginForm">
                <div class="mb-3">
                    <label for="username" class="form-label" data-i18n="usernameLabel">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="fas fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="username" name="username" required
                            data-i18n-placeholder="usernamePlaceholder" placeholder="Enter your username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label" data-i18n="passwordLabel">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" required
                            placeholder="********">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2" id="loginBtn" data-i18n="loginBtn">
                    <span class="btn-text">Login</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </form>

            <div class="text-center mt-3 text-muted small">
                <p class="mb-0">Demo credentials: <strong>admin</strong> / <strong>admin123</strong></p>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/utils.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Handle login form submission
            $('#loginForm').on('submit', async function(e) {
                e.preventDefault();
                
                const $btn = $('#loginBtn');
                const $btnText = $btn.find('.btn-text');
                const $spinner = $btn.find('.spinner-border');
                const $alert = $('#loginAlert');
                
                // Show loading state
                $btn.prop('disabled', true);
                $btnText.text('Logging in...');
                $spinner.removeClass('d-none');
                $alert.addClass('d-none');
                
                const username = $('#username').val().trim();
                const password = $('#password').val();
                
                try {
                    const response = await API.auth.login(username, password);
                    
                    if (response.success) {
                        // Store user info if needed
                        localStorage.setItem('clinic_user', JSON.stringify(response.user));
                        
                        // Redirect to dashboard using the server-provided redirect URL
                        window.location.href = response.redirect || '/dashboard';
                    } else {
                        throw new Error(response.message || 'Login failed');
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    
                    // Show error message
                    $alert.removeClass('d-none alert-success')
                          .addClass('alert-danger')
                          .text(error.message || 'Invalid username or password');
                    
                    // Reset button state
                    $btn.prop('disabled', false);
                    $btnText.text('Login');
                    $spinner.addClass('d-none');
                }
            });
        });
    </script>
</body>

</html>
