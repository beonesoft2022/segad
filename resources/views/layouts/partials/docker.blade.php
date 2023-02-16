@inject('request', 'Illuminate\Http\Request')

<head>
    <link rel="stylesheet" href="{{ asset('css/docker.css') }}">

</head>


<header id="headerdock">

    <div class="no-print dockpc"
        style="border-bottom: 2px double;margin-bottom: 35px;border-right: 2px double;border-left: 2px double;"
        id="dock-container">
        <div id="dock">

            <div style="margin-left:unset;margin-right:0" class="row">
                <div class="col-sm-3">

                    <button class="button-30" href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
						@if(Module::has('Essentials'))

						<a  type="button" class=" btn btn-sm clock_in_btn @if (!empty($clock_in)) hide @endif "
						data-type="clock_in" data-toggle="tooltip" data-placement="bottom"
						title="@lang('essentials::lang.clock_in')">
						<i class="fas fa-arrow-circle-down"></i>
					</a>

					<a type="button" class="btn btn-sm clock_out_btn @if (empty($clock_in)) hide @endif "
						data-type="clock_out" data-toggle="tooltip" data-placement="bottom" data-html="true"
						title="@lang('essentials::lang.clock_out') @if (!empty($clock_in)) <br>
							<small>
								<b>@lang('essentials::lang.clocked_in_at'):</b> {{ @format_datetime($clock_in->clock_in_time) }}
							</small>
							<br>
							<small><b>@lang('essentials::lang.shift'):</b> {{ ucfirst($clock_in->shift_name) }}</small>
							@if (!empty($clock_in->start_time) && !empty($clock_in->end_time))
								<br>
								<small>
									<b>@lang('restaurant.start_time'):</b> {{ @format_time($clock_in->start_time) }}<br>
									<b>@lang('restaurant.end_time'):</b> {{ @format_time($clock_in->end_time) }}
								</small> @endif
						@endif">
						<i class="fas fa-hourglass-half fa-spin"></i>
					</a>
					@endif
						{{ @format_date('now') }}
					</button>
                </div>


                <div class="col-sm-6">
                    <ul style="padding-top: 7px;">
                        @can('product.view')
                            <li title="@lang('lang_v1.dock_products')" style="margin-right: 2px;margin-left: 2px;">
                                <span class="dock-containerspan">@lang('lang_v1.dock_products')</span>
                                <a title="@lang('lang_v1.dock_products')" href="{{ action('ProductController@index') }}"><img
                                        src={{ asset('/images/icons/dockbar/products.png') }} /></a>
                            </li>
                        @endcan

                        @can('all_expense.access')
                            <li title="@lang('lang_v1.list_expenses')" style="margin-right: 2px;margin-left: 2px;">
                                <span class="dock-containerspan">@lang('lang_v1.list_expenses')</span>
                                <a title="@lang('lang_v1.list_expenses')" href="{{ action('ExpenseController@index') }}"><img
                                        src={{ asset('/images/icons/expense64.png') }} /></a>
                            </li style="margin-right: 2px;margin-left: 2px;">
                        @endcan

                        @can('sell.view')
                            <li title="@lang('lang_v1.all_sales')">
                                <span class="dock-containerspan">@lang('lang_v1.dock_sales')</span>
                                <a title="@lang('lang_v1.all_sales')" href="{{ action('SellController@index') }}"><img
                                        src={{ asset('/images/icons/dockbar/sales.png') }} /></a>
                            </li style="margin-right: 2px;margin-left: 2px;">
                        @endcan
                        @can('business_settings.access')
                            <li title="@lang('lang_v1.business_settings')" style="margin-right: 2px;margin-left: 2px;">
                                <span class="dock-containerspan">@lang('lang_v1.business_settings')</span>
                                <a title="@lang('lang_v1.business_settings')" href="{{ action('BusinessController@getBusinessSettings') }}"><img
                                        src={{ asset('/images/icons/settings.png') }} /></a>
                            </li>
                        @endcan
                        @can('purchase.view')
                            <li title="@lang('lang_v1.dock_purchase')" style="margin-right: 2px;margin-left: 2px;">
                                <span class="dock-containerspan">@lang('lang_v1.dock_purchase')</span>
                                <a title="@lang('lang_v1.dock_purchase')" href="{{ action('PurchaseController@index') }}"><img
                                        src={{ asset('/images/icons/dockbar/purchase.png') }} /></a>
                            </li>
                        @endcan

                        @php
                            $all_notifications = auth()->user()->notifications;
                            $unread_notifications = $all_notifications->where('read_at', null);
                            $total_unread = count($unread_notifications);
                        @endphp

                        <li style="margin-right: 2px;margin-left: 2px;" class="dropdown notifications-menu">
                            <a href="#" class="dropdown-toggle load_notifications" data-toggle="dropdown"
                                id="show_unread_notifications" data-loaded="false">
                                <img src={{ asset('/images/icons/dockbar/notification.png') }} />

                                <span
                                    style="display: unset;position: unset;bottom: unset;left: unset;width: unset;background-color: unset;padding: unset;"
                                    class=" label-warning notifications_count">
                                    @if (!empty($total_unread))
                                        {{ $total_unread }}
                                    @endif
                                </span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- <li class="header">You have 10 unread notifications</li> -->
                                <li>
                                    <!-- inner menu: contains the actual data -->

                                    <ul style="width: 250px;padding: 10px;" class="menu" id="notifications_list">
                                        <span class="notif-info"
                                            style="display: unset;position: unset;bottom: unset;left: unset;width: unset;background-color: unset;padding: unset;">

                                        </span>
                                    </ul>
                                </li>

                                @if (count($all_notifications) > 10)
                                    <li class="footer load_more_li">
                                        <a href="#" class="load_more_notifications">@lang('lang_v1.load_more')</a>
                                    </li>
                                @endif
                            </ul>
                        </li>






                    </ul>
                </div>
                <div class="col-sm-3">
                    @if (in_array('pos_sale', $enabled_modules))
                        @can('sell.create')
                            <a href="{{ action('SellPosController@create') }}" title="@lang('sale.pos_sale')"
                                data-toggle="tooltip" data-placement="bottom" class="button-30">
                                <strong> @lang('lang_v1.dock_pos') <i class="fa fa-th-large"></i> &nbsp;</strong>
                            </a>
                        @endcan
                    @endif

                </div>
            </div>

        </div>
    </div>


    <div class="no-print" style="z-index: 1030;" id="dock-statusbar">
        <div class="row">
            <div class="col-sm-4">
                <div id="dock">
                    <li>
                        <span class="dock-containerspan">@lang('lang_v1.dock_menu')</span>
                        <a onclick="topFunction()" href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"><img
                                src={{ asset('/images/icons/dockbar/menu.png') }} /></a>
                    </li>
                    <li>
                        <span class="dock-containerspan">@lang('lang_v1.dock_up')</span>
                        <a href="#"><img style="margin-top: 2px;"
                                src={{ asset('/images/icons/dockbar/up.png') }} /></a>
                    </li>
				</li>
				<li>
					<span class="dock-containerspan">@lang('lang_v1.dock_howto')</span>
					<a href="{{ action('BusinessController@howto') }}"><img
							src={{ asset('/images/icons/dockbar/howto.png') }} /></a>
				</li>

                    <li style="">
                        <span class="dock-containerspan">@lang('lang_v1.dock_calendar')</span>
                        <a href="{{ route('calendar') }}"><img style="margin-top: 2px;"
                                src={{ asset('/images/icons/dockbar/todo.png') }} /></a>
                    </li>
                    <li style="">
                        <span class="dock-containerspan">@lang('lang_v1.dock_Tasks')</span>
                        <a href="#" class="btn-modal"
                            data-href="{{ action('\Modules\Essentials\Http\Controllers\ToDoController@create') }}"
                            data-container="#task_modal"><img style="margin-top: 2px;"
                                src={{ asset('/images/icons/dockbar/task.png') }} /></a>
                    </li>

					<li onclick="hideheader()" id="hideall">
                        <span class="dock-containerspan">@lang('lang_v1.dock_Hide')</span>
                        <a href="#" id="view_todays_profit"><img style="margin-top: 2px;"
                                src={{ asset('/images/icons/dockbar/hideicon.png') }} /></a>
                    </li>

                </div>
            </div>



            <div class="col-sm-4">
                <div class="row">
                    <div class="col-sm-4">
                        <div style="" class="">
                            <a href="{{ action('HomeController@index') }}"><img
                                    style="margin-top: 2px;animation: wiggle 2s linear infinite;"
                                    src={{ asset('/images/icons/dockbar/letter-b.png') }} />
                                @if (Module::has('Superadmin'))
                                    @includeIf('superadmin::layouts.partials.active_subscription')
                                @endif
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="" class=" containerbtn">

                            <a href="{{ action('UserController@getProfile') }}"
                                style="width: 150px !important;margin-top: 3px;" class="buttonBlue">
                                <span class="dock-containerspan">

                                    {{ Auth::User()->first_name }} {{ Auth::User()->last_name }}
                                </span>
                            </a>

                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="" class="">
                            <a href="{{ action('HomeController@index') }}"><img
                                    style="margin-top: 2px;animation: wiggle 2s linear infinite;"
                                    src={{ asset('/images/icons/dockbar/letter-b.png') }} /></a>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-sm-4">
                <div class="">



                    <li>
                        <span class="dock-containerspan">@lang('lang_v1.dock_suppliers')</span>
                        <a href="{{ action('ContactController@index', ['type' => 'supplier']) }}"><img
                                src={{ asset('/images/icons/dockbar/client.png') }} /></a>
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
					<li title="@lang('lang_v1.RightClick_BassicSetting')" style="margin-right: 2px;margin-left: 2px;">
						<span class="dock-containerspan">@lang('lang_v1.RightClick_BassicSetting')</span>
						<a title="@lang('lang_v1.RightClick_BassicSetting')" href="{{ action('BusinessController@getbasicBusinessSettings') }}"><img
								src={{ asset('/images/icons/dockbar/basicsetting.png') }} /></a>
					</li>
				@endcan

				<li title="@lang('lang_v1.profile')" style="margin-right: 2px;margin-left: 2px;">
					<span class="dock-containerspan">@lang('lang_v1.profile')</span>
					<a title="@lang('lang_v1.profile')" href="{{ action('UserController@getProfile') }}"><img
							src={{ asset('/images/icons/dockbar/profile.png') }} /></a>
				</li>



                    <li>
                        <span class="dock-containerspan">@lang('lang_v1.dock_logout')</span>
                        <a href="{{action('Auth\LoginController@logout')}}"><img style="margin-top: 2px;"
                                src={{ asset('/images/icons/dockbar/logout.png') }} /></a>
                    </li>

                </div>
            </div>
        </div>


    </div>


</header>

<script>
function topFunction() {
  document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
}
	function hideheader() {
            document.getElementById("headerdock").style.display = "none";
        }
</script>
