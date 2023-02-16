@extends('layouts.auth2')
@section('title', __('lang_v1.login'))


<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/loginbrand.css') }}">
</head>

@section('content')
<button style="top: 0;position: fixed;" onclick="location.href='{{ route('login') }}'"  class='button-30'>{{ __('business.Reg_BacktoLogin')}} </button>

    <div class="background-wrap">
        <div class="background"></div>
    </div>

    <div style="display:none">


        <form method="POST" action="{{ route('login') }}" id="login-form">
            @csrf
            {{ csrf_field() }}
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
                        <input id="username" type="text" class="form-control" name="username"
                            value="{{ $username }}" required autofocus placeholder="@lang('lang_v1.username')">
                        @if ($errors->has('username'))
                            <span class="help-block">
                                <strong>{{ $errors->first('username') }}</strong>
                            </span>
                        @endif
                    </p>
                </div>

                <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
                    <p>
                        <input id="password" type="password" class="form-control" name="password"
                            value="{{ $password }}" required placeholder="@lang('lang_v1.password')">
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
                    <input style="font-family: 'droid';margin-top: 25px;" class="btn-login login-button" type="submit"
                        name="Login" id="go" value="تسجيل دخول">
                </p>
                <p class="p-container form-group">
                    <br>
                    <a href="{{ route('password.request') }}" class="login-button"
                        style="font-family:'droid';margin-top:20px;margin-right: 18%;" value="">
                        @lang('lang_v1.forgot_your_password')
                    </a>
                </p>
        </form>
    </div>


@stop
<div style="margin-top: 110px">
    @component('components.widget',
        [
            'class' => 'box-primary',
            'header' =>
                '<h4 class="text-center"> اضــغــط على نـوع نشـاطـك لـتـجربـة السيســتم<small><i> .</u></i></small></h4>',
        ])
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;"
            class="btn btn-app bg-olive demo-login-demo-allfeatures" data-toggle="tooltip" title="."> جميع النشاطات </a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;"
            class="btn btn-app bg-yellow-active demo-login-demo-Supermarket" data-toggle="tooltip" title="."> سوبر
            ماركت ومواد غذائيه ومجمدات</a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;" class="btn btn-app bg-blue demo-login-demo-rest"
            data-toggle="tooltip" title="."> الــمــطــاعــم</a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;" class="btn btn-app bg-red demo-login-demo-cafe"
            data-toggle="tooltip" title="."> الــكــافــيهـــات</a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;" class="btn btn-app bg-navy demo-login-demo-clos"
            data-toggle="tooltip" title="."> ملابس واحذيه ومفروشات</a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;"
            class="btn btn-app bg-purple demo-login-demo-hometoles" data-toggle="tooltip" title="."> أدوات منزليه</a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;"
            class="btn btn-app bg-teal demo-login-demo-electronics" data-toggle="tooltip" title="."> كمبيوتر وموبايل
            وأجهزة كهربائيه </a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;" class="btn btn-app bg-info demo-login-demo-pharmacy"
            data-toggle="tooltip" title="."> صــيــدلــيات</a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;"
            class="btn btn-app bg-black-gradient demo-login-demo-company" data-toggle="tooltip" title="."> ادارة
            شــركــات</a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;"
            class="btn btn-app bg-maroon-gradient demo-login-demo-construction" data-toggle="tooltip" title=".">
            شركات مقاولات</a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;"
            class="btn btn-app bg-lime-active demo-login-demo-services" data-toggle="tooltip" title="."> نشاط خدمي
            (يقدم خدمات)</a>
        <a href="?demo_type=all_in_one" style="width: 100% !important;font-family: 'droid';font-size: 15px;"
            class="btn btn-app bg-light-blue-active demo-login-demo-mnu" data-toggle="tooltip" title=".">
            مــصــــانــــــع</a>
    @endcomponent
</div>
<div class="brandloginfooter">
    <a class="button-86"
        href="{{ route('business.getRegister') }}@if (!empty(request()->lang)) {{ '?lang=' . request()->lang }} @endif">{{ __('business.Login_Register') }}</a>
</div>
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#change_lang').change(function() {
                window.location = "{{ route('login') }}?lang=" + $(this).val();
            });

            $('a.demo-login').click(function(e) {
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

		$(document).ready(function(){
        $('#change_lang').change( function(){
            window.location = "{{ route('login') }}?lang=" + $(this).val();
        });

        $('a.demo-login-demo-allfeatures').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-allfeatures-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-Supermarket').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-Supermarket-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-rest').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-rest-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-cafe').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-cafe-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-clos').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-clos-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-hometoles').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-hometoles-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-electronics').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-electronics-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-pharmacy').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-pharmacy-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-company').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-company-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-construction').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-construction-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-services').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-services-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
		$('a.demo-login-demo-mnu').click( function (e) {
           e.preventDefault();
           $('#username').val("demo-mnu-user");
           $('#password').val("Hm607080###");
           $('form#login-form').submit();
        });
    })
    </script>
@endsection
