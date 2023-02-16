<!doctype html>
<html lang="{{ config('app.locale') }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,600" rel="stylesheet" type="text/css"> -->

    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/wellcomebrand.css') }}">
    <!-- Styles -->
    <style>


    </style>
</head>

<body>


    <div class="container">
        <div class="row">
            <div class="area">
                <ul class="circles">
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <div class="col-xs-12 col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
                        <div class="page-title  home text-center">
                            <h1 class="brandstyle"><span class='one'>B</span><span class='two'>R</span><span
                                    class='three'>A</span><span class='four'>N</span><span class='five'>D</span>
                            </h1>

                            <h1 style="color: #ffffff;" class="mt20">@lang('lang_v1.brandhome')</h1>
                        </div>

                        <div class="hexagon-menu clear">

                            @if (!Auth::check())
                                <div onclick="location.href='{{ route('login') }}'" class="hexagon-item">
                                    <div class="hex-item">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                    <div class="hex-item">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                    <a class="hex-content">
                                        <span onclick="location.href='{{ route('login') }}'" class="hex-content-inner">
                                            <span class="icon">
                                                <div class="wellcomeloginimg"></div>
                                            </span>
                                            <span class="title">@lang('lang_v1.login')</span>
                                        </span>
                                        <svg viewBox="0 0 173.20508075688772 200" height="200" width="174"
                                            version="1.1" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M86.60254037844386 0L173.20508075688772 50L173.20508075688772 150L86.60254037844386 200L0 150L0 50Z"
                                                fill="#1e2530"></path>
                                        </svg>
                                    </a>
                                </div>
                                <div onclick="location.href='{{ route('business.getRegister') }}'" class="hexagon-item">
                                    <div class="hex-item">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                    <div class="hex-item">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                    <a class="hex-content">
                                        <span class="hex-content-inner">
                                            <span class="icon">
                                                <div class="wellcomeregisterimg"></div>
                                            </span>
                                            <span onclick="location.href='{{ route('business.getRegister') }}'"
                                                class="title">@lang('lang_v1.register')</span>
                                        </span>
                                        <svg viewBox="0 0 173.20508075688772 200" height="200" width="174"
                                            version="1.1" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M86.60254037844386 0L173.20508075688772 50L173.20508075688772 150L86.60254037844386 200L0 150L0 50Z"
                                                fill="#1e2530"></path>
                                        </svg>
                                    </a>
                                </div>
                            @else
                                <div onclick="location.href='{{ action('HomeController@index') }}'"
                                    class="hexagon-item">
                                    <div class="hex-item">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                    <div class="hex-item">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                    <a onclick="location.href='{{ action('HomeController@index') }}'"
                                        class="hex-content">
                                        <span class="hex-content-inner">
                                            <span class="icon">
                                                <div class="wellcomehomeimg"></div>
                                            </span>
                                            <span onclick="location.href='{{ action('HomeController@index') }}'"
                                                class="title">@lang('home.home')</span>

                                        </span>

                                        <svg viewBox="0 0 173.20508075688772 200" height="200" width="174"
                                            version="1.1" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M86.60254037844386 0L173.20508075688772 50L173.20508075688772 150L86.60254037844386 200L0 150L0 50Z"
                                                fill="#1e2530"></path>
                                        </svg>
                                    </a>
                                </div>
                                <div onclick="location.href='https://www.onoo.pro/support/public'" class="hexagon-item">
                                    <div class="hex-item">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                    <div class="hex-item">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                    <a onclick="location.href='https://www.onoo.pro/support/public'"
                                        class="hex-content">
                                        <span class="hex-content-inner">
                                            <span class="icon">
                                                <div class="wellcomesupporttoimg"></div>
                                            </span>
                                            <span onclick="location.href='https://www.onoo.pro/support/public'"
                                                class="title">@lang('lang_v1.BrandSupport')</span>

                                        </span>
                                        <svg viewBox="0 0 173.20508075688772 200" height="200" width="174"
                                            version="1.1" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M86.60254037844386 0L173.20508075688772 50L173.20508075688772 150L86.60254037844386 200L0 150L0 50Z"
                                                fill="#1e2530"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endif
                            <div class="hexagon-item">
                                <div class="hex-item">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                                <div class="hex-item">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                                <a class="hex-content">
                                    <span class="hex-content-inner">
                                        <span class="icon">
                                            <div class="wellcomehowtoimg"></div>
                                        </span>
                                        <span class="title">@lang('lang_v1.brand_howto')</span>
                                    </span>
                                    <svg viewBox="0 0 173.20508075688772 200" height="200" width="174"
                                        version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M86.60254037844386 0L173.20508075688772 50L173.20508075688772 150L86.60254037844386 200L0 150L0 50Z"
                                            fill="#1e2530"></path>
                                    </svg>
                                </a>
                            </div>


                        </div>
                    </div>
            </div>
        </div>
    </div>
    </div>
    <!-- Scripts -->
    <div class="container">
        <div class="row">


            <div class="hexagon-menu">


                <div class="hexagon-item">
                    <div class="hex-item">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    <div class="hex-item">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    <a class="hex-content">
                        <span class="hex-content-inner">
                            <span class="icon">
                                <div class="wellcomecontactimg"></div>
                            </span>
                            <span class="title">@lang('lang_v1.brand_contact')</span>
                        </span>
                        <svg viewBox="0 0 173.20508075688772 200" height="200" width="174" version="1.1"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M86.60254037844386 0L173.20508075688772 50L173.20508075688772 150L86.60254037844386 200L0 150L0 50Z"
                                fill="#1e2530"></path>
                        </svg>
                    </a>
                </div>

            </div>



        </div>


    </div>
    </div>
</body>

</html>
