<?php

namespace App\Http\Middleware;

use App\Utils\ModuleUtil;
use Closure;
use Menu;
use Nwidart\Menus\Presenters\Bootstrap\NavbarPresenter;

class AdminSidebarMenu
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($request->ajax()) {
			return $next($request);
		}

		Menu::create('admin-sidebar-menu', function ($menu) {

			$enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];

			$common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
			$pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];

			$is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
			//Home
			$menu->url(action('HomeController@index'), __('home.home'), ['icon' => 'homeimg', 'active' => request()->segment(1) == 'home'])->order(0);

			//User management dropdown
			if (auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view') || auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own')) {
				$menu->dropdown(
					__('menulang.people'),
					function ($sub) {
						if (auth()->user()->can('user.view')) {
							$sub->url(
								action('ManageUserController@index'),
								__('user.users'),
								['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'users']
							);
						}
						if (auth()->user()->can('roles.view')) {
							$sub->url(
								action('RoleController@index'),
								__('user.roles'),
								['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(1) == 'roles']
							);
						}
						if (auth()->user()->can('user.create')) {
							$sub->url(
								action('SalesCommissionAgentController@index'),
								__('lang_v1.sales_commission_agents'),
								['icon' => 'fa fas fa-handshake', 'active' => request()->segment(1) == 'sales-commission-agents']
							);
						}
						if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own')) {
							$sub->url(
								action('ContactController@index', ['type' => 'supplier']),
								__('report.supplier'),
								['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'supplier']
							);
						}
						if (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own')) {
							$sub->url(
								action('ContactController@index', ['type' => 'customer']),
								__('report.customer'),
								['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'customer']
							);
						}


						if (!empty(env('GOOGLE_MAP_API_KEY'))) {
							$sub->url(
								action('ContactController@contactMap'),
								__('lang_v1.map'),
								['icon' => 'fa fas fa-map-marker-alt', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'map']
							);
						}
					},
					['icon' => 'peopleimg']
				)->order(1);
			}


			//Warehouse dropdown
			if (auth()->user()->can('product.view') || auth()->user()->can('product.create')) {
				$menu->dropdown(
					__('menulang.warehouse'),
					function ($sub) use ($enabled_modules, $common_settings) {
						if (auth()->user()->can('product.view')) {
							$sub->url(
								action('ProductController@index'),
								__('lang_v1.list_products'),
								['icon' => '', 'active' => request()->segment(1) == 'products' && request()->segment(2) == '']
							);
						}

						if (in_array('purchases', $enabled_modules)) {
							if (auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase')) {
								$sub->url(
									action('PurchaseController@index'),
									__('purchase.list_purchase'),
									['icon' => '', 'active' => request()->segment(1) == 'purchases' && request()->segment(2) == null]
								);
							}

							if (auth()->user()->can('purchase.update')) {
								$sub->url(
									action('PurchaseReturnController@index'),
									__('lang_v1.list_purchase_return'),
									['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'purchase-return']
								);
							}
						}

			//Stock transfer dropdown
						if (in_array('stock_transfers', $enabled_modules)){
							if (auth()->user()->can('purchase.view')) {
								$sub->url(
									action('StockTransferController@index'),
									__('lang_v1.list_stock_transfers'),
									['icon' => '', 'active' => request()->segment(1) == 'stock-transfers' && request()->segment(2) == null]
								);
							}
						}
			//stock adjustment dropdown
			if (in_array('stock_adjustment', $enabled_modules)){
				if (auth()->user()->can('purchase.view')) {
					$sub->url(
						action('StockAdjustmentController@index'),
						__('stock_adjustment.list'),
						['icon' => '', 'active' => request()->segment(1) == 'stock-adjustments' && request()->segment(2) == null]
					);
				}
			}

					},
					['icon' => 'warehouseimg']
				)->order(2);
			}


			//Sell dropdown
			if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping', 'access_sell_return', 'direct_sell.view', 'direct_sell.update', 'access_own_sell_return'])) {
				$menu->dropdown(
					__('sale.sale'),
					function ($sub) use ($enabled_modules, $is_admin, $pos_settings) {


						if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'direct_sell.view', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
							$sub->url(
								action('SellController@index'),
								__('lang_v1.all_sales'),
								['icon' => '', 'active' => request()->segment(1) == 'sells' && request()->segment(2) == null]
							);
						}

						if (auth()->user()->can('sell.create')) {
							if (in_array('pos_sale', $enabled_modules)) {
								if (auth()->user()->can('sell.view')) {
									$sub->url(
										action('SellPosController@index'),
										__('sale.list_pos'),
										['icon' => '', 'active' => request()->segment(1) == 'pos' && request()->segment(2) == null]
									);
								}
							}
						}



						if (auth()->user()->can('access_sell_return') || auth()->user()->can('access_own_sell_return')) {
							$sub->url(
								action('SellReturnController@index'),
								__('lang_v1.list_sell_return'),
								['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'sell-return' && request()->segment(2) == null]
							);
						}

						if ($is_admin || auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
							$sub->url(
								action('SellController@shipments'),
								__('lang_v1.shipments'),
								['icon' => 'fa fas fa-truck', 'active' => request()->segment(1) == 'shipments']
							);
						}

						if (auth()->user()->can('discount.access')) {
							$sub->url(
								action('DiscountController@index'),
								__('lang_v1.discounts'),
								['icon' => 'fa fas fa-percent', 'active' => request()->segment(1) == 'discount']
							);
						}
						if (in_array('subscription', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
							$sub->url(
								action('SellPosController@listSubscriptions'),
								__('lang_v1.subscriptions'),
								['icon' => 'fa fas fa-recycle', 'active' => request()->segment(1) == 'subscriptions']
							);
						}
					},
					['icon' => 'salesimg', 'id' => 'tour_step7']
				)->order(3);
			}



			//Expense dropdown
			if (in_array('expenses', $enabled_modules) && (auth()->user()->can('all_expense.access') || auth()->user()->can('view_own_expense'))) {
				$menu->dropdown(
					__('expense.expenses'),
					function ($sub) {
						$sub->url(
							action('ExpenseController@index'),
							__('lang_v1.list_expenses'),
							['icon' => '', 'active' => request()->segment(1) == 'expenses' && request()->segment(2) == null]
						);
					},
					['icon' => 'expenseimg']
				)->order(4);
			}
			//Accounts dropdown
			if (auth()->user()->can('account.access') && in_array('account', $enabled_modules)) {
				$menu->dropdown(
					__('lang_v1.payment_accounts'),
					function ($sub) {
						$sub->url(
							action('AccountController@index'),
							__('account.list_accounts'),
							['icon' => '', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'account']
						);
						$sub->url(
							action('AccountReportsController@balanceSheet'),
							__('account.balance_sheet'),
							['icon' => 'fa fas fa-book', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'balance-sheet']
						);
						$sub->url(
							action('AccountReportsController@trialBalance'),
							__('account.trial_balance'),
							['icon' => 'fa fas fa-balance-scale', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'trial-balance']
						);
						$sub->url(
							action('AccountController@cashFlow'),
							__('lang_v1.cash_flow'),
							['icon' => 'fa fas fa-exchange-alt', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'cash-flow']
						);
						$sub->url(
							action('AccountReportsController@paymentAccountReport'),
							__('account.payment_account_report'),
							['icon' => 'fa fas fa-file-alt', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'payment-account-report']
						);
					},
					['icon' => 'accountimg']
				)->order(6);
			}

			//Reports dropdown
			if (
				auth()->user()->can('purchase_n_sell_report.view') || auth()->user()->can('contacts_report.view')
				|| auth()->user()->can('stock_report.view') || auth()->user()->can('tax_report.view')
				|| auth()->user()->can('trending_product_report.view') || auth()->user()->can('sales_representative.view') || auth()->user()->can('register_report.view')
				|| auth()->user()->can('expense_report.view')
			) {
				$menu->dropdown(
					__('report.reports'),
					function ($sub) use ($enabled_modules, $is_admin) {
						if (auth()->user()->can('profit_loss_report.view')) {
							$sub->url(
								action('ReportController@getProfitLoss'),
								__('report.profit_loss'),
								['icon' => 'fa fas fa-file-invoice-dollar', 'active' => request()->segment(2) == 'profit-loss']
							);
						}
						if (config('constants.show_report_606') == true) {
							$sub->url(
								action('ReportController@purchaseReport'),
								'Report 606 (' . __('lang_v1.purchase') . ')',
								['icon' => 'fa fas fa-arrow-circle-down', 'active' => request()->segment(2) == 'purchase-report']
							);
						}
						if (config('constants.show_report_607') == true) {
							$sub->url(
								action('ReportController@saleReport'),
								'Report 607 (' . __('business.sale') . ')',
								['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'sale-report']
							);
						}
						if ((in_array('purchases', $enabled_modules) || in_array('add_sale', $enabled_modules) || in_array('pos_sale', $enabled_modules)) && auth()->user()->can('purchase_n_sell_report.view')) {
							$sub->url(
								action('ReportController@getPurchaseSell'),
								__('report.purchase_sell_report'),
								['icon' => 'fa fas fa-exchange-alt', 'active' => request()->segment(2) == 'purchase-sell']
							);
						}

						if (auth()->user()->can('tax_report.view')) {
							$sub->url(
								action('ReportController@getTaxReport'),
								__('report.tax_report'),
								['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'tax-report']
							);
						}
						if (auth()->user()->can('contacts_report.view')) {
							$sub->url(
								action('ReportController@getCustomerSuppliers'),
								__('report.contacts'),
								['icon' => 'fa fas fa-address-book', 'active' => request()->segment(2) == 'customer-supplier']
							);
							$sub->url(
								action('ReportController@getCustomerGroup'),
								__('lang_v1.customer_groups_report'),
								['icon' => 'fa fas fa-users', 'active' => request()->segment(2) == 'customer-group']
							);
						}
						if (auth()->user()->can('stock_report.view')) {
							$sub->url(
								action('ReportController@getStockReport'),
								__('report.stock_report'),
								['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'stock-report']
							);
							if (session('business.enable_product_expiry') == 1) {
								$sub->url(
									action('ReportController@getStockExpiryReport'),
									__('report.stock_expiry_report'),
									['icon' => 'fa fas fa-calendar-times', 'active' => request()->segment(2) == 'stock-expiry']
								);
							}
							if (session('business.enable_lot_number') == 1) {
								$sub->url(
									action('ReportController@getLotReport'),
									__('lang_v1.lot_report'),
									['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'lot-report']
								);
							}

							if (in_array('stock_adjustment', $enabled_modules)) {
								$sub->url(
									action('ReportController@getStockAdjustmentReport'),
									__('report.stock_adjustment_report'),
									['icon' => 'fa fas fa-sliders-h', 'active' => request()->segment(2) == 'stock-adjustment-report']
								);
							}
						}

						if (auth()->user()->can('trending_product_report.view')) {
							$sub->url(
								action('ReportController@getTrendingProducts'),
								__('report.trending_products'),
								['icon' => 'fa fas fa-chart-line', 'active' => request()->segment(2) == 'trending-products']
							);
						}

						if (auth()->user()->can('purchase_n_sell_report.view')) {
							$sub->url(
								action('ReportController@itemsReport'),
								__('lang_v1.items_report'),
								['icon' => 'fa fas fa-tasks', 'active' => request()->segment(2) == 'items-report']
							);

							$sub->url(
								action('ReportController@getproductPurchaseReport'),
								__('lang_v1.product_purchase_report'),
								['icon' => 'fa fas fa-arrow-circle-down', 'active' => request()->segment(2) == 'product-purchase-report']
							);

							$sub->url(
								action('ReportController@getproductSellReport'),
								__('lang_v1.product_sell_report'),
								['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'product-sell-report']
							);

							$sub->url(
								action('ReportController@purchasePaymentReport'),
								__('lang_v1.purchase_payment_report'),
								['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'purchase-payment-report']
							);

							$sub->url(
								action('ReportController@sellPaymentReport'),
								__('lang_v1.sell_payment_report'),
								['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'sell-payment-report']
							);
						}
						if (in_array('expenses', $enabled_modules) && auth()->user()->can('expense_report.view')) {
							$sub->url(
								action('ReportController@getExpenseReport'),
								__('report.expense_report'),
								['icon' => 'fa fas fa-search-minus', 'active' => request()->segment(2) == 'expense-report']
							);
						}
						if (auth()->user()->can('register_report.view')) {
							$sub->url(
								action('ReportController@getRegisterReport'),
								__('report.register_report'),
								['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(2) == 'register-report']
							);
						}
						if (auth()->user()->can('sales_representative.view')) {
							$sub->url(
								action('ReportController@getSalesRepresentativeReport'),
								__('report.sales_representative'),
								['icon' => 'fa fas fa-user', 'active' => request()->segment(2) == 'sales-representative-report']
							);
						}
						if (auth()->user()->can('purchase_n_sell_report.view') && in_array('tables', $enabled_modules)) {
							$sub->url(
								action('ReportController@getTableReport'),
								__('restaurant.table_report'),
								['icon' => 'fa fas fa-table', 'active' => request()->segment(2) == 'table-report']
							);
						}

						if (auth()->user()->can('sales_representative.view') && in_array('service_staff', $enabled_modules)) {
							$sub->url(
								action('ReportController@getServiceStaffReport'),
								__('restaurant.service_staff_report'),
								['icon' => 'fa fas fa-user-secret', 'active' => request()->segment(2) == 'service-staff-report']
							);
						}

						if ($is_admin) {
							$sub->url(
								action('ReportController@activityLog'),
								__('lang_v1.activity_log'),
								['icon' => 'fa fas fa-user-secret', 'active' => request()->segment(2) == 'activity-log']
							);
						}
					},
					['icon' => 'reportsimg', 'id' => 'tour_step8']
				)->order(7);
			}

			//Backup menu
			if (auth()->user()->can('backup')) {
				$menu->url(action('BackUpController@index'), __('lang_v1.backup'), ['icon' => 'backupimg', 'active' => request()->segment(1) == 'backup'])->order(99);
			}

			//Modules menu
			if (auth()->user()->can('manage_modules')) {
				$menu->url(action('Install\ModulesController@index'), __('lang_v1.modules'), ['icon' => 'modulesimg', 'active' => request()->segment(1) == 'manage-modules'])->order(98);
			}

			//Booking menu
			if (in_array('booking', $enabled_modules) && (auth()->user()->can('crud_all_bookings') || auth()->user()->can('crud_own_bookings'))) {
				$menu->url(action('Restaurant\BookingController@index'), __('restaurant.bookings'), ['icon' => 'bookingimg', 'active' => request()->segment(1) == 'bookings'])->order(5);
			}

			//Kitchen menu
			if (in_array('kitchen', $enabled_modules)) {
				$menu->url(action('Restaurant\KitchenController@index'), __('restaurant.kitchen'), ['icon' => 'kitchenimg', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'kitchen'])->order(5);
			}

			//Service Staff menu
			if (in_array('service_staff', $enabled_modules)) {
				$menu->url(action('Restaurant\OrderController@index'), __('restaurant.orders'), ['icon' => 'ordersimg', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'orders'])->order(5);
			}



			//Settings Dropdown
			if (
				auth()->user()->can('business_settings.access') ||
				auth()->user()->can('barcode_settings.access') ||
				auth()->user()->can('invoice_settings.access') ||
				auth()->user()->can('tax_rate.view') ||
				auth()->user()->can('tax_rate.create') ||
				auth()->user()->can('access_package_subscriptions')
			) {
				$menu->dropdown(
					__('business.settings'),
					function ($sub) use ($enabled_modules) {
						if (auth()->user()->can('business_settings.access')) {
							$sub->url(
								action('BusinessController@getBusinessSettings'),
								__('business.business_settings'),
								['icon' => 'fa fas fa-cogs', 'active' => request()->segment(1) == 'business', 'id' => "tour_step2"]
							);
							$sub->url(
								action('BusinessLocationController@index'),
								__('business.business_locations'),
								['icon' => 'fa fas fa-map-marker', 'active' => request()->segment(1) == 'business-location']
							);

						}
						if (auth()->user()->can('business_settings.access')) {
							$sub->url(
								action('BusinessController@getbasicBusinessSettings'),
								__('business.basicsetting'),
								['icon' => 'fa fas fa-cogs', 'active' => request()->segment(1) == "basicsetting"]
							);
						}
													//Notification template menu

						if (auth()->user()->can('send_notifications')) {
							$sub->url(
								action('NotificationTemplateController@index'),
								__('lang_v1.notification_templates'),
								['icon' => 'fa fas fa-envelope', 'active' => request()->segment(1) == 'notification-templates']
							);

						}

						if (auth()->user()->can('invoice_settings.access')) {
							$sub->url(
								action('InvoiceSchemeController@index'),
								__('invoice.invoice_settings'),
								['icon' => 'fa fas fa-file', 'active' => in_array(request()->segment(1), ['invoice-schemes', 'invoice-layouts'])]
							);
						}
						if (auth()->user()->can('barcode_settings.access')) {
							$sub->url(
								action('BarcodeController@index'),
								__('barcode.barcode_settings'),
								['icon' => 'fa fas fa-barcode', 'active' => request()->segment(1) == 'barcodes']
							);
						}
						if (auth()->user()->can('access_printers')) {
							$sub->url(
								action('PrinterController@index'),
								__('printer.receipt_printers'),
								['icon' => 'fa fas fa-share-alt', 'active' => request()->segment(1) == 'printers']
							);
						}

						if (auth()->user()->can('tax_rate.view') || auth()->user()->can('tax_rate.create')) {
							$sub->url(
								action('TaxRateController@index'),
								__('tax_rate.tax_rates'),
								['icon' => 'fa fas fa-bolt', 'active' => request()->segment(1) == 'tax-rates']
							);
						}

						if (in_array('tables', $enabled_modules) && auth()->user()->can('access_tables')) {
							$sub->url(
								action('Restaurant\TableController@index'),
								__('restaurant.tables'),
								['icon' => 'fa fas fa-table', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'tables']
							);
						}

						if (in_array('modifiers', $enabled_modules) && (auth()->user()->can('product.view') || auth()->user()->can('product.create'))) {
							$sub->url(
								action('Restaurant\ModifierSetsController@index'),
								__('restaurant.modifiers'),
								['icon' => 'fa fas fa-pizza-slice', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'modifiers']
							);
						}

						if (in_array('types_of_service', $enabled_modules) && auth()->user()->can('access_types_of_service')) {
							$sub->url(
								action('TypesOfServiceController@index'),
								__('lang_v1.types_of_service'),
								['icon' => 'fa fas fa-user-circle', 'active' => request()->segment(1) == 'types-of-service']
							);
						}
					},
					['icon' => 'settingimg', 'id' => 'tour_step3']
				)->order(8);
			}
		});

		//Add menus from modules
		$moduleUtil = new ModuleUtil;
		$moduleUtil->getModuleData('modifyAdminMenu');

		return $next($request);
	}
}
