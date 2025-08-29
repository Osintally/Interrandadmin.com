@extends('layouts.auth2')

@section('title', __('lang_v1.reset_password'))

@section('content')
    <div class="col-md-4 tw-hidden md:tw-block">
    </div>
    <div class="col-md-4 col-sm-8 col-xs-12 tw-px-4 md:tw-px-0">
        <div
            class="tw-p-4 md:tw-p-6 tw-mb-4 tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 tw-ring-gray-200 tw-w-full tw-max-w-md tw-mx-auto">
            <div class="tw-flex tw-flex-col tw-gap-4 tw-p-2 md:tw-p-6">
                <div class="tw-text-center tw-mb-4">
                    <h1 class="tw-text-xl md:tw-text-2xl tw-font-semibold tw-text-[#1e1e1e] tw-mb-2">@lang('lang_v1.reset_password')</h1>
                    <h3 class="tw-text-sm md:tw-text-base tw-font-medium tw-text-gray-500">Enter your new password</h3>
                </div>
                <form method="POST" action="{{ route('password.request') }}">
                    {{ csrf_field() }}

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
                        <label class="tw-dw-form-control">
                            <div class="tw-dw-label">
                                <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-black">@lang('Email')</span>
                            </div>
                            <input id="email" type="email" class="tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 md:tw-h-14 tw-bg-transparent tw-rounded-lg tw-px-3 md:tw-px-4 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium tw-w-full tw-text-sm md:tw-text-base" name="email"
                                value="{{ $email ?? old('email') }}" required autofocus placeholder="@lang('lang_v1.email_address')">

                            @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </label>
                    </div>

                    <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
                        <div class="tw-mb-2">
                            <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-black">@lang('lang_v1.password')</span>
                        </div>
                        <div class="tw-relative tw-w-full">
                            <input id="password" type="password" class="tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 md:tw-h-14 tw-bg-transparent tw-rounded-lg tw-pl-3 md:tw-pl-4 tw-pr-12 md:tw-pr-14 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium tw-w-full tw-text-sm md:tw-text-base" name="password"
                                required placeholder="@lang('lang_v1.password')">
                            <button type="button" class="password-toggle tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-justify-center tw-w-12 md:tw-w-14 tw-h-full tw-rounded-r-lg hover:tw-bg-gray-50 tw-transition-colors tw-z-10">
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

                    <div class="form-group has-feedback {{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                        <div class="tw-mb-2">
                            <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-black">@lang('business.confirm_password')</span>
                        </div>
                        <div class="tw-relative tw-w-full">
                            <input id="password_confirmation" type="password" class="tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 md:tw-h-14 tw-bg-transparent tw-rounded-lg tw-pl-3 md:tw-pl-4 tw-pr-12 md:tw-pr-14 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium tw-w-full tw-text-sm md:tw-text-base"
                                name="password_confirmation" required placeholder="@lang('business.confirm_password')">
                            <button type="button" class="password-toggle tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-justify-center tw-w-12 md:tw-w-14 tw-h-full tw-rounded-r-lg hover:tw-bg-gray-50 tw-transition-colors tw-z-10">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye tw-w-5 tw-h-5 md:tw-w-6 md:tw-h-6 tw-text-gray-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                </svg>
                            </button>
                        </div>
                        @if ($errors->has('password_confirmation'))
                            <span class="help-block tw-text-red-500 tw-text-sm tw-mt-1 tw-block">
                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                            </span>
                        @endif
                    </div>
                    <button type="submit" class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 md:tw-h-14 tw-rounded-xl tw-text-base md:tw-text-lg tw-text-white tw-font-semibold tw-w-full tw-max-w-full tw-mt-4 hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700 tw-transition-all tw-duration-200">
                        @lang('lang_v1.reset_password')</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4 tw-hidden md:tw-block">
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Password visibility toggle functionality
            $('.password-toggle').on('click', function() {
                var passwordInput = $(this).siblings('input[type="password"], input[type="text"]');
                var icon = $(this).find('svg');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.html(`
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" />
                        <path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87" />
                        <path d="M3 3l18 18" />
                    `);
                } else {
                    passwordInput.attr('type', 'password');
                    icon.html(`
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                    `);
                }
            });
        });
    </script>
@endsection

