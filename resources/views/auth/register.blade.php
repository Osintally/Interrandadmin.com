@extends('layouts.auth')

@section('content')

    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            overflow-x: hidden;
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
            position: relative;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2D1B3D 0%, #4A2C5E 30%, #5E3A6F 60%, #6B4782 100%);
            z-index: 0;
            overflow: hidden;
        }

        .animated-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 30%, rgba(255, 107, 53, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(230, 57, 70, 0.15) 0%, transparent 50%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        /* Floating Blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            mix-blend-mode: screen;
            animation-iteration-count: infinite;
            animation-timing-function: ease-in-out;
        }

        .blob-1 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 107, 53, 0.6), rgba(230, 57, 70, 0.4));
            top: 5%;
            left: 5%;
            animation: float-diagonal 22s infinite;
        }

        .blob-2 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 165, 0, 0.5), rgba(255, 107, 53, 0.4));
            top: 50%;
            right: 8%;
            animation: float-circular 28s infinite;
        }

        .blob-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(230, 57, 70, 0.5), rgba(201, 24, 74, 0.3));
            bottom: 10%;
            left: 50%;
            animation: float-vertical 20s infinite;
        }

        /* Blob Animations */
        @keyframes float-diagonal {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            25% {
                transform: translate(100px, 100px) rotate(90deg);
            }

            50% {
                transform: translate(200px, 0) rotate(180deg);
            }

            75% {
                transform: translate(100px, -100px) rotate(270deg);
            }
        }

        @keyframes float-circular {
            0% {
                transform: translate(0, 0);
            }

            25% {
                transform: translate(80px, -80px);
            }

            50% {
                transform: translate(0, -160px);
            }

            75% {
                transform: translate(-80px, -80px);
            }

            100% {
                transform: translate(0, 0);
            }
        }

        @keyframes float-vertical {

            0%,
            100% {
                transform: translateY(0) scale(1);
            }

            50% {
                transform: translateY(-150px) scale(1.1);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Logo Container */
        .logo-header {
            position: fixed;
            top: 2.5rem;
            left: 3rem;
            z-index: 1000;
            animation: fadeIn 0.8s ease-out;
        }

        .logo-header img {
            width: 220px;
            height: auto;
            filter: drop-shadow(0 8px 24px rgba(255, 107, 53, 0.4)) brightness(1.1);
        }

        /* Top Navigation */
        .top-nav {
            position: fixed;
            top: 0;
            right: 0;
            padding: 2.5rem 3.5rem;
            display: flex;
            gap: 2.5rem;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.8s ease-out 0.6s both;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 1rem;
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
            background: linear-gradient(135deg, #FF6B35, #FFA500);
            transition: width 0.3s ease;
        }

        .nav-link:hover {
            color: #ffffff;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-login-nav {
            padding: 0.875rem 2.25rem;
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 3rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-block;
            backdrop-filter: blur(10px);
        }

        .btn-login-nav:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        /* Registration Card */
        .register-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 2rem;
            padding: 3rem;
            width: 100%;
            max-width: 900px;
            box-shadow:
                0 30px 90px rgba(0, 0, 0, 0.25),
                0 0 1px rgba(230, 57, 70, 0.1),
                inset 0 1px 1px rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideInScale 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s both;
            position: relative;
            z-index: 10;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .register-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .register-header p {
            font-size: 1rem;
            color: #737373;
            font-weight: 500;
        }

        /* Section Headers */
        .section-divider {
            margin: 2.5rem 0 2rem;
            text-align: center;
            position: relative;
        }

        .section-divider h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            display: inline-block;
            padding: 0 1.5rem;
            background: rgba(255, 255, 255, 0.98);
            position: relative;
            z-index: 1;
        }

        .section-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #e5e7eb 20%, #e5e7eb 80%, transparent);
        }

        /* Form Grid */
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-row.single {
            grid-template-columns: 1fr;
        }

        .form-row.triple {
            grid-template-columns: repeat(3, 1fr);
        }

        .form-group {
            width: 100%;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.6rem;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-addon {
            position: absolute;
            left: 1rem;
            z-index: 2;
            color: #6b7280;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            height: 3.5rem;
            padding: 0 1rem 0 3rem;
            border: 2px solid #e5e7eb;
            border-radius: 1rem;
            font-size: 1rem;
            font-weight: 500;
            color: #1a1a1a;
            background: #f9fafb;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-control:hover {
            background: #ffffff;
            border-color: #d1d5db;
        }

        .form-control:focus {
            border-color: #FF6B35;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.08);
            transform: translateY(-1px);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Cpath fill='%236b7280' d='M8 11L3 6h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 3rem;
        }

        /* Submit Button */
        .form-footer {
            margin-top: 2.5rem;
            display: flex;
            justify-content: center;
        }

        .btn-register {
            padding: 1rem 4rem;
            background: linear-gradient(135deg, #E63946 0%, #FF6B35 100%);
            color: white;
            font-size: 1.15rem;
            font-weight: 700;
            border: none;
            border-radius: 1rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(230, 57, 70, 0.25);
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #C9184A 0%, #E63946 100%);
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(230, 57, 70, 0.35);
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .btn-register:active {
            transform: translateY(0);
        }

        /* Login Link */
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #737373;
        }

        .login-link a {
            font-weight: 700;
            color: #FF6B35;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #FF6B35, #FFA500);
            transition: width 0.3s ease;
        }

        .login-link a:hover {
            color: #E63946;
        }

        .login-link a:hover::after {
            width: 100%;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-container {
                padding: 2rem 1rem;
            }

            .register-card {
                padding: 2rem 1.5rem;
                border-radius: 1.5rem;
            }

            .register-header h1 {
                font-size: 2rem;
            }

            .form-row,
            .form-row.triple {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            .logo-header {
                left: 1.5rem;
                top: 1.5rem;
            }

            .logo-header img {
                width: 160px;
            }

            .top-nav {
                padding: 1.5rem;
                gap: 1.5rem;
            }

            .btn-login-nav {
                padding: 0.75rem 1.75rem;
                font-size: 0.9rem;
            }

            .section-divider {
                margin: 2rem 0 1.5rem;
            }

            .blob {
                filter: blur(50px);
                opacity: 0.3;
            }
        }

        @media (max-width: 480px) {
            .register-card {
                padding: 1.75rem 1.25rem;
            }

            .register-header h1 {
                font-size: 1.75rem;
            }

            .form-control {
                height: 3.25rem;
                font-size: 0.95rem;
            }

            .btn-register {
                padding: 1rem 3rem;
                font-size: 1.05rem;
            }
        }

        /* Focus Visible for Accessibility */
        *:focus-visible {
            outline: 2px solid #FF6B35;
            outline-offset: 2px;
        }
    </style>

    <div class="animated-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- Logo Header -->
    <div class="logo-header">
        <img src="{{ asset('images/interrand-logo.png') }}" alt="Interrand Logo">
    </div>

    <!-- Top Navigation -->
    <div class="top-nav">
        <a href="#" class="nav-link">Pricing</a>
        <a href="#" class="nav-link">▶ English</a>
        <a href="{{ route('login') }}" class="btn-login-nav">Login</a>
    </div>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>{{ config('app.name', 'ultimatePOS') }}</h1>
                <p>Register and Get Started in minutes</p>
            </div>

            {!! Form::open(['url' => route('business.postRegister')]) !!}
            {!! Form::token() !!}

            <!-- Business Information Section -->
            <div class="form-row single">
                <div class="form-group">
                    {!! Form::label('name', 'Business Name:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-suitcase"></i>
                        </span>
                        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Business name']) !!}
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    {!! Form::label('start_date', 'Start Date:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        {!! Form::text('start_date', null, ['class' => 'form-control start-date-picker', 'placeholder' => 'Start Date', 'readonly']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('currency', 'Currency:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-money-bill-alt"></i>
                        </span>
                        {!! Form::select('currency', $currencies, '', ['class' => 'form-control', 'placeholder' => 'Select Currency']) !!}
                    </div>
                </div>
            </div>

            <div class="form-row triple">
                <div class="form-group">
                    {!! Form::label('country', 'Country:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-globe"></i>
                        </span>
                        {!! Form::text('country', null, ['class' => 'form-control', 'placeholder' => 'Country']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('state', 'State:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-map-marker"></i>
                        </span>
                        {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => 'State']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('city', 'City:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-map-marker"></i>
                        </span>
                        {!! Form::text('city', null, ['class' => 'form-control', 'placeholder' => 'City']) !!}
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    {!! Form::label('zip_code', 'Zip Code:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-map-marker"></i>
                        </span>
                        {!! Form::text('zip_code', null, ['class' => 'form-control', 'placeholder' => 'Zip/Postal Code']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('landmark', 'Landmark:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-map-marker"></i>
                        </span>
                        {!! Form::text('landmark', null, ['class' => 'form-control', 'placeholder' => 'Landmark']) !!}
                    </div>
                </div>
            </div>

            <!-- Tax Information Section -->
            <div class="section-divider">
                <h3>Tax Information</h3>
            </div>

            <div class="form-row">
                <div class="form-group">
                    {!! Form::label('tax_label_1', 'Tax 1 Name:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::text('tax_label_1', null, ['class' => 'form-control', 'placeholder' => 'GST / VAT / Other']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('tax_number_1', 'Tax 1 No.:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::text('tax_number_1', null, ['class' => 'form-control', 'placeholder' => 'Tax Number']) !!}
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    {!! Form::label('tax_label_2', 'Tax 2 Name:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::text('tax_label_2', null, ['class' => 'form-control', 'placeholder' => 'GST / VAT / Other']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('tax_number_2', 'Tax 2 No.:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::text('tax_number_2', null, ['class' => 'form-control', 'placeholder' => 'Tax Number']) !!}
                    </div>
                </div>
            </div>

            <!-- Owner Information Section -->
            <div class="section-divider">
                <h3>Owner Information</h3>
            </div>

            <div class="form-row triple">
                <div class="form-group">
                    {!! Form::label('surname', 'Surname:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::text('surname', null, ['class' => 'form-control', 'placeholder' => 'Surname']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('first_name', 'First Name:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::text('first_name', null, ['class' => 'form-control', 'placeholder' => 'First Name']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('last_name', 'Last Name:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => 'Last Name']) !!}
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    {!! Form::label('username', 'Username:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Username used for login']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('email', 'Email:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-envelope"></i>
                        </span>
                        {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Email address']) !!}
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    {!! Form::label('password', 'Password:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </span>
                        {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Login Password']) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('confirm_password', 'Confirm Password:', ['class' => 'form-label']) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </span>
                        {!! Form::password('confirm_password', ['class' => 'form-control', 'placeholder' => 'Same as Login Password']) !!}
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="form-footer">
                <button type="submit" class="btn-register">Register</button>
            </div>

            <div class="login-link">
                Already have an account? <a href="{{ route('login') }}">Login Here</a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>

@endsection