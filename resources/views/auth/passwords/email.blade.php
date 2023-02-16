@extends('layouts.auth2')

@section('title', __('lang_v1.reset_password'))

@section('content')

    <div class="background-wrap">
        <div class="background"></div>
    </div>
    <div class="login-form  right-col-content">
        <form method="POST" action="{{ route('login') }}" id="login-form">
            {{ csrf_field() }}
            <h1 id="litheader">Brand</h1>
            <div class="inset">

                <div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}"
                        required autofocus placeholder="@lang('lang_v1.email_address')">
                    <span class="fa fa-envelope form-control-feedback"></span>
                    @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
                <br>
                <div class="form-group">
                    <button type="submit" class="login-button btn btn-primary btn-block btn-flat">
                        @lang('lang_v1.send_password_reset_link')
                    </button>
                </div>
				<p class="p-container form-group">
					<br>
					<a href="{{ route('login') }}" class="login-button" style="font-family:'droid';margin-top:20px;margin-right: 13%;" value="">
						@lang('lang_v1.backtologin')
					</a>
				</p>
            </div>
        </form>
    </div>

@endsection
