@inject('request', 'Illuminate\Http\Request')

@if($request->segment(1) == 'pos' && ($request->segment(2) == 'create' || $request->segment(3) == 'edit'))
    @php
        $pos_layout = true;
    @endphp
@else
    @php
        $pos_layout = false;
		$is_mobile = isMobile();
    @endphp
@endif

@php
    $whitelist = ['127.0.0.1', '::1'];
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr'}}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
		<script src="{{ asset('js/vendor/modernizr-2.6.2.min.js') }}"></script>
		<link rel="stylesheet" href="{{ asset('css/main.css') }}">


        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ Session::get('business.name') }}</title>

        @include('layouts.partials.css')

        @yield('css')

		<style>

.context-menu {
   list-style: none;
   z-index: 999999;
  position: absolute;
  font-size: 17px;
    font-weight: 900;
}
.menu {
  display: flex;
  flex-direction: column;
  background: darkgray;
  border-radius: 10px;
  box-shadow: 0 10px 20px rgb(64 64 64 / 5%);
  padding: 10px 0;
  -webkit-animation: fadeIn 0.1s ease-out;
  animation: fadeIn 0.5s ease-out;
  opacity:1.0;
  display:block;
  width: 250px;
}
.menu > li > a {
  font: 'droid';
  border: 0;
font-style: 'droid';
padding: 2px 30px 2px 15px;
  width: 100%;
  display: flex;
  align-items: center;
  position: relative;
  text-decoration: unset;
  color: #000;
  font-weight: 500;
  transition: 0.5s linear;
  -webkit-transition: 0.5s linear;
  -moz-transition: 0.5s linear;
  -ms-transition: 0.5s linear;
  -o-transition: 0.5s linear;
}
.menu > li > a:hover {
  background:#f1f3f7;
  color: #4b00ff;
}
.menu > li {
	display: unset;
}
.menu > li > a > i {
  padding-right: 10px;
}
.menu > li.trash > a:hover {
  color: red;
}

/* Animatinons */
@-webkit-keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1.0;}
}

@keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1.0;}
}
@-webkit-keyframes fadeOut {
    from {opacity: 1.0;}
    to {opacity: 0.0;}
}

@keyframes fadeOut {
    from {opacity: 1.0;}
    to {opacity: 0.0;}
}

.is-fadingIn {
  -webkit-animation: fadeIn 0.1s ease-out;
  animation: fadeIn 0.1s ease-out;
  opacity:1.0;
  display:block;
}
.is-fadingOut {
  -webkit-animation: fadeOut 0.1s ease-out;
  animation: fadeOut 0.1s ease-out;
  opacity:0.0;
  display:block;
}
		</style>
    </head>

    <body class="@if($pos_layout) hold-transition lockscreen @else hold-transition skin-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'blue-light'}}@endif sidebar-mini @endif">

		<div id="contextMenu" class="context-menu" style="display: none">
			<ul class="menu">
				<li class="share"><a href="{{ action('HomeController@index') }}">@lang('lang_v1.Rightclick_Home') </a></li>

				@can('sell.create')
				<li >
					<a  href="{{ action('SellController@create') }}">@lang('lang_v1.RightClick_add_sale')</a>
				</li>
		    	@endcan

				@can('product.create')
				<li >
					<a  href="{{ action('ProductController@create') }}">@lang('lang_v1.RightClick_AddProduct')</a>
				</li>
		    	@endcan

				@can('purchase.create')
				<li >
					<a  href="{{ action('PurchaseController@create') }}">@lang('lang_v1.RightClick_purchasecreate')</a>
				</li>
		    	@endcan

				@can('expense.add')
				<li >
					<a  href="{{ action('ExpenseController@create') }}">@lang('lang_v1.RightClick_add_expense')</a>
				</li>
		    	@endcan


				@can('customer.view')
				<li >
					<a  href="{{ action('ContactController@index', ['type' => 'customer']) }}">@lang('lang_v1.RightClick_ManageClient')</a>
				</li>
		    	@endcan

				@can('supplier.create')
				<li >
					<a  href="{{ action('ContactController@index', ['type' => 'supplier']) }}">@lang('lang_v1.RightClick_ManageSupplier')</a>
				</li>
		    	@endcan
				{{-- <li class="link"><a href="#">--------------------------------</a></li>

				<li>
					<a class="fa fa-facebook" href="#">xxxx</a>
				</li>
				<li>
					<a class="fa fa-facebook" href="#">xxxx</a>
				</li>
				<li>
					<a class="fa fa-facebook" href="#">xxxx</a>
				</li> --}}
				<li class="link"><a href="#">--------------------------------</a></li>
				@can('business_settings.access')
				<li class="copy"><a href="{{ action('BusinessController@getBusinessSettings') }}">@lang('lang_v1.RightClick_Setting')</a></li>
				<li class="copy"><a href="{{ action('BusinessController@getbasicBusinessSettings') }}">@lang('lang_v1.RightClick_BassicSetting')</a></li>

				@endcan
				<li class="paste"><a href="https://www.onoo.pro/support/public">@lang('lang_v1.RightClick_Support')</a></li>
				<li class="download"><a  href="{{ action('UserController@getProfile') }}">@lang('lang_v1.RightClick_ManageProfile')</a></li>
				<li class="trash"><a href="{{action('Auth\LoginController@logout')}}">@lang('lang_v1.RightClick_Logout')</a></li>
			</ul>
		</div>


        <div class="wrapper thetop">
            <script type="text/javascript">
                if(localStorage.getItem("upos_sidebar_collapse") == 'true'){
                    var body = document.getElementsByTagName("body")[0];
                    body.className += " sidebar-collapse";
                }
            </script>
              @if(!$pos_layout)

			  @include('layouts.partials.header')
			  @include('layouts.partials.sidebar')


			  @else
                @include('layouts.partials.header-pos')
            @endif

            @if(in_array($_SERVER['REMOTE_ADDR'], $whitelist))
                <input type="hidden" id="__is_localhost" value="true">
            @endif

            <!-- Content Wrapper. Contains page content -->
            <div class="@if(!$pos_layout) content-wrapper @endif">
                <!-- empty div for vuejs -->
                <div id="app">
                    @yield('vue')
                </div>
                <!-- Add currency related field-->
                <input type="hidden" id="__code" value="{{session('currency')['code']}}">
                <input type="hidden" id="__symbol" value="{{session('currency')['symbol']}}">
                <input type="hidden" id="__thousand" value="{{session('currency')['thousand_separator']}}">
                <input type="hidden" id="__decimal" value="{{session('currency')['decimal_separator']}}">
                <input type="hidden" id="__symbol_placement" value="{{session('business.currency_symbol_placement')}}">
                <input type="hidden" id="__precision" value="{{session('business.currency_precision', 2)}}">
                <input type="hidden" id="__quantity_precision" value="{{session('business.quantity_precision', 2)}}">
                <!-- End of currency related field-->
                @can('view_export_buttons')
                    <input type="hidden" id="view_export_buttons">
                @endcan
                @if(isMobile())
                    <input type="hidden" id="__is_mobile">
                @endif
                @if (session('status'))
                    <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
                @endif
                @yield('content')

                <div class='scrolltop no-print'>
                    <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
                </div>

                @if(config('constants.iraqi_selling_price_adjustment'))
                    <input type="hidden" id="iraqi_selling_price_adjustment">
                @endif

                <!-- This will be printed -->
                <section class="invoice print_section" id="receipt_section">
                </section>

            </div>
            @include('home.todays_profit_modal')
            <!-- /.content-wrapper -->

            @if(!$pos_layout)
            @include('layouts.partials.footer')
			@if(isMobile())
			@include('layouts.partials.dockermobile')
			@else ()
			@include('layouts.partials.docker')
		  @endif

            {{-- @else
                @include('layouts.partials.footer_pos') --}}
            @endif

            <audio id="success-audio">
              <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
              <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
            </audio>
            <audio id="error-audio">
              <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
              <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
            </audio>
            <audio id="warning-audio">
              <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
              <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
            </audio>
        </div>

        @if(!empty($__additional_html))
            {!! $__additional_html !!}
        @endif

        @include('layouts.partials.javascripts')

        <div class="modal fade view_modal" tabindex="-1" role="dialog"
        aria-labelledby="gridSystemModalLabel"></div>

        @if(!empty($__additional_views) && is_array($__additional_views))
            @foreach($__additional_views as $additional_view)
                @includeIf($additional_view)
            @endforeach
        @endif

 <script type="text/javascript">

document.onclick = hideMenu;
       document.oncontextmenu = rightClick;

        function hideMenu() {
            document.getElementById("contextMenu")
                    .style.display = "none"
					$('#contextmenu').addClass('is-fadingOut');
        }

        function rightClick(e) {
            e.preventDefault();

            if (document.getElementById("contextMenu") .style.display == "block"){
                hideMenu();
            }else{
                var menu = document.getElementById("contextMenu")
                menu.style.display = 'block';
                menu.style.left = e.pageX + "px";
                menu.style.top = e.pageY + "px";
				$('#contextmenu').addClass('is-fadingIn');
            }
        }



</script>
<script src="{{ asset('js/main.js') }}"></script>


    </body>

</html>
