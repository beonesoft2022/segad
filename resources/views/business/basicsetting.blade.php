@extends('layouts.app')
@section('title', __('business.basicsetting'))


<!-- Main content -->
@section('content')


    <head>
        <link rel="stylesheet" href="{{ asset('css/home.css') }}">

    </head>



    <div class="login-form col-md-12 col-xs-12 right-col-content-register">
        <!-- Start Onoo Services -->
        <div class="HomeCardOnoo card " style="width:100% !important;height:45px !important;min-height:unset;text-align: center;color: mintcream;font-size: 17px;">
			<label style="text-align: center;color: mintcream;font-size: 17px;">
				@lang('business.basicsetting')
			</label>
        </div>



    <div class="login-form col-md-12 col-xs-12 right-col-content-register">
        <!-- Start Onoo Services -->
        <div class="HomeCardOnoo card " style="width:100% !important;height:100% !important">
            <div style="min-height: unset !important;height: 40px  !important;width:100%;margin-right: 0px !important;"
                class="card card-SlickCarbon HomeCardOnooHeader">
                <label style="text-align: center;color: mintcream;font-size: 17px;">
					@lang('menulang.people')
                </label>
            </div>

            <div class="row rowcardheader">
                @can('customer.view')
                    <li class="button-dropul">
                        <a href="{{ action('CustomerGroupController@index') }}">
                            @lang('lang_v1.customer_groups')
                        </a>
                    </li>
                @endcan

            </div>
        </div>

		<div class="HomeCardOnoo card " style="width:100% !important;height:100% !important">
            <div style="min-height: unset !important;height: 40px  !important;width:100%;margin-right: 0px !important;"
                class="card card-SlickCarbon HomeCardOnooHeader">
                <label style="text-align: center;color: mintcream;font-size: 17px">
					@lang('menulang.warehouse')
                </label>
            </div>

            <div class="row rowcardheader">
				@can('product.view')
				<li class="button-dropul">
					<a href="{{ action('SellingPriceGroupController@index') }}">
						@lang('lang_v1.selling_price_group')
					</a>
				</li>
			@endcan

            <div class="row rowcardheader">
				@can('product.create')
				<li class="button-dropul">
					<a href="{{ action('VariationTemplateController@index') }}">
						@lang('product.variations')
					</a>
				</li>
			@endcan
			@can('product.view')
                    <li class="button-dropul">
                        <a href="{{ action('LabelsController@show') }}">
                            @lang('barcode.print_labels')
                        </a>
                    </li>
                @endcan

				@can('unit.view')
                    <li class="button-dropul">
                        <a href="{{ action('UnitController@index') }}">
                            @lang('unit.units')
                        </a>
                    </li>
                @endcan
				@can('category.view')
                    <li class="button-dropul">
                        <a href="{{ action('TaxonomyController@index') . '?type=product'}}">
                            @lang('category.categories')
                        </a>
                    </li>
                @endcan

				@can('brand.view')
                    <li class="button-dropul">
                        <a href="{{ action('BrandController@index') }}">
                            @lang('brand.brands')
                        </a>
                    </li>
                @endcan

				@can('product.view')
                    <li class="button-dropul">
                        <a href="{{ action('WarrantyController@index') }}">
                            @lang('lang_v1.warranties')
                        </a>
                    </li>
                @endcan



            </div>


        </div>

		<div class="HomeCardOnoo card " style="width:100% !important;height:100% !important">
            <div style="min-height: unset !important;height: 40px  !important;width:100%;margin-right: 0px !important;"
                class="card card-SlickCarbon HomeCardOnooHeader">
                <label style="text-align: center;color: mintcream;font-size: 17px">
					@lang('expense.expenses')
                </label>
            </div>

            <div class="row rowcardheader">
 @can('expense.add')
                    <li class="button-dropul">
                        <a href="{{ action('ExpenseCategoryController@index') }}">
                            @lang('expense.expense_categories')
                        </a>
                    </li>
                @endcan

            </div>


        </div>

		<div class="HomeCardOnoo card " style="width:100% !important;height:100% !important">
            <div style="min-height: unset !important;height: 40px  !important;width:100%;margin-right: 0px !important;"
                class="card card-SlickCarbon HomeCardOnooHeader">
                <label style="text-align: center;color: mintcream;font-size: 17px">
					@lang('lang_v1.importdata')
                </label>
            </div>

            <div class="row rowcardheader">
				@can('supplier.create')
				<li class="button-dropul">
					<a href="{{ action('ContactController@getImportContacts') }}">
						@lang('lang_v1.import_contacts')
					</a>
				</li>
			@endcan

			@can('product.create')
                    <li class="button-dropul">
                        <a href="{{ action('ImportProductsController@index') }}">
                            @lang('product.import_products')
                        </a>
                    </li>
                @endcan

				@can('product.create')
                    <li class="button-dropul">
                        <a href="{{ action('ImportOpeningStockController@index') }}">
                            @lang('lang_v1.import_opening_stock')
                        </a>
                    </li>
                @endcan

				@can('sell.create')
                    <li class="button-dropul">
                        <a href="{{ action('ImportSalesController@index') }}">
                            @lang('lang_v1.import_sales')
                        </a>
                    </li>
                @endcan

            </div>


        </div>
        <!-- End Onoo Services -End -->
    </div>
@endsection
