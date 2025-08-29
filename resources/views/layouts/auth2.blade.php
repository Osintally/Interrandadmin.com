<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @if(config('app.name') == 'admin' || config('app.name') == 'Admin' || config('app.name') == 'ADMIN')
            POS - @yield('title')
        @else
            {{ config('app.name', 'POS') }} - @yield('title')
        @endif
    </title>

    @include('layouts.partials.css')

    @include('layouts.partials.extracss_auth')

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src='https://www.google.com/recaptcha/api.js'></script>

</head>

<body class="pace-done" data-new-gr-c-s-check-loaded="14.1172.0" data-gr-ext-installed="" cz-shortcut-listen="true">
    @inject('request', 'Illuminate\Http\Request')
    @if (session('status') && session('status.success'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
            data-msg="{{ session('status.msg') }}">
    @endif
    <div class="container-fluid tw-min-h-screen">
        <div class="row eq-height-row tw-min-h-screen">
            <div class="col-md-12 col-sm-12 col-xs-12 right-col tw-pt-4 md:tw-pt-20 tw-pb-10 tw-px-2 md:tw-px-5">
                <div class="row">
                    {{-- Hide header navigation on mobile for cleaner login/register experience --}}
                    @if(!isset($hide_header_nav) || !$hide_header_nav)
                        <div class="tw-absolute tw-top-2 md:tw-top-5 tw-left-4 md:tw-left-8 tw-flex tw-items-center tw-gap-4 tw-hidden md:tw-flex"
                            style="text-align: left">
                            <a href="{{ url('/') }}">
                                <div
                                    class="lg:tw-w-16 md:tw-h-16 tw-w-12 tw-h-12 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-overflow-hidden tw-p-0.5 tw-mb-4">
                                    <img src="{{ asset('img/logo-small.png')}}" alt="lock" class="tw-object-fill" />
                                </div>
                            </a>
                            @if(config('constants.SHOW_REPAIR_STATUS_LOGIN_SCREEN') && Route::has('repair-status'))
                                <a class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white"
                                    href="{{ action([\Modules\Repair\Http\Controllers\CustomerRepairStatusController::class, 'index']) }}">
                                    @lang('repair::lang.repair_status')
                                </a>
                            @endif

                            @if(Route::has('member_scanner'))
                                <a class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white"
                                    href="{{ action([\Modules\Gym\Http\Controllers\MemberController::class, 'member_scanner']) }}">
                                    @lang('gym::lang.gym_member_profile')
                                </a>
                            @endif
                        </div>

                        <div class="tw-absolute tw-top-5 md:tw-top-8 tw-right-5 md:tw-right-10 tw-flex tw-items-center tw-gap-4 tw-hidden md:tw-flex"
                            style="text-align: left">
                            @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                                <!-- Register Url -->
                                @if (config('constants.allow_registration'))
                                <div class="tw-border-2 tw-border-white tw-rounded-full tw-h-10 md:tw-h-12 tw-w-24 tw-flex tw-items-center tw-justify-center">
                                 <a href="{{ route('business.getRegister')}}@if(!empty(request()->lang)){{'?lang='.request()->lang}}@endif"
                                        class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white">
                                        {{ __('business.register') }}</a>
                                </div>

                                    <!-- pricing url -->
                                    @if (Route::has('pricing') && config('app.env') != 'demo' && $request->segment(1) != 'pricing')
                                        &nbsp; <a class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white"
                                            href="{{ url('/pricing') }}">Pricing</a>
                                    @endif
                                @endif
                            @endif
                            @if ($request->segment(1) != 'login')
                                <a class="tw-text-white tw-font-medium tw-text-sm md:tw-text-base hover:tw-text-white"
                                    href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'login'])}}@if(!empty(request()->lang)){{'?lang='.request()->lang}}@endif">{{ __('business.sign_in') }}</a>
                            @endif
                            @include('layouts.partials.language_btn')
                        </div>
                    @endif

                    {{-- Mobile-only app logo/title and navigation --}}
                    <div class="tw-flex md:tw-hidden tw-justify-between tw-items-center tw-w-full tw-pt-4 tw-pb-2 tw-px-4">
                        {{-- Back to login button for register and forgot password pages --}}
                        @if($request->segment(1) == 'business' && $request->segment(2) == 'register')
                            <a href="{{ route('login') }}" class="tw-flex tw-items-center tw-text-white hover:tw-text-gray-200 tw-transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-6 tw-h-6 tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span class="tw-text-sm tw-font-medium">Login</span>
                            </a>
                        @elseif($request->segment(1) == 'password')
                            <a href="{{ route('login') }}" class="tw-flex tw-items-center tw-text-white hover:tw-text-gray-200 tw-transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-6 tw-h-6 tw-mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span class="tw-text-sm tw-font-medium">Login</span>
                            </a>
                        @else
                            <div></div>
                        @endif

                        {{-- App title --}}
                        <h1 class="tw-text-white tw-text-xl tw-font-semibold tw-text-center tw-flex-1">
                            @if(config('app.name') == 'admin' || config('app.name') == 'Admin' || config('app.name') == 'ADMIN')
                                POS
                            @else
                                {{ config('app.name', 'POS') }}
                            @endif
                        </h1>

                        {{-- Register button for login page --}}
                        @if($request->segment(1) == 'login' && config('constants.allow_registration'))
                            <a href="{{ route('business.getRegister') }}@if(!empty(request()->lang)){{'?lang='.request()->lang}}@endif" class="tw-flex tw-items-center tw-text-white hover:tw-text-gray-200 tw-transition-colors">
                                <span class="tw-text-sm tw-font-medium tw-mr-2">Register</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-6 tw-h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @else
                            <div></div>
                        @endif
                    </div>

                    <div class="col-md-10 col-xs-8" style="text-align: right;">

                    </div>
                </div>
                @yield('content')
            </div>
        </div>
    </div>


    @include('layouts.partials.javascripts')

    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>

    @yield('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2_register').select2();

            // $('input').iCheck({
            //     checkboxClass: 'icheckbox_square-blue',
            //     radioClass: 'iradio_square-blue',
            //     increaseArea: '20%' // optional
            // });
        });
    </script>
    <style>
        .wizard>.content {
            background-color: white !important;
        }

        /* Password visibility button improvements */
        .show_hide_icon, .password-toggle {
            z-index: 10;
            cursor: pointer;
        }

        .show_hide_icon:hover, .password-toggle:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        /* Checkbox styling improvements */
        input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            background-color: #ffffff;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }

        input[type="checkbox"]:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            left: 2px;
            top: -1px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        input[type="checkbox"]:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }

        /* Mobile responsive improvements for auth pages */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 0 !important;
            }

            .right-col {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }

            /* Form improvements for mobile */
            .form-control {
                font-size: 16px !important; /* Prevents zoom on iOS */
                height: 48px !important;
                padding: 12px 16px !important;
            }

            .input-group-addon {
                padding: 12px 16px !important;
                min-width: 48px !important;
            }

            /* Button improvements */
            .btn {
                min-height: 48px !important;
                font-size: 16px !important;
                padding: 12px 24px !important;
            }

            /* Mobile checkbox sizing */
            input[type="checkbox"] {
                width: 20px !important;
                height: 20px !important;
                min-height: 20px !important;
            }

            /* Wizard steps for register form */
            .wizard .steps ul li {
                width: 100% !important;
                margin-bottom: 8px !important;
            }

            .wizard .steps ul li a {
                padding: 12px 8px !important;
                font-size: 14px !important;
            }

            /* Form fieldsets */
            fieldset {
                margin-bottom: 20px !important;
                padding: 16px 8px !important;
            }

            legend {
                font-size: 18px !important;
                margin-bottom: 16px !important;
            }

            /* Column adjustments for mobile */
            .col-md-6, .col-md-4, .col-md-3 {
                margin-bottom: 16px !important;
            }

            /* Select2 dropdown improvements */
            .select2-container .select2-selection--single {
                height: 48px !important;
                line-height: 48px !important;
            }

            .select2-container .select2-selection--single .select2-selection__rendered {
                padding-left: 16px !important;
                padding-right: 16px !important;
                line-height: 48px !important;
            }

            .select2-container .select2-selection--single .select2-selection__arrow {
                height: 48px !important;
                right: 16px !important;
            }
        }

        /* Tablet responsive improvements */
        @media (min-width: 769px) and (max-width: 1024px) {
            .right-col {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }
        }
    </style>
</body>

</html>
