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
                    <h3 class="tw-text-sm md:tw-text-base tw-font-medium tw-text-gray-500">@lang('lang_v1.send_password_reset_link')</h3>
                </div>

                @if (session('status') && is_string(session('status')))
                    <div class="alert alert-info" role="alert">{{ session('status') }}</div>
                @endif


                <form method="POST" action="{{ route('password.email') }}">
                    {{ csrf_field() }}
                    <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
                        <label class="tw-dw-form-control">
                            <div class="tw-dw-label">
                                <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-black">@lang('Email')</span>
                            </div>
                                <input id="email" type="email" class="tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 md:tw-h-14 tw-bg-transparent tw-rounded-lg tw-px-3 md:tw-px-4 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium tw-w-full tw-text-sm md:tw-text-base" name="email" value="{{ old('email') }}" required autofocus placeholder="@lang('lang_v1.email_address')">

                                @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </label>
                    </div>

                    <button type="submit" class="tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 md:tw-h-14 tw-rounded-xl tw-text-base md:tw-text-lg tw-text-white tw-font-semibold tw-w-full tw-max-w-full tw-mt-4 hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700 tw-transition-all tw-duration-200">
                        @lang('lang_v1.send_password_reset_link')
                    </button>
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
            $('.change_lang').click(function() {
                window.location = "{{ route('password.request') }}?lang=" + $(this).attr('value');
            });
        })
    </script>
@endsection