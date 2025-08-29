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
    <div class="row tw-justify-center">
        <div class="col-md-4 tw-hidden md:tw-block">
        {{-- Hide demo section to remove admin references --}}
        @if (false && config('app.env') == 'demo')
        
                @component('components.widget', [
                    'class' => 'box-primary',
                    'header' =>
                        '<h4 class="text-center">Demo Shops <small><i> <br/>Demos are for example purpose only, this application <u>can be used in many other similar businesses.</u></i> <br/><b>Click button to login that business</b></small></h4>',
                ])
                    <a href="?demo_type=all_in_one" class="btn btn-app bg-olive demo-login" data-toggle="tooltip"
                        title="Showcases all feature available in the application."
                        data-admin="{{ $demo_types['all_in_one'] }}"> <i class="fas fa-star"></i> All In One</a>

                    <a href="?demo_type=pharmacy" class="btn bg-maroon btn-app demo-login" data-toggle="tooltip"
                        title="Shops with products having expiry dates." data-admin="{{ $demo_types['pharmacy'] }}"><i
                            class="fas fa-medkit"></i>Pharmacy</a>

                    <a href="?demo_type=services" class="btn bg-orange btn-app demo-login" data-toggle="tooltip"
                        title="For all service providers like Web Development, Restaurants, Repairing, Plumber, Salons, Beauty Parlors etc."
                        data-admin="{{ $demo_types['services'] }}"><i class="fas fa-wrench"></i>Multi-Service Center</a>

                    <a href="?demo_type=electronics" class="btn bg-purple btn-app demo-login" data-toggle="tooltip"
                        title="Products having IMEI or Serial number code." data-admin="{{ $demo_types['electronics'] }}"><i
                            class="fas fa-laptop"></i>Electronics & Mobile Shop</a>

                    <a href="?demo_type=super_market" class="btn bg-navy btn-app demo-login" data-toggle="tooltip"
                        title="Super market & Similar kind of shops." data-admin="{{ $demo_types['super_market'] }}"><i
                            class="fas fa-shopping-cart"></i> Super Market</a>

                    <a href="?demo_type=restaurant" class="btn bg-red btn-app demo-login" data-toggle="tooltip"
                        title="Restaurants, Salons and other similar kind of shops."
                        data-admin="{{ $demo_types['restaurant'] }}"><i class="fas fa-utensils"></i> Restaurant</a>
                    <hr>

                    <i class="icon fas fa-plug"></i> Premium optional modules:<br><br>

                    <a href="?demo_type=superadmin" class="btn bg-red-active btn-app demo-login" data-toggle="tooltip"
                        title="SaaS & Superadmin extension Demo" data-admin="{{ $demo_types['superadmin'] }}"><i
                            class="fas fa-university"></i> SaaS / Superadmin</a>

                    <a href="?demo_type=woocommerce" class="btn bg-woocommerce btn-app demo-login" data-toggle="tooltip"
                        title="WooCommerce demo user - Open web shop in minutes!!" style="color:white !important"
                        data-admin="{{ $demo_types['woocommerce'] }}"> <i class="fab fa-wordpress"></i> WooCommerce</a>

                    <a href="?demo_type=essentials" class="btn bg-navy btn-app demo-login" data-toggle="tooltip"
                        title="Essentials & HRM (human resource management) Module Demo" style="color:white !important"
                        data-admin="{{ $demo_types['essentials'] }}">
                        <i class="fas fa-check-circle"></i>
                        Essentials & HRM</a>

                    <a href="?demo_type=manufacturing" class="btn bg-orange btn-app demo-login" data-toggle="tooltip"
                        title="Manufacturing module demo" style="color:white !important"
                        data-admin="{{ $demo_types['manufacturing'] }}">
                        <i class="fas fa-industry"></i>
                        Manufacturing Module</a>

                    <a href="?demo_type=superadmin" class="btn bg-maroon btn-app demo-login" data-toggle="tooltip"
                        title="Project module demo" style="color:white !important"
                        data-admin="{{ $demo_types['superadmin'] }}">
                        <i class="fas fa-project-diagram"></i>
                        Project Module</a>

                    <a href="?demo_type=services" class="btn btn-app demo-login" data-toggle="tooltip"
                        title="Advance repair module demo" style="color:white !important; background-color: #bc8f8f"
                        data-admin="{{ $demo_types['services'] }}">
                        <i class="fas fa-wrench"></i>
                        Advance Repair Module</a>

                    <a href="{{ url('docs') }}" target="_blank" class="btn btn-app" data-toggle="tooltip"
                        title="Advance repair module demo" style="color:white !important; background-color: #2dce89">
                        <i class="fas fa-network-wired"></i>
                        Connector Module / API Documentation</a>
                @endcomponent
            
            
        
    @endif
        </div>
        <div class="col-md-4 col-sm-8 col-xs-12 tw-px-4 md:tw-px-0">
            <div
                class="tw-p-4 md:tw-p-6 tw-mb-4 tw-rounded-2xl tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-ring-1 tw-ring-gray-200 tw-w-full tw-max-w-md tw-mx-auto">
                <div class="tw-flex tw-flex-col tw-gap-4 tw-p-2 md:tw-p-6">
                    <div class="tw-flex tw-items-center tw-flex-col tw-text-center">
                        <h1 class="tw-text-xl md:tw-text-2xl tw-font-semibold tw-text-[#1e1e1e] tw-mb-2">
                            Welcome Back
                        </h1>
                        <h2 class="tw-text-sm md:tw-text-base tw-font-medium tw-text-gray-500">
                            Sign in to
                            @if(config('app.name') == 'admin' || config('app.name') == 'Admin' || config('app.name') == 'ADMIN')
                                your account
                            @else
                                {{ config('app.name', 'ultimatePOS') }}
                            @endif
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('login') }}" id="login-form">
                        {{ csrf_field() }}
                        <div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
                            <label class="tw-dw-form-control">
                                <div class="tw-dw-label">
                                    <span
                                        class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-black">Username</span>
                                </div>

                                <input
                                    class="tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 md:tw-h-14 tw-bg-transparent tw-rounded-lg tw-px-3 md:tw-px-4 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium tw-w-full tw-text-sm md:tw-text-base"
                                    name="username" required autofocus placeholder="Username"
                                    data-last-active-input="" id="username" type="text" name="username"
                                    value="{{ $username }}" />
                                @if ($errors->has('username'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                @endif
                            </label>
                        </div>

                        <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
                            <div class="tw-mb-2">
                                <div class="tw-flex tw-justify-between tw-items-center">
                                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-black">Password</span>
                                    @if (config('app.env') != 'demo')
                                        <a href="{{ route('password.request') }}"
                                            class="tw-text-xs md:tw-text-sm tw-font-medium tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-inline-block tw-text-transparent tw-bg-clip-text hover:tw-text-[#467BF5]"
                                            tabindex="-1">Forgot Password?</a>
                                    @endif
                                </div>
                            </div>

                            <div class="tw-relative tw-w-full">
                                <input
                                    class="tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 md:tw-h-14 tw-bg-transparent tw-rounded-lg tw-pl-3 md:tw-pl-4 tw-pr-12 md:tw-pr-14 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium tw-w-full tw-text-sm md:tw-text-base"
                                    id="password" type="password" name="password" value="{{ $password }}" required
                                    placeholder="Password" />
                                <button type="button" id="show_hide_icon" class="show_hide_icon tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-justify-center tw-w-12 md:tw-w-14 tw-h-full tw-rounded-r-lg hover:tw-bg-gray-50 tw-transition-colors tw-z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye tw-w-5 tw-h-5 md:tw-w-6 md:tw-h-6 tw-text-gray-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                    </svg>
                                </button>
                            </div>

                            @if ($errors->has('password'))
                                <span class="help-block tw-text-red-500 tw-text-sm tw-mt-1 tw-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>


                        <div class="tw-flex tw-items-center tw-gap-3 tw-my-4">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                                id="remember_me" class="tw-w-4 tw-h-4 tw-text-blue-600 tw-bg-gray-100 tw-border-gray-300 tw-rounded focus:tw-ring-blue-500 focus:tw-ring-2">
                            <label for="remember_me" class="tw-text-sm md:tw-text-base tw-font-medium tw-text-black tw-cursor-pointer tw-select-none">
                                Remember Me
                            </label>
                        </div>
                        @if(config('constants.enable_recaptcha'))
                        <div class="tw-w-full tw-my-4">
                            <div class="form-group">
                                <div class="g-recaptcha tw-flex tw-justify-center" data-sitekey="{{ config('constants.google_recaptcha_key') }}"></div>
                                    @if ($errors->has('g-recaptcha-response'))
                                        <span class="text-danger tw-text-sm tw-block tw-text-center tw-mt-2">{{ $errors->first('g-recaptcha-response') }}</span>
                                    @endif
                            </div>
                        </div>
                        @endif
                        <button type="submit"
                            class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 md:tw-h-14 tw-rounded-xl tw-text-base md:tw-text-lg tw-text-white tw-font-semibold tw-w-full tw-max-w-full tw-mt-4 hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700 tw-transition-all tw-duration-200">
                            Login
                        </button>
                    </form>

                    <div class="tw-flex tw-items-center tw-flex-col">
                        <!-- Register Url -->

                        @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                            <!-- Register Url -->
                            @if (config('constants.allow_registration'))
                                <a href="{{ route('business.getRegister') }}@if (!empty(request()->lang)) {{ '?lang=' . request()->lang }} @endif"
                                    class="tw-text-sm tw-font-medium tw-text-gray-500 hover:tw-text-gray-500 tw-mt-2">Not yet registered?
                                    <span
                                        class="tw-text-sm tw-font-medium tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-inline-block tw-text-transparent tw-bg-clip-text hover:tw-text-[#467BF5] hover:tw-underline">Register Now</span></a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 tw-hidden md:tw-block"></div>
    </div>

@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#show_hide_icon').off('click');
            $('.change_lang').click(function() {
                window.location = "{{ route('login') }}?lang=" + $(this).attr('value');
            });
            $('a.demo-login').click(function(e) {
                e.preventDefault();
                $('#username').val('demo');
                $('#password').val("{{ $password }}");
                $('form#login-form').submit();
            });

            $('#show_hide_icon').on('click', function(e) {
            e.preventDefault();
            const passwordInput = $('#password');

            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                $('#show_hide_icon').html('<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off tw-w-6" viewBox="0 0 24 24" stroke-width="1.5" stroke="#000000" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>');
            }
            else if (passwordInput.attr('type') === 'text') {
                passwordInput.attr('type', 'password');
                $('#show_hide_icon').html('<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye tw-w-6" viewBox="0 0 24 24" stroke-width="1.5" stroke="#000000" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>');
            }
        });
        })
    </script>
@endsection
