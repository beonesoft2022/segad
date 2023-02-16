@extends('layouts.auth2')
@section('title', __('lang_v1.login'))


<head>
    <link rel="stylesheet" href="{{ asset('css/loginbrand.css') }}">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="stylesheet" href="css/main.css">
	<script src="js/vendor/modernizr-2.6.2.min.js"></script>
</head>
<button style="top: 0;position: fixed;" onclick="location.href='{{ route('demosys') }}'"  class='button-30'>{{ __('business.Login_Demo')}} </button>

@section('content')
    <div class="background-wrap">
        <div class="background"></div>
    </div>

    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf
		{{ csrf_field() }}
		<div id="loader-wrapper">
			<div style="z-index: 99999999" class="ring">جاري تحميل الملفات
				<span class="ringspan"></span>
			  </div>
			<div id="loader"></div>

			<div class="loader-section section-left"></div>
            <div class="loader-section section-right"></div>

		</div>
        <h1 id="litheader">Brand</h1>
        <div class="inset">

            <div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
                <p>
                    @php
                        $username = old('username');
                        $password = null;
                        if (config('app.env') == 'demo') {
                            $username = 'admin';
                            $password = '123456';

                            $demo_types = [
                                'all_in_one' => 'admin',
                                'super_market' => 'admin',
                                'pharmacy' => 'admin-pharmacy',
                                'electronics' => 'admin-electronics',
                                'services' => 'admin-services',
                                'restaurant' => 'admin-restaurant',
                                'superadmin' => 'superadmin',
                                'woocommerce' => 'woocommerce_user',
                                'essentials' => 'admin-essentials',
                                'manufacturing' => 'manufacturer-demo',
                            ];

                            if (!empty($_GET['demo_type']) && array_key_exists($_GET['demo_type'], $demo_types)) {
                                $username = $demo_types[$_GET['demo_type']];
                            }
                        }
                    @endphp
                    {{-- <input type="text" name="username" id="email" placeholder="Email address"> --}}
                    <input id="username" type="text" class="form-control" name="username" value="{{ $username }}"
                        required autofocus placeholder="@lang('lang_v1.username')">
                    @if ($errors->has('username'))
                        <span class="help-block">
                            <strong>{{ $errors->first('username') }}</strong>
                        </span>
                    @endif
                </p>
            </div>

            <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
                <p>
                    <input id="password" type="password" class="form-control" name="password" value="{{ $password }}"
                        required placeholder="@lang('lang_v1.password')">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </p>
            </div>

            <div style="">
                <div style="font-family: 'droid';margin-top: 40px;" class="checkbox icheck ">
                    <label>
                        <input step="color:#fff !important;" type="checkbox" name="remember"
                            {{ old('remember') ? 'checked' : '' }}> @lang('lang_v1.remember_me')
                    </label>
                </div>
                <input class="loginLoginValue" type="hidden" name="service" value="login" />
            </div>
			<p class="p-container form-group">
                <input style="font-family: 'droid';margin-top: 25px;" class="btn-login login-button" type="submit" name="Login" id="go" value="تسجيل دخول">
            </p>
            <p class="p-container form-group">
				<br>
                <a href="{{ route('password.request') }}" class="login-button" style="font-family:'droid';margin-top:20px;margin-right: 18%;" value="">
					@lang('lang_v1.forgot_your_password')
				</a>
            </p>
    </form>

    {{--
	<div class="col-md-12 col-xs-12" style="padding-bottom: 30px;">
        @component('components.widget', ['class' => 'box-primary', 'header' => '<h4 class="text-center">تستطيع من خلال الضغط على الزرار التالى تجريب النسخة دون الحاجة الى تسجيل <small><i> .</u></i></small></h4>'])

            <a href="?demo_type=all_in_one" style="width: 100% !important" class="btn btn-app bg-olive demo-login" data-toggle="tooltip" title="." > <i class="fas fa-star"></i> اضغط هنا لتجريب السيستم</a>

        @endcomponent
    </div>
 --}}
@stop
<div class="brandloginfooter">
	<a class="button-86" href="{{ route('business.getRegister') }}@if (!empty(request()->lang)){{'?lang=' . request()->lang}} @endif">{{ __('business.Login_Register') }}</a>
</div>
@section('javascript')
<script type="text/javascript">
    $(document).ready(function(){
        $('#change_lang').change( function(){
            window.location = "{{ route('login') }}?lang=" + $(this).val();
        });

        $('a.demo-login').click( function (e) {
           e.preventDefault();
           $('#username').val("demo1");
           $('#password').val("123456");
           $('form#login-form').submit();
        });
    })
	$(document).ready(function() {

var state = false;

//$("input:text:visible:first").focus();

$('#accesspanel').on('submit', function(e) {

	e.preventDefault();

	state = !state;

	if (state) {
		document.getElementById("litheader").className = "poweron";
		document.getElementById("go").className = "";
		document.getElementById("go").value = "Initializing...";
	} else {
		document.getElementById("litheader").className = "";
		document.getElementById("go").className = "denied";
		document.getElementById("go").value = "Access Denied";
	}

});

});
</script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
<script src="js/main.js"></script>
@endsection
