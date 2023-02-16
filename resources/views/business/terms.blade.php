@extends('layouts.app')
@section('title', __('lang_v1.Terms_Terms'))


<!-- Main content -->
@section('content')


    <head>
        <link rel="stylesheet" href="{{ asset('css/home.css') }}">

    </head>



    <div style="margin-top:50px" class="login-form col-md-12 col-xs-12 right-col-content-register">
        <!-- Start Onoo Services -->
        <div class="HomeCardOnoo card "
            style="width:100% !important;height:45px !important;min-height:unset;text-align: center;color: mintcream;font-size: 17px;">
            <label style="text-align: center;color: mintcream;font-size: 17px;">
                @lang('lang_v1.Terms_Terms')
            </label>
        </div>



        <div class="login-form col-md-12 col-xs-12 right-col-content-register">

            <div class="HomeCardOnoo card " style="width:100% !important;height:100% !important">
                <div style="min-height: unset !important;height: 40px  !important;width:100%;margin-right: 0px !important;"
                    class="card card-SlickCarbon HomeCardOnooHeader">
                    <label style="text-align: center;color: mintcream;font-size: 17px;">
                        @lang('lang_v1.Terms_Head_1')
                    </label>
                </div>

                <div class="row rowcardheader">

                    <li class="button-dropul">
                        <a href="{{ action('CustomerGroupController@index') }}">
                            @lang('lang_v1.Terms_Head_1')
                        </a>
                    </li>
                </div>
            </div>
        </div>


        <div class="login-form col-md-12 col-xs-12 right-col-content-register">

            <div class="HomeCardOnoo card " style="width:100% !important;height:100% !important">
                <div style="min-height: unset !important;height: 40px  !important;width:100%;margin-right: 0px !important;"
                    class="card card-SlickCarbon HomeCardOnooHeader">
                    <label style="text-align: center;color: mintcream;font-size: 17px;">
                        @lang('lang_v1.Terms_Head_2')
                    </label>
                </div>

                <div class="row rowcardheader">

                    <li class="button-dropul">
                        <a href="{{ action('CustomerGroupController@index') }}">
                            @lang('lang_v1.Terms_Head_2')
                        </a>
                    </li>
                </div>
            </div>
        </div>


		<div class="login-form col-md-12 col-xs-12 right-col-content-register">

            <div class="HomeCardOnoo card " style="width:100% !important;height:100% !important">
                <div style="min-height: unset !important;height: 40px  !important;width:100%;margin-right: 0px !important;"
                    class="card card-SlickCarbon HomeCardOnooHeader">
                    <label style="text-align: center;color: mintcream;font-size: 17px;">
                        @lang('lang_v1.Terms_Head_3')
                    </label>
                </div>

                <div class="row rowcardheader">

                    <li class="button-dropul">
                        <a href="{{ action('CustomerGroupController@index') }}">
                            @lang('lang_v1.Terms_Head_3')
                        </a>
                    </li>
                </div>
            </div>
        </div>

		<div class="login-form col-md-12 col-xs-12 right-col-content-register">

            <div class="HomeCardOnoo card " style="width:100% !important;height:100% !important">
                <div style="min-height: unset !important;height: 40px  !important;width:100%;margin-right: 0px !important;"
                    class="card card-SlickCarbon HomeCardOnooHeader">
                    <label style="text-align: center;color: mintcream;font-size: 17px;">
                        @lang('lang_v1.Terms_Head_4')
                    </label>
                </div>

                <div class="row rowcardheader">

                    <li class="button-dropul">
                        <a href="{{ action('CustomerGroupController@index') }}">
                            @lang('lang_v1.Terms_Head_4')
                        </a>
                    </li>
                </div>
            </div>
        </div>

    </div>
    </div>
    <!-- End Onoo Services -End -->
    </div>
@endsection
