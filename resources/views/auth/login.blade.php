@extends('layouts.auth2')
@section('title', __('lang_v1.login'))
@inject('request', 'Illuminate\Http\Request')

@section('content')
@php
    $username = old('username');
    $password = null;
    if (config('app.env') == 'demo') {
        $username = 'demo';
        $password = '123456';
    }
@endphp

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* Animated Background */
    .auth-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background: linear-gradient(135deg, #1a0a2e 0%, #3d1e6d 30%, #5b2a86 60%, #7a3d99 100%);
        overflow: hidden;
        padding: 2rem;
    }

    .auth-wrapper::before {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(247, 113, 23, 0.3) 0%, transparent 70%);
        border-radius: 50%;
        top: -150px;
        left: -150px;
        animation: pulse 8s ease-in-out infinite;
    }

    .auth-wrapper::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 140, 66, 0.25) 0%, transparent 70%);
        border-radius: 50%;
        bottom: -100px;
        right: -100px;
        animation: pulse 8s ease-in-out infinite reverse;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.7;
            transform: scale(1.1);
        }
    }

    /* Floating decorative shapes */
    .shape {
        position: absolute;
        border-radius: 50%;
        background: rgba(247, 113, 23, 0.15);
        animation: float 20s ease-in-out infinite;
    }

    .shape-1 {
        width: 250px;
        height: 250px;
        top: 20%;
        right: 15%;
        animation-delay: 0s;
    }

    .shape-2 {
        width: 180px;
        height: 180px;
        bottom: 25%;
        left: 8%;
        background: rgba(255, 165, 0, 0.12);
        animation-delay: 2s;
    }

    .shape-3 {
        width: 200px;
        height: 200px;
        top: 60%;
        right: 25%;
        background: rgba(255, 140, 66, 0.1);
        animation-delay: 4s;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        33% {
            transform: translateY(-30px) rotate(120deg);
        }
        66% {
            transform: translateY(15px) rotate(240deg);
        }
    }

    /* Pill shapes */
    .pill {
        position: absolute;
        background: rgba(247, 113, 23, 0.08);
        border-radius: 100px;
    }

    .pill-1 {
        width: 120px;
        height: 300px;
        top: 10%;
        left: 28%;
        transform: rotate(30deg);
        animation: sway 15s ease-in-out infinite;
    }

    .pill-2 {
        width: 100px;
        height: 250px;
        bottom: 15%;
        right: 20%;
        transform: rotate(-20deg);
        animation: sway 18s ease-in-out infinite reverse;
    }

    @keyframes sway {
        0%, 100% {
            transform: translateX(0) rotate(30deg);
        }
        50% {
            transform: translateX(30px) rotate(40deg);
        }
    }

    /* Logo inside container */
    .logo-container {
        margin-bottom: 3rem;
        animation: fadeInDown 0.8s ease-out 0.5s both;
    }

    .logo-container img {
        height: 70px;
        width: auto;
        filter: drop-shadow(0 4px 12px rgba(247, 113, 23, 0.4));
        transition: transform 0.3s ease;
    }

    .logo-container img:hover {
        transform: scale(1.05);
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Top Navigation */
    .top-nav {
        position: absolute;
        top: 2.5rem;
        right: 3rem;
        display: flex;
        gap: 2rem;
        align-items: center;
        z-index: 100;
        animation: fadeInDown 0.8s ease-out 0.2s both;
    }

    .nav-link {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 0;
        height: 2px;
        background: #f77117;
        transition: width 0.3s ease;
    }

    .nav-link:hover {
        color: #ffffff;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .btn-register {
        padding: 0.75rem 2rem;
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 25px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-register:hover {
        background: rgba(247, 113, 23, 0.2);
        border-color: #f77117;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(247, 113, 23, 0.3);
    }

    /* Unified Glassmorphic Container */
    .auth-container {
        display: flex;
        max-width: 1200px;
        width: 100%;
        background: rgba(45, 20, 70, 0.6);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        position: relative;
        z-index: 10;
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: slideIn 0.8s ease-out 0.4s both;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(30px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Left Panel - Welcome Section */
    .welcome-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 4rem 3.5rem;
        color: white;
    }

    .welcome-heading {
        font-size: 3.5rem;
        font-weight: 700;
        line-height: 1.15;
        margin-bottom: 1.5rem;
        color: #ffffff;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        animation: fadeInUp 0.8s ease-out 0.6s both;
    }

    .welcome-divider {
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #f77117, #ffa500);
        margin-bottom: 2rem;
        border-radius: 2px;
        animation: fadeInUp 0.8s ease-out 0.8s both;
    }

    .welcome-text {
        font-size: 1rem;
        line-height: 1.7;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 2.5rem;
        animation: fadeInUp 0.8s ease-out 1s both;
    }

    .btn-learn-more {
        padding: 0.875rem 2.25rem;
        background: linear-gradient(90deg, #f77117 0%, #ff8c42 100%);
        color: white;
        border: none;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(247, 113, 23, 0.3);
        animation: fadeInUp 0.8s ease-out 1.2s both;
        display: inline-block;
        align-self: flex-start;
    }

    .btn-learn-more:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(247, 113, 23, 0.4);
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Right Panel - Login Form with Darker Overlay */
    .login-section {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3.5rem 3rem;
        background: rgba(70, 40, 100, 0.5);
        backdrop-filter: blur(5px);
    }

    .login-form-wrapper {
        width: 100%;
        max-width: 450px;
    }

    .login-form-wrapper h1 {
        font-size: 2.25rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 0.5rem;
        text-align: center;
        animation: fadeInUp 0.8s ease-out 0.8s both;
    }

    .login-form-wrapper h2 {
        font-size: 1rem;
        font-weight: 400;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 2.5rem;
        text-align: center;
        animation: fadeInUp 0.8s ease-out 1s both;
    }

    /* Form Styling - Integrated with Dark Overlay */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-size: 0.95rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.95);
        margin-bottom: 0.5rem;
    }

    .form-input {
        width: 100%;
        padding: 1rem 1.25rem;
        border: none;
        border-radius: 25px;
        font-size: 1rem;
        color: #ffffff;
        background: rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
        outline: none;
    }

    .form-input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-input:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .form-input:focus {
        background: rgba(255, 255, 255, 0.2);
        box-shadow: 0 0 0 3px rgba(247, 113, 23, 0.3);
    }

    .password-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.6);
        transition: color 0.3s ease;
    }

    .password-toggle:hover {
        color: #f77117;
    }

    .forgot-password {
        font-size: 0.9rem;
        font-weight: 600;
        color: #f77117;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .forgot-password:hover {
        color: #ff8c42;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 1.5rem 0;
    }

    .remember-me input[type="checkbox"] {
        width: 1.2rem;
        height: 1.2rem;
        border-radius: 4px;
        cursor: pointer;
        accent-color: #f77117;
    }

    .remember-me label {
        font-size: 0.95rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.85);
        cursor: pointer;
    }

    .btn-login {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #f77117 0%, #ff4242 100%);
        color: white;
        font-size: 1.1rem;
        font-weight: 700;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1.5rem;
        box-shadow: 0 8px 20px rgba(247, 113, 23, 0.4);
        position: relative;
        overflow: hidden;
    }

    .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.6s ease;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(247, 113, 23, 0.5);
    }

    .btn-login:hover::before {
        left: 100%;
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .register-link {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .register-link a {
        font-weight: 700;
        color: #f77117;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .register-link a:hover {
        color: #ff8c42;
    }

    /* Social Icons */
    .social-icons {
        display: flex;
        justify-content: center;
        gap: 1.25rem;
        margin-top: 1.75rem;
    }

    .social-icons a {
        color: rgba(255, 255, 255, 0.8);
        font-size: 1.2rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .social-icons a:hover {
        color: #f77117;
        transform: translateY(-3px);
    }

    /* Error Messages */
    .help-block {
        color: #ffc4c4;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    .has-error .form-input {
        border: 2px solid #ff6b6b;
        background: rgba(255, 107, 107, 0.1);
    }

    .has-error .form-input:focus {
        box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.3);
    }

    /* reCAPTCHA */
    .recaptcha-wrapper {
        display: flex;
        justify-content: center;
        margin: 1.5rem 0;
    }

    /* Responsive Design */    # show current remotes
    git remote -v
    
    # set the correct HTTPS URL
    git remote set-url ignite https://github.com/ignitepack/igniteerp.git
    
    # OR set SSH URL (if you prefer)
    # git remote set-url ignite git@github.com:ignitepack/igniteerp.git
    
    # verify and fetch
    git remote -v
    git fetch ignite
    git remote show ignite
    @media (max-width: 1024px) {
        .auth-container {
            flex-direction: column;
            max-width: 600px;
        }

        .welcome-section {
            padding: 3rem 2.5rem;
        }

        .login-section {
            padding: 3rem 2.5rem;
        }

        .welcome-heading {
            font-size: 3rem;
        }
    }

    @media (max-width: 768px) {
        .logo-container {
            top: 1.5rem;
            left: 1.5rem;
        }

        .logo-container img {
            height: 40px;
        }

        .top-nav {
            top: 1.5rem;
            right: 1.5rem;
            gap: 1rem;
        }

        .nav-link {
            font-size: 0.85rem;
        }

        .btn-register {
            padding: 0.6rem 1.5rem;
            font-size: 0.85rem;
        }

        .auth-wrapper {
            padding: 1rem;
        }

        .welcome-section {
            padding: 2.5rem 2rem;
        }

        .login-section {
            padding: 2.5rem 2rem;
        }

        .welcome-heading {
            font-size: 2.5rem;
        }

        .login-form-wrapper h1 {
            font-size: 2rem;
        }

        .shape, .pill {
            opacity: 0.5;
        }
    }

    @media (max-width: 480px) {
        .welcome-section {
            padding: 2rem 1.5rem;
        }

        .login-section {
            padding: 2rem 1.5rem;
        }

        .welcome-heading {
            font-size: 2rem;
        }

        .welcome-divider {
            width: 60px;
            height: 3px;
        }

        .login-form-wrapper h1 {
            font-size: 1.75rem;
        }

        .form-input {
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
        }

        .btn-login {
            padding: 0.875rem;
            font-size: 1rem;
        }
    }

    /* Accessibility */
    *:focus-visible {
        outline: 2px solid #f77117;
        outline-offset: 2px;
    }

    ::selection {
        background: rgba(247, 113, 23, 0.3);
        color: #ffffff;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    ::-webkit-scrollbar-thumb {
        background: #f77117;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #d65d0f;
    }
</style>

<div class="auth-wrapper">
    <!-- Decorative shapes -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="pill pill-1"></div>
    <div class="pill pill-2"></div>

    <!-- Top Navigation -->
    <div class="top-nav">
        <a href="#" class="nav-link">Pricing</a>
        <a href="#" class="nav-link">▶ English</a>
        @if (config('constants.allow_registration'))
            <a href="{{ route('business.getRegister') }}@if (!empty(request()->lang)) {{ '?lang=' . request()->lang }} @endif"
                class="btn-register">Register</a>
        @endif
    </div>

    <!-- Unified Glassmorphic Container -->
    <div class="auth-container">
        <!-- Left Panel - Welcome Section -->
        <div class="welcome-section">
            <!-- Logo inside container -->
            <div class="logo-container">
                <img src="{{ asset('images/interrandlogo.png') }}" alt="Interrand Logo">
            </div>
            
            <h1 class="welcome-heading">Welcome!<br>to InterrandAdmin</h1>
            <div class="welcome-divider"></div>
            <p class="welcome-text">
                Powered by IgniteERP
            </p>
        </div>

        <!-- Right Panel - Login Form with Dark Overlay -->
        <div class="login-section">
            <div class="login-form-wrapper">
                <h1>Welcome Back</h1>
                <h2>Sign in to your account</h2>

                <form method="POST" action="{{ route('login') }}" id="login-form">
                    {{ csrf_field() }}

                    <!-- Username Field -->
                    <div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
                        <label class="form-label">User Name</label>
                        <input type="text" name="username" id="username" class="form-input" 
                               value="{{ $username }}" placeholder="TechTree" required autofocus>
                        @if ($errors->has('username'))
                            <span class="help-block">
                                <strong>{{ $errors->first('username') }}</strong>
                            </span>
                        @endif
                    </div>

                    <!-- Password Field -->
                    <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label class="form-label" style="margin: 0;">Password</label>
                            @if (config('app.env') != 'demo')
                                <a href="{{ route('password.request') }}" class="forgot-password">Forgot Password?</a>
                            @endif
                        </div>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-input" 
                                   value="{{ $password }}" placeholder="••••••••" required>
                            <button type="button" id="show_hide_icon" class="password-toggle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" 
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        @if ($errors->has('password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>

                    <!-- Remember Me -->
                    <div class="remember-me">
                        <input type="checkbox" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember_me">Remember Me</label>
                    </div>

                    <!-- reCAPTCHA -->
                    @if(config('constants.enable_recaptcha'))
                        <div class="recaptcha-wrapper">
                            <div class="g-recaptcha" data-sitekey="{{ config('constants.google_recaptcha_key') }}"></div>
                        </div>
                        @if ($errors->has('g-recaptcha-response'))
                            <span class="help-block" style="text-align: center; display: block;">
                                {{ $errors->first('g-recaptcha-response') }}
                            </span>
                        @endif
                    @endif

                    <!-- Login Button -->
                    <button type="submit" class="btn-login">Submit</button>

                    <!-- Register Link -->
                    @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                        @if (config('constants.allow_registration'))
                            <div class="register-link">
                                Not yet registered?
                                <a href="{{ route('business.getRegister') }}@if (!empty(request()->lang)) {{ '?lang=' . request()->lang }} @endif">
                                    Register Now
                                </a>
                            </div>
                        @endif
                    @endif

                    <!-- Social Icons -->
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest-p"></i></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#show_hide_icon').off('click');

            $('.change_lang').click(function () {
                window.location = "{{ route('login') }}?lang=" + $(this).attr('value');
            });

            $('a.demo-login').click(function (e) {
                e.preventDefault();
                $('#username').val('demo');
                $('#password').val("{{ $password }}");
                $('form#login-form').submit();
            });

            $('#show_hide_icon').on('click', function (e) {
                e.preventDefault();
                const passwordInput = $('#password');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    $(this).html('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>');
                } else {
                    passwordInput.attr('type', 'password');
                    $(this).html('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>');
                }
            });
        });
    </script>
@endsection