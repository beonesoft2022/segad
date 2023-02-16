@inject('request', 'Illuminate\Http\Request')

<head>
    <link rel="stylesheet" href="{{ asset('css/docker.css') }}">

</head>


<header id="headerdock">




    <div class="no-print" style="z-index: 1030;" id="dock-statusbar">
        <div class="row">



            <div class="col-sm-4">
                <div class="">
					<li title="@lang('lang_v1.dock_products')"  style="margin-right: 2px;margin-left: 2px;">
						<span class="dock-containerspan">@lang('lang_v1.dock_menu')</span>
						<a onclick="topFunction()" style="float: unset !important;display: unset !important;" href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"><img
								src={{ asset('/images/icons/dockbar/menu.png') }} /></a>
					</li>
					<li>
                        <a href="{{ action('HomeController@index') }}"><img
							style="margin-top: 2px;animation: wiggle 2s linear infinite;"
							src={{ asset('/images/icons/dockbar/letter-b.png') }} />
						@if (Module::has('Superadmin'))
							@includeIf('superadmin::layouts.partials.active_subscription')
						@endif
					</a>
                    <li>
                        <span class="dock-containerspan">@lang('lang_v1.dock_home')</span>
						<a href="{{ action('HomeController@index') }}"><img
							style="margin-top: 2px"
							src={{ asset('/images/icons/willcome/home.png') }} />
					</a>
                    </li>
                    <li>
                        <span class="dock-containerspan">@lang('lang_v1.dock_clients')</span>
                        <a href="{{ action('ContactController@index', ['type' => 'customer']) }}"><img
                                src={{ asset('/images/icons/dockbar/supplier.png') }} /></a>
                    </li>
                    <li>
                        <span class="dock-containerspan">@lang('lang_v1.dock_users')</span>
                        <a href="{{ action('ManageUserController@index') }}"><img
                                src={{ asset('/images/icons/dockbar/users.png') }} /></a>
                    </li>
					@can('business_settings.access')
					<li title="@lang('lang_v1.business_settings')" style="margin-right: 2px;margin-left: 2px;">
						<span class="dock-containerspan">@lang('lang_v1.business_settings')</span>
						<a title="@lang('lang_v1.business_settings')" href="{{ action('BusinessController@getBusinessSettings') }}"><img
								src={{ asset('/images/icons/settings.png') }} /></a>
					</li>
				@endcan

                    <li title="@lang('lang_v1.profile')" style="margin-right: 2px;margin-left: 2px;">
                        <span class="dock-containerspan">@lang('lang_v1.profile')</span>
                        <a title="@lang('lang_v1.profile')" href="{{ action('UserController@getProfile') }}"><img
                                src={{ asset('/images/icons/dockbar/profile.png') }} /></a>
                    </li>



                    <li>
                        <span class="dock-containerspan">@lang('lang_v1.dock_logout')</span>
                        <a href="{{ action('Auth\LoginController@logout') }}"><img style="margin-top: 2px;"
                                src={{ asset('/images/icons/dockbar/logout.png') }} /></a>
                    </li>

                </div>
            </div>
        </div>


    </div>


</header>

<script>
    function hideheader() {
        document.getElementById("headerdock")
            .style.display = "none";

    }

	function topFunction() {
  document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
}
</script>
