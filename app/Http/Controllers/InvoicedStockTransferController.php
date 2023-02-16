<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Media;
use App\PurchaseLine;
use App\Transaction;
use App\TransactionSellLinesPurchaseLines;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Datatables;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use App\Utils\CashRegisterUtil;
use App\Account;
use App\Brands;
use App\Business;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\TransactionSellLine;
use App\TypesOfService;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\NotificationUtil;
use App\Variation;
use App\Warranty;
use App\InvoiceLayout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\InvoiceScheme;
use App\SalesOrderController;
use Razorpay\Api\Api;
use App\TransactionPayment;
use Stripe\Charge;
use Stripe\Stripe;


class InvoicedStockTransferController extends Controller
{

  /**
   * All Utils instance.
   *
   */
  protected $contactUtil;
  protected $productUtil;
  protected $transactionUtil;
  protected $moduleUtil;
  protected $cashRegisterUtil;

  /**
   * Constructor
   *
   * @param ProductUtils $product
   * @return void
   */
  public function __construct(ContactUtil $contactUtil, ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, CashRegisterUtil $cashRegisterUtil)
  {
    $this->contactUtil = $contactUtil;
    $this->productUtil = $productUtil;
    $this->transactionUtil = $transactionUtil;
    $this->moduleUtil = $moduleUtil;
    $this->cashRegisterUtil = $cashRegisterUtil;
    $this->status_colors = [
      'in_transit' => 'bg-yellow',
      'completed' => 'bg-green',
      'pending' => 'bg-red',
    ];

    $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
      'is_return' => 0, 'transaction_no' => ''];
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create')) {
      abort(403, 'Unauthorized action.');
    }

    $statuses = $this->stockTransferStatuses();

    if (request()->ajax()) {
      $business_id = request()->session()->get('user.business_id');
      $edit_days = request()->session()->get('business.transaction_edit_days');

      $stock_transfers = Transaction::join(
        'business_locations AS l1',
        'transactions.location_id',
        '=',
        'l1.id'
      )
        ->join('transactions as t2', 't2.transfer_parent_id', '=', 'transactions.id')
        ->join(
          'business_locations AS l2',
          't2.location_id',
          '=',
          'l2.id'
        )
        ->where('transactions.business_id', $business_id)
        ->where('transactions.type', 'sell_transfer')
        ->select(
          'transactions.id',
          'transactions.transaction_date',
          'transactions.ref_no',
          'l1.name as location_from',
          'l2.name as location_to',
          'transactions.final_total',
          'transactions.shipping_charges',
          'transactions.additional_notes',
          'transactions.id as DT_RowId',
          'transactions.status'
        );

      return Datatables::of($stock_transfers)
        ->addColumn('action', function ($row) use ($edit_days) {
          $html = '<button type="button" title="' . __("stock_adjustment.view_details") . '" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal" data-href="' . action('InvoicedStockTransferController@show', [$row->id]) . '"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</button>';

          $html .= ' <a href="#" class="print-invoice btn btn-info btn-xs" data-href="' . action('InvoicedStockTransferController@printInvoice', [$row->id]) . '"><i class="fa fa-print" aria-hidden="true"></i> ' . __("messages.print") . '</a>';

          $date = \Carbon::parse($row->transaction_date)
            ->addDays($edit_days);
          $today = today();

          if ($date->gte($today)) {
            $html .= '&nbsp;
                        <button type="button" data-href="' . action("InvoicedStockTransferController@destroy", [$row->id]) . '" class="btn btn-danger btn-xs delete_stock_transfer"><i class="fa fa-trash" aria-hidden="true"></i> ' . __("messages.delete") . '</button>';
          }

          if ($row->status != 'final') {
            $html .= '&nbsp;
                        <a href="' . action("InvoicedStockTransferController@edit", [$row->id]) . '" class="btn btn-primary btn-xs"><i class="fa fa-edit" aria-hidden="true"></i> ' . __("messages.edit") . '</a>';
          }

          return $html;
        })
        ->editColumn(
          'final_total',
          '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>'
        )
        ->editColumn(
          'shipping_charges',
          '<span class="display_currency" data-currency_symbol="true">{{$shipping_charges}}</span>'
        )
        ->editColumn('status', function ($row) use ($statuses) {
          $row->status = $row->status == 'final' ? 'completed' : $row->status;
          $status = $statuses[$row->status];
          $status_color = !empty($this->status_colors[$row->status]) ? $this->status_colors[$row->status] : 'bg-gray';
          $status = $row->status != 'completed' ? '<a href="#" class="stock_transfer_status" data-status="' . $row->status . '" data-href="' . action("InvoicedStockTransferController@updateStatus", [$row->id]) . '"><span class="label ' . $status_color . '">' . $statuses[$row->status] . '</span></a>' : '<span class="label ' . $status_color . '">' . $statuses[$row->status] . '</span>';

          return $status;
        })
        ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
        ->rawColumns(['final_total', 'action', 'shipping_charges', 'status'])
        ->setRowAttr([
          'data-href' => function ($row) {
            return action('InvoicedStockTransferController@show', [$row->id]);
          }])
        ->make(true);
    }

    return view('stock_transfer.index')->with(compact('statuses'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    if (!auth()->user()->can('purchase.create')) {
      abort(403, 'Unauthorized action.');
    }

    $business_id = request()->session()->get('user.business_id');

    //Check if subscribed or not
    if (!$this->moduleUtil->isSubscribed($business_id)) {
      return $this->moduleUtil->expiredResponse(action('InvoicedStockTransferController@index'));
    }

    $business_locations = BusinessLocation::forDropdown($business_id);

    $statuses = $this->stockTransferStatuses();

    return view('stock_transfer.invoiced_create')
      ->with(compact('business_locations', 'statuses'));
  }

  private function stockTransferStatuses()
  {
    return [
      'pending' => __('lang_v1.pending'),
      'in_transit' => __('lang_v1.in_transit'),
      'completed' => __('restaurant.completed')
    ];
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */

  public function sell(Request $request)
  {
    if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('so.create')) {
      abort(403, 'Unauthorized action.');
    }

    $is_direct_sale = false;
    if (!empty($request->input('is_direct_sale'))) {
      $is_direct_sale = true;
    }

    //Check if there is a open register, if no then redirect to Create Register screen.
    if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
      return redirect()->action('CashRegisterController@create');
    }

    try {
      $input = $request->except('_token');

      $input['is_quotation'] = 0;
      //status is sent as quotation from Add sales screen.
      if ($input['status'] == 'quotation') {
        $input['status'] = 'draft';
        $input['is_quotation'] = 1;
        $input['sub_status'] = 'quotation';
      } else if ($input['status'] == 'proforma') {
        $input['status'] = 'draft';
        $input['sub_status'] = 'proforma';
      }

      //Add change return
      $change_return = $this->dummyPaymentLine;
      if (!empty($input['payment']['change_return'])) {
        $change_return = $input['payment']['change_return'];
        unset($input['payment']['change_return']);
      }


      // End of not important
      $products = $request->input('products');
      if (!empty($products)) {
        $business_id = $request->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
          return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
          return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
        }
        // Important
        $user_id = $request->session()->get('user.id');

//        $discount = ['discount_type' => $input['discount_type'],
//          'discount_amount' => $input['discount_amount']
//        ];

        // My discount type and amount edit
        $discount = ['discount_type' => 'fixed',
          'discount_amount' => 0.00
        ];


//        $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

        $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], 'None', $discount);

        // End of Important
        // Important
        DB::beginTransaction();

        if (empty($request->input('transaction_date'))) {
          $input['transaction_date'] = \Carbon::now();
        } else {
          $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
        }
        if (!$is_direct_sale) {
          $input['is_direct_sale'] = 1;
        }
        // End of Important

        // Important
        //Set commission agent
        $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
        $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');
        if ($commsn_agnt_setting == 'logged_in_user') {
          $input['commission_agent'] = $user_id;
        }

        if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
          $input['exchange_rate'] = 1;
        }

        //Customer group details

//        $contact_id = $request->get('contact_id', null);
        // Static contact_id
        $contact_id = 3;
        // Not important
//        $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
//        $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;
//
//        //set selling price group id
//        $price_group_id = $request->has('price_group') ? $request->input('price_group') : null;
//
//        //If default price group for the location exists
//        $price_group_id = $price_group_id == 0 && $request->has('default_price_group') ? $request->input('default_price_group') : $price_group_id;
//
//        $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend'] ? 1 : 0;
//        if ($input['is_suspend']) {
//          $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
//        }
//
//        //Generate reference number
//        if (!empty($input['is_recurring'])) {
//          //Update reference count
//          $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
//          $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);
//        }
        // End of not important
        // Imortant
        if (!empty($request->input('invoice_scheme_id'))) {
          $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
        }
        // End of important
        // Not important
        //Types of service
//        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
//          $input['types_of_service_id'] = $request->input('types_of_service_id');
//          $price_group_id = !empty($request->input('types_of_service_price_group')) ? $request->input('types_of_service_price_group') : $price_group_id;
//          $input['packing_charge'] = !empty($request->input('packing_charge')) ?
//            $this->transactionUtil->num_uf($request->input('packing_charge')) : 0;
//          $input['packing_charge_type'] = $request->input('packing_charge_type');
//          $input['service_custom_field_1'] = !empty($request->input('service_custom_field_1')) ?
//            $request->input('service_custom_field_1') : null;
//          $input['service_custom_field_2'] = !empty($request->input('service_custom_field_2')) ?
//            $request->input('service_custom_field_2') : null;
//          $input['service_custom_field_3'] = !empty($request->input('service_custom_field_3')) ?
//            $request->input('service_custom_field_3') : null;
//          $input['service_custom_field_4'] = !empty($request->input('service_custom_field_4')) ?
//            $request->input('service_custom_field_4') : null;
//          $input['service_custom_field_5'] = !empty($request->input('service_custom_field_5')) ?
//            $request->input('service_custom_field_5') : null;
//          $input['service_custom_field_6'] = !empty($request->input('service_custom_field_6')) ?
//            $request->input('service_custom_field_6') : null;
//        }
//
//        if ($request->input('additional_expense_value_1') != '') {
//          $input['additional_expense_key_1'] = $request->input('additional_expense_key_1');
//          $input['additional_expense_value_1'] = $request->input('additional_expense_value_1');
//        }
//
//        if ($request->input('additional_expense_value_2') != '') {
//          $input['additional_expense_key_2'] = $request->input('additional_expense_key_2');
//          $input['additional_expense_value_2'] = $request->input('additional_expense_value_2');
//        }
//
//        if ($request->input('additional_expense_value_3') != '') {
//          $input['additional_expense_key_3'] = $request->input('additional_expense_key_3');
//          $input['additional_expense_value_3'] = $request->input('additional_expense_value_3');
//        }
//
//        if ($request->input('additional_expense_value_4') != '') {
//          $input['additional_expense_key_4'] = $request->input('additional_expense_key_4');
//          $input['additional_expense_value_4'] = $request->input('additional_expense_value_4');
//        }
//
//        $input['selling_price_group_id'] = $price_group_id;
//
//        if ($this->transactionUtil->isModuleEnabled('tables')) {
//          $input['res_table_id'] = request()->get('res_table_id');
//        }
//        if ($this->transactionUtil->isModuleEnabled('service_staff')) {
//          $input['res_waiter_id'] = request()->get('res_waiter_id');
//        }
//
//        //upload document
//        $input['document'] = $this->transactionUtil->uploadFile($request, 'sell_document', 'documents');
        // End Not important

        // Important
        $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);

        //Upload Shipping documents
        Media::uploadMedia($business_id, $transaction, $request, 'shipping_documents', false, 'shipping_document');


        $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);

        $change_return['amount'] = $input['change_return'] ?? 0;
        $change_return['is_return'] = 1;

        $input['payment'][] = $change_return;

        $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1 ? true : false;
        // Not important
        if (!$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
          $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
        }
// End of Not important

        //Check for final and do some processing.
        if ($input['status'] == 'final') {
          //update product stock

          // Important
          foreach ($input['products'] as $product) {
            $decrease_qty = $this->productUtil
              ->num_uf($product['quantity']);
            if (!empty($product['base_unit_multiplier'])) {
              $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
            }
            // End of Important

            // Not imporatnt
            if ($product['enable_stock']) {
              $this->productUtil->decreaseProductQuantity(
                $product['product_id'],
                $product['variation_id'],
                $input['location_id'],
                $decrease_qty
              );
            }

            if ($product['product_type'] == 'combo') {
              //Decrease quantity of combo as well.
              $this->productUtil
                ->decreaseProductQuantityCombo(
                  $product['combo'],
                  $input['location_id']
                );
            }
            // End of Not Important
          }

          //Add payments to Cash Register
          // Important
          if (!$is_direct_sale && !$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
            $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
          }

          //Update payment status
          $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

          $transaction->payment_status = $payment_status;
          // End of Important
          // Not important
          if ($request->session()->get('business.enable_rp') == 1) {
            $redeemed = !empty($input['rp_redeemed']) ? $input['rp_redeemed'] : 0;
            $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, 0, $redeemed);
          }
          // end pf  Not important
          //Allocate the quantity from purchase and add mapping of
          //purchase & sell lines in
          //transaction_sell_lines_purchase_lines table

          $business_details = $this->businessUtil->getDetails($business_id);
          //Not important
          $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
          //End of Not important
          // Important
          $business = ['id' => $business_id,
            'accounting_method' => $request->session()->get('business.accounting_method'),
            'location_id' => $input['location_id'],
            'pos_settings' => $pos_settings
          ];
          $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

          //Auto send notification
          $whatsapp_link = $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
        }
        // End of Important
        // Not important
        if (!empty($transaction->sales_order_ids)) {
          $this->transactionUtil->updateSalesOrderStatus($transaction->sales_order_ids);
        }

        $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);

        Media::uploadMedia($business_id, $transaction, $request, 'documents');

        $this->transactionUtil->activityLog($transaction, 'added');
        //End of  Not important

        // Important
        DB::commit();

        if ($request->input('is_save_and_print') == 1) {
          $url = $this->transactionUtil->getInvoiceUrl($transaction->id, $business_id);
          return redirect()->to($url . '?print_on_load=true');
        }

        $msg = trans("sale.pos_sale_added");
        $receipt = '';
        $invoice_layout_id = $request->input('invoice_layout_id');
        $print_invoice = false;
        if (!$is_direct_sale) {
          if ($input['status'] == 'draft') {
            $msg = trans("sale.draft_added");

            if ($input['is_quotation'] == 1) {
              $msg = trans("lang_v1.quotation_added");
              $print_invoice = true;
            }
          } elseif ($input['status'] == 'final') {
            $print_invoice = true;
          }
        }

        if ($transaction->is_suspend == 1 && empty($pos_settings['print_on_suspend'])) {
          $print_invoice = false;
        }

        if (!auth()->user()->can("print_invoice")) {
          $print_invoice = false;
        }

        if ($print_invoice) {
          $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
        }

        $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];

        if (!empty($whatsapp_link)) {
          $output['whatsapp_link'] = $whatsapp_link;
        }
      } else {
        $output = ['success' => 0,
          'msg' => trans("messages.something_went_wrong")
        ];
      }
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
      $msg = trans("messages.something_went_wrong");

      if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
        $msg = $e->getMessage();
      }
      if (get_class($e) == \App\Exceptions\AdvanceBalanceNotAvailable::class) {
        $msg = $e->getMessage();
      }

      $output = ['success' => 0,
        'msg' => $msg
      ];
    }

    if (!$is_direct_sale) {
      return $output;
    } else {
      if ($input['status'] == 'draft') {
        if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
          return redirect()
            ->action('SellController@getQuotations')
            ->with('status', $output);
        } else {
          return redirect()
            ->action('SellController@getDrafts')
            ->with('status', $output);
        }
      } elseif ($input['status'] == 'quotation') {
        return redirect()
          ->action('SellController@getQuotations')
          ->with('status', $output);
      } elseif (isset($input['type']) && $input['type'] == 'sales_order') {
        return redirect()
          ->action('SalesOrderController@index')
          ->with('status', $output);
      } else {
        if (!empty($input['sub_type']) && $input['sub_type'] == 'repair') {
          $redirect_url = $input['print_label'] == 1 ? action('\Modules\Repair\Http\Controllers\RepairController@printLabel', [$transaction->id]) : action('\Modules\Repair\Http\Controllers\RepairController@index');
          return redirect($redirect_url)
            ->with('status', $output);
        }
        return redirect()
          ->action('SellController@index')
          ->with('status', $output);
      }
    }
    // End Important

  }

  public function store(Request $request)
  {

    $this->sell($request);

  }


  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    if (!auth()->user()->can('purchase.view')) {
      abort(403, 'Unauthorized action.');
    }

    $business_id = request()->session()->get('user.business_id');

    $sell_transfer = Transaction::where('business_id', $business_id)
      ->where('id', $id)
      ->where('type', 'sell_transfer')
      ->with(
        'contact',
        'sell_lines',
        'sell_lines.product',
        'sell_lines.variations',
        'sell_lines.variations.product_variation',
        'sell_lines.lot_details',
        'sell_lines.sub_unit',
        'location',
        'sell_lines.product.unit'
      )
      ->first();

    foreach ($sell_transfer->sell_lines as $key => $value) {
      if (!empty($value->sub_unit_id)) {
        $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);

        $sell_transfer->sell_lines[$key] = $formated_sell_line;
      }
    }

    $purchase_transfer = Transaction::where('business_id', $business_id)
      ->where('transfer_parent_id', $sell_transfer->id)
      ->where('type', 'purchase_transfer')
      ->first();

    $location_details = ['sell' => $sell_transfer->location, 'purchase' => $purchase_transfer->location];

    $lot_n_exp_enabled = false;
    if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
      $lot_n_exp_enabled = true;
    }

    $statuses = $this->stockTransferStatuses();

    $statuses['final'] = __('restaurant.completed');

    $activities = Activity::forSubject($sell_transfer)
      ->with(['causer', 'subject'])
      ->latest()
      ->get();

    return view('stock_transfer.show')
      ->with(compact('sell_transfer', 'location_details', 'lot_n_exp_enabled', 'statuses', 'activities'));
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */

  public function destroy($id)
  {
    if (!auth()->user()->can('purchase.delete')) {
      abort(403, 'Unauthorized action.');
    }
    try {
      if (request()->ajax()) {
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
          return ['success' => 0,
            'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])];
        }

        //Get sell transfer transaction
        $sell_transfer = Transaction::where('id', $id)
          ->where('type', 'sell_transfer')
          ->with(['sell_lines'])
          ->first();

        //Get purchase transfer transaction
        $purchase_transfer = Transaction::where('transfer_parent_id', $sell_transfer->id)
          ->where('type', 'purchase_transfer')
          ->with(['purchase_lines'])
          ->first();

        //Check if any transfer stock is deleted and delete purchase lines
        $purchase_lines = $purchase_transfer->purchase_lines;
        foreach ($purchase_lines as $purchase_line) {
          if ($purchase_line->quantity_sold > 0) {
            return ['success' => 0,
              'msg' => __('lang_v1.stock_transfer_cannot_be_deleted')
            ];
          }
        }

        DB::beginTransaction();
        //Get purchase lines from transaction_sell_lines_purchase_lines and decrease quantity_sold
        $sell_lines = $sell_transfer->sell_lines;
        $deleted_sell_purchase_ids = [];
        $products = []; //variation_id as array

        foreach ($sell_lines as $sell_line) {
          $purchase_sell_line = TransactionSellLinesPurchaseLines::where('sell_line_id', $sell_line->id)->first();

          if (!empty($purchase_sell_line)) {
            //Decrease quntity sold from purchase line
            PurchaseLine::where('id', $purchase_sell_line->purchase_line_id)
              ->decrement('quantity_sold', $sell_line->quantity);

            $deleted_sell_purchase_ids[] = $purchase_sell_line->id;

            //variation details
            if (isset($products[$sell_line->variation_id])) {
              $products[$sell_line->variation_id]['quantity'] += $sell_line->quantity;
              $products[$sell_line->variation_id]['product_id'] = $sell_line->product_id;
            } else {
              $products[$sell_line->variation_id]['quantity'] = $sell_line->quantity;
              $products[$sell_line->variation_id]['product_id'] = $sell_line->product_id;
            }
          }
        }

        //Update quantity available in both location
        if (!empty($products)) {
          foreach ($products as $key => $value) {
            //Decrease from location 2
            $this->productUtil->decreaseProductQuantity(
              $products[$key]['product_id'],
              $key,
              $purchase_transfer->location_id,
              $products[$key]['quantity']
            );

            //Increase in location 1
            $this->productUtil->updateProductQuantity(
              $sell_transfer->location_id,
              $products[$key]['product_id'],
              $key,
              $products[$key]['quantity']
            );
          }
        }

        //Delete sale line purchase line
        if (!empty($deleted_sell_purchase_ids)) {
          TransactionSellLinesPurchaseLines::whereIn('id', $deleted_sell_purchase_ids)
            ->delete();
        }

        //Delete both transactions
        $sell_transfer->delete();
        $purchase_transfer->delete();

        $output = ['success' => 1,
          'msg' => __('lang_v1.stock_transfer_delete_success')
        ];
        DB::commit();
      }
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

      $output = ['success' => 0,
        'msg' => __('messages.something_went_wrong')
      ];
    }
    return $output;
  }

  /**
   * Checks if ref_number and supplier combination already exists.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function printInvoice($id)
  {
    try {
      $business_id = request()->session()->get('user.business_id');

      $sell_transfer = Transaction::where('business_id', $business_id)
        ->where('id', $id)
        ->where('type', 'sell_transfer')
        ->with(
          'contact',
          'sell_lines',
          'sell_lines.product',
          'sell_lines.variations',
          'sell_lines.variations.product_variation',
          'sell_lines.lot_details',
          'location',
          'sell_lines.product.unit'
        )
        ->first();

      $purchase_transfer = Transaction::where('business_id', $business_id)
        ->where('transfer_parent_id', $sell_transfer->id)
        ->where('type', 'purchase_transfer')
        ->first();

      $location_details = ['sell' => $sell_transfer->location, 'purchase' => $purchase_transfer->location];

      $lot_n_exp_enabled = false;
      if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
        $lot_n_exp_enabled = true;
      }


      $output = ['success' => 1, 'receipt' => [], 'print_title' => $sell_transfer->ref_no];
      $output['receipt']['html_content'] = view('stock_transfer.print', compact('sell_transfer', 'location_details', 'lot_n_exp_enabled'))->render();
    } catch (\Exception $e) {
      \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

      $output = ['success' => 0,
        'msg' => __('messages.something_went_wrong')
      ];
    }

    return $output;
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
//    public function edit($id)
//    {
//        $business_id = request()->session()->get('user.business_id');
//
//        $business_locations = BusinessLocation::forDropdown($business_id);
//
//        $statuses = $this->stockTransferStatuses();
//
//        $sell_transfer = Transaction::where('business_id', $business_id)
//            ->where('type', 'sell_transfer')
//            ->where('status', '!=', 'final')
//            ->with(['sell_lines'])
//            ->findOrFail($id);
//
//        $purchase_transfer = Transaction::where('business_id',
//            $business_id)
//            ->where('transfer_parent_id', $id)
//            ->where('status', '!=', 'received')
//            ->where('type', 'purchase_transfer')
//            ->first();
//
//        $products = [];
//        foreach ($sell_transfer->sell_lines as $sell_line) {
//            $product = $this->productUtil->getDetailsFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id);
//            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);
//            $product->sub_unit_id = $sell_line->sub_unit_id;
//            $product->quantity_ordered = $sell_line->quantity;
//            $product->transaction_sell_lines_id = $sell_line->id;
//            $product->lot_no_line_id = $sell_line->lot_no_line_id;
//
//            $product->unit_details = $this->productUtil->getSubUnits($business_id, $product->unit_id);
//
//            //Get lot number dropdown if enabled
//            $lot_numbers = [];
//            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
//                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($sell_line->variation_id, $business_id, $sell_transfer->location_id, true);
//                foreach ($lot_number_obj as $lot_number) {
//                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
//                    $lot_numbers[] = $lot_number;
//                }
//            }
//            $product->lot_numbers = $lot_numbers;
//
//            $products[] = $product;
//        }
//
//        return view('stock_transfer.edit')
//            ->with(compact('sell_transfer', 'purchase_transfer', 'business_locations', 'statuses', 'products'));
//    }

  public function edit($id)
  {
    $business_id = request()->session()->get('user.business_id');

    if (!(auth()->user()->can('superadmin') || auth()->user()->can('sell.update') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('repair.update')))) {
      abort(403, 'Unauthorized action.');
    }

    //Check if the transaction can be edited or not.
    $edit_days = request()->session()->get('business.transaction_edit_days');
    if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
      return back()
        ->with('status', ['success' => 0,
          'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
    }

    //Check if there is a open register, if no then redirect to Create Register screen.
    if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
      return redirect()->action('CashRegisterController@create');
    }

    //Check if return exist then not allowed
    if ($this->transactionUtil->isReturnExist($id)) {
      return back()->with('status', ['success' => 0,
        'msg' => __('lang_v1.return_exist')]);
    }

    $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

    $business_details = $this->businessUtil->getDetails($business_id);

    $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

    $transaction = Transaction::where('business_id', $business_id)
      ->where('type', 'sell')
      ->with(['price_group', 'types_of_service'])
      ->findorfail($id);

    $location_id = $transaction->location_id;
    $business_location = BusinessLocation::find($location_id);
    $payment_types = $this->productUtil->payment_types($business_location, true);
    $location_printer_type = $business_location->receipt_printer_type;
    $sell_details = TransactionSellLine::
    join(
      'products AS p',
      'transaction_sell_lines.product_id',
      '=',
      'p.id'
    )
      ->join(
        'variations AS variations',
        'transaction_sell_lines.variation_id',
        '=',
        'variations.id'
      )
      ->join(
        'product_variations AS pv',
        'variations.product_variation_id',
        '=',
        'pv.id'
      )
      ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
        $join->on('variations.id', '=', 'vld.variation_id')
          ->where('vld.location_id', '=', $location_id);
      })
      ->leftjoin('units', 'units.id', '=', 'p.unit_id')
      ->leftjoin('units as u', 'p.secondary_unit_id', '=', 'u.id')
      ->where('transaction_sell_lines.transaction_id', $id)
      ->with(['warranties'])
      ->select(
        DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
        'p.id as product_id',
        'p.enable_stock',
        'p.name as product_actual_name',
        'p.type as product_type',
        'pv.name as product_variation_name',
        'pv.is_dummy as is_dummy',
        'variations.name as variation_name',
        'variations.sub_sku',
        'p.barcode_type',
        'p.enable_sr_no',
        'variations.id as variation_id',
        'units.short_name as unit',
        'units.allow_decimal as unit_allow_decimal',
        'u.short_name as second_unit',
        'transaction_sell_lines.secondary_unit_quantity',
        'transaction_sell_lines.tax_id as tax_id',
        'transaction_sell_lines.item_tax as item_tax',
        'transaction_sell_lines.unit_price as default_sell_price',
        'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
        'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
        'transaction_sell_lines.id as transaction_sell_lines_id',
        'transaction_sell_lines.id',
        'transaction_sell_lines.quantity as quantity_ordered',
        'transaction_sell_lines.sell_line_note as sell_line_note',
        'transaction_sell_lines.parent_sell_line_id',
        'transaction_sell_lines.lot_no_line_id',
        'transaction_sell_lines.line_discount_type',
        'transaction_sell_lines.line_discount_amount',
        'transaction_sell_lines.res_service_staff_id',
        'units.id as unit_id',
        'transaction_sell_lines.sub_unit_id',
        DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
      )
      ->get();
    if (!empty($sell_details)) {
      foreach ($sell_details as $key => $value) {

        //If modifier or combo sell line then unset
        if (!empty($sell_details[$key]->parent_sell_line_id)) {
          unset($sell_details[$key]);
        } else {
          if ($transaction->status != 'final') {
            $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
            $sell_details[$key]->qty_available = $actual_qty_avlbl;
            $value->qty_available = $actual_qty_avlbl;
          }

          $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

          //Add available lot numbers for dropdown to sell lines
          $lot_numbers = [];
          if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
            $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
            foreach ($lot_number_obj as $lot_number) {
              //If lot number is selected added ordered quantity to lot quantity available
              if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                $lot_number->qty_available += $value->quantity_ordered;
              }

              $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
              $lot_numbers[] = $lot_number;
            }
          }
          $sell_details[$key]->lot_numbers = $lot_numbers;

          if (!empty($value->sub_unit_id)) {
            $value = $this->productUtil->changeSellLineUnit($business_id, $value);
            $sell_details[$key] = $value;
          }

          $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

          if ($this->transactionUtil->isModuleEnabled('modifiers')) {
            //Add modifier details to sel line details
            $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
              ->where('children_type', 'modifier')
              ->get();
            $modifiers_ids = [];
            if (count($sell_line_modifiers) > 0) {
              $sell_details[$key]->modifiers = $sell_line_modifiers;
              foreach ($sell_line_modifiers as $sell_line_modifier) {
                $modifiers_ids[] = $sell_line_modifier->variation_id;
              }
            }
            $sell_details[$key]->modifiers_ids = $modifiers_ids;

            //add product modifier sets for edit
            $this_product = Product::find($sell_details[$key]->product_id);
            if (count($this_product->modifier_sets) > 0) {
              $sell_details[$key]->product_ms = $this_product->modifier_sets;
            }
          }

          //Get details of combo items
          if ($sell_details[$key]->product_type == 'combo') {
            $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
              ->where('children_type', 'combo')
              ->get()
              ->toArray();
            if (!empty($sell_line_combos)) {
              $sell_details[$key]->combo_products = $sell_line_combos;
            }

            //calculate quantity available if combo product
            $combo_variations = [];
            foreach ($sell_line_combos as $combo_line) {
              $combo_variations[] = [
                'variation_id' => $combo_line['variation_id'],
                'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
                'unit_id' => null
              ];
            }
            $sell_details[$key]->qty_available =
              $this->productUtil->calculateComboQuantity($location_id, $combo_variations);

            if ($transaction->status == 'final') {
              $sell_details[$key]->qty_available = $sell_details[$key]->qty_available + $sell_details[$key]->quantity_ordered;
            }

            $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
          }
        }
      }
    }

    $featured_products = $business_location->getFeaturedProducts();

    $payment_lines = $this->transactionUtil->getPaymentDetails($id);
    //If no payment lines found then add dummy payment line.
    if (empty($payment_lines)) {
      $payment_lines[] = $this->dummyPaymentLine;
    }

    $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
    $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

    $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
    $commission_agent = [];
    if ($commsn_agnt_setting == 'user') {
      $commission_agent = User::forDropdown($business_id, false);
    } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
      $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
    }

    //If brands, category are enabled then send else false.
    $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
    $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::forDropdown($business_id)
      ->prepend(__('lang_v1.all_brands'), 'all') : false;

    $change_return = $this->dummyPaymentLine;

    $types = [];
    if (auth()->user()->can('supplier.create')) {
      $types['supplier'] = __('report.supplier');
    }
    if (auth()->user()->can('customer.create')) {
      $types['customer'] = __('report.customer');
    }
    if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
      $types['both'] = __('lang_v1.both_supplier_customer');
    }
    $customer_groups = CustomerGroup::forDropdown($business_id);

    //Accounts
    $accounts = [];
    if ($this->moduleUtil->isModuleEnabled('account')) {
      $accounts = Account::forDropdown($business_id, true, false, true);
    }

    $waiters = [];
    if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
      $waiters_enabled = true;
      $waiters = $this->productUtil->serviceStaffDropdown($business_id);
    }
    $redeem_details = [];
    if (request()->session()->get('business.enable_rp') == 1) {
      $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

      $redeem_details['points'] += $transaction->rp_redeemed;
      $redeem_details['points'] -= $transaction->rp_earned;
    }

    $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
    $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
    $shipping_statuses = $this->transactionUtil->shipping_statuses();

    $warranties = $this->__getwarranties();
    $sub_type = request()->get('sub_type');

    //pos screen view from module
    $pos_module_data = $this->moduleUtil->getModuleData('get_pos_screen_view', ['sub_type' => $sub_type]);

    $invoice_schemes = [];
    $default_invoice_schemes = null;

    if ($transaction->status == 'draft') {
      $invoice_schemes = InvoiceScheme::forDropdown($business_id);
      $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
    }

    $invoice_layouts = InvoiceLayout::forDropdown($business_id);

    $customer_due = $this->transactionUtil->getContactDue($transaction->contact_id, $transaction->business_id);

    $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';

    //Added check because $users is of no use if enable_contact_assign if false
    $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

    return view('sale_pos.edit')
      ->with(compact('business_details', 'taxes', 'payment_types', 'walk_in_customer', 'sell_details', 'transaction', 'payment_lines', 'location_printer_type', 'shortcuts', 'commission_agent', 'categories', 'pos_settings', 'change_return', 'types', 'customer_groups', 'brands', 'accounts', 'waiters', 'redeem_details', 'edit_price', 'edit_discount', 'shipping_statuses', 'warranties', 'sub_type', 'pos_module_data', 'invoice_schemes', 'default_invoice_schemes', 'invoice_layouts', 'featured_products', 'customer_due', 'users'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    if (!auth()->user()->can('purchase.create')) {
      abort(403, 'Unauthorized action.');
    }

    try {
      $business_id = $request->session()->get('user.business_id');

      //Check if subscribed or not
      if (!$this->moduleUtil->isSubscribed($business_id)) {
        return $this->moduleUtil->expiredResponse(action('InvoicedStockTransferController@create'));
      }

      $business_id = request()->session()->get('user.business_id');

      $sell_transfer = Transaction::where('business_id', $business_id)
        ->where('type', 'sell_transfer')
        ->findOrFail($id);

      $sell_transfer_before = $sell_transfer->replicate();

      $purchase_transfer = Transaction::where('business_id',
        $business_id)
        ->where('transfer_parent_id', $id)
        ->where('type', 'purchase_transfer')
        ->with(['purchase_lines'])
        ->first();

      $status = $request->input('status');

      DB::beginTransaction();

      $input_data = $request->only(['transaction_date', 'additional_notes', 'shipping_charges', 'final_total']);
      $status = $request->input('status');

      $input_data['total_before_tax'] = $input_data['final_total'];

      $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date'], true);
      $input_data['shipping_charges'] = $this->productUtil->num_uf($input_data['shipping_charges']);
      $input_data['status'] = $status == 'completed' ? 'final' : $status;

      $products = $request->input('products');
      $sell_lines = [];
      $purchase_lines = [];
      $edited_purchase_lines = [];
      if (!empty($products)) {
        foreach ($products as $product) {
          $sell_line_arr = [
            'product_id' => $product['product_id'],
            'variation_id' => $product['variation_id'],
            'quantity' => $this->productUtil->num_uf($product['quantity']),
            'item_tax' => 0,
            'tax_id' => null];

          if (!empty($product['product_unit_id'])) {
            $sell_line_arr['product_unit_id'] = $product['product_unit_id'];
          }
          if (!empty($product['sub_unit_id'])) {
            $sell_line_arr['sub_unit_id'] = $product['sub_unit_id'];
          }

          $purchase_line_arr = $sell_line_arr;

          if (!empty($product['base_unit_multiplier'])) {
            $sell_line_arr['base_unit_multiplier'] = $product['base_unit_multiplier'];
          }

          $sell_line_arr['unit_price'] = $this->productUtil->num_uf($product['unit_price']);

          $pp = 100;
          $sell_line_arr['unit_price_inc_tax'] = $this->productUtil->num_uf($pp);
//          $sell_line_arr['unit_price_inc_tax'] = $sell_line_arr['unit_price'];

          $purchase_line_arr['purchase_price'] = $sell_line_arr['unit_price'];
          $purchase_line_arr['purchase_price_inc_tax'] = $sell_line_arr['unit_price'];
          if (isset($product['transaction_sell_lines_id'])) {
            $sell_line_arr['transaction_sell_lines_id'] = $product['transaction_sell_lines_id'];
          }

          if (!empty($product['lot_no_line_id'])) {
            //Add lot_no_line_id to sell line
            $sell_line_arr['lot_no_line_id'] = $product['lot_no_line_id'];

            //Copy lot number and expiry date to purchase line
            $lot_details = PurchaseLine::find($product['lot_no_line_id']);
            $purchase_line_arr['lot_number'] = $lot_details->lot_number;
            $purchase_line_arr['mfg_date'] = $lot_details->mfg_date;
            $purchase_line_arr['exp_date'] = $lot_details->exp_date;
          }

          if (!empty($product['base_unit_multiplier'])) {
            $purchase_line_arr['quantity'] = $purchase_line_arr['quantity'] * $product['base_unit_multiplier'];
            $purchase_line_arr['purchase_price'] = $purchase_line_arr['purchase_price'] / $product['base_unit_multiplier'];
            $purchase_line_arr['purchase_price_inc_tax'] = $purchase_line_arr['purchase_price_inc_tax'] / $product['base_unit_multiplier'];
          }

          if (isset($purchase_line_arr['sub_unit_id']) && $purchase_line_arr['sub_unit_id'] == $purchase_line_arr['product_unit_id']) {
            unset($purchase_line_arr['sub_unit_id']);
          }
          unset($purchase_line_arr['product_unit_id']);

          $sell_lines[] = $sell_line_arr;

          $purchase_line = [];
          //check if purchase_line for the variation exists else create new
          foreach ($purchase_transfer->purchase_lines as $pl) {
            if ($pl->variation_id == $purchase_line_arr['variation_id']) {
              $pl->update($purchase_line_arr);
              $edited_purchase_lines[] = $pl->id;
              $purchase_line = $pl;
              break;
            }
          }
          if (empty($purchase_line)) {
            $purchase_line = new PurchaseLine($purchase_line_arr);
          }

          $purchase_lines[] = $purchase_line;
        }
      }

      //Create Sell Transfer transaction
      $sell_transfer->update($input_data);
      $sell_transfer->save();

      //Create Purchase Transfer at transfer location
      $input_data['status'] = $status == 'completed' ? 'received' : $status;

      $purchase_transfer->update($input_data);
      $purchase_transfer->save();

      //Sell Product from first location
      if (!empty($sell_lines)) {
        $this->transactionUtil->createOrUpdateSellLines($sell_transfer, $sell_lines, $sell_transfer->location_id, false, 'draft', [], false);
      }

      //Purchase product in second location
      if (!empty($purchase_lines)) {
        if (!empty($edited_purchase_lines)) {
          PurchaseLine::where('transaction_id', $purchase_transfer->id)
            ->whereNotIn('id', $edited_purchase_lines)
            ->delete();
        }
        $purchase_transfer->purchase_lines()->saveMany($purchase_lines);
      }

      //Decrease product stock from sell location
      //And increase product stock at purchase location
      if ($status == 'completed') {
        foreach ($products as $product) {
          if ($product['enable_stock']) {

            $decrease_qty = $this->productUtil
              ->num_uf($product['quantity']);
            if (!empty($product['base_unit_multiplier'])) {
              $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
            }

            $this->productUtil->decreaseProductQuantity(
              $product['product_id'],
              $product['variation_id'],
              $sell_transfer->location_id,
              $decrease_qty
            );

            $this->productUtil->updateProductQuantity(
              $purchase_transfer->location_id,
              $product['product_id'],
              $product['variation_id'],
              $decrease_qty,
              0,
              null,
              false
            );
          }
        }

        //Adjust stock over selling if found
        $this->productUtil->adjustStockOverSelling($purchase_transfer);

        //Map sell lines with purchase lines
        $business = ['id' => $business_id,
          'accounting_method' => $request->session()->get('business.accounting_method'),
          'location_id' => $sell_transfer->location_id
        ];
        $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase');
      }

      $this->transactionUtil->activityLog($sell_transfer, 'edited', $sell_transfer_before);

      $output = ['success' => 1,
        'msg' => __('lang_v1.updated_succesfully')
      ];

      DB::commit();
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

      $output = ['success' => 0,
        'msg' => $e->getMessage()
      ];
    }

    return redirect('stock-transfers')->with('status', $output);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function updateStatus(Request $request, $id)
  {
    if (!auth()->user()->can('purchase.update')) {
      abort(403, 'Unauthorized action.');
    }

    try {
      $business_id = request()->session()->get('user.business_id');

      $sell_transfer = Transaction::where('business_id', $business_id)
        ->where('type', 'sell_transfer')
        ->with(['sell_lines', 'sell_lines.product'])
        ->findOrFail($id);

      $purchase_transfer = Transaction::where('business_id',
        $business_id)
        ->where('transfer_parent_id', $id)
        ->where('type', 'purchase_transfer')
        ->with(['purchase_lines'])
        ->first();

      $status = $request->input('status');

      DB::beginTransaction();
      if ($status == 'completed' && $sell_transfer->status != 'completed') {

        foreach ($sell_transfer->sell_lines as $sell_line) {
          if ($sell_line->product->enable_stock) {
            $this->productUtil->decreaseProductQuantity(
              $sell_line->product_id,
              $sell_line->variation_id,
              $sell_transfer->location_id,
              $sell_line->quantity
            );

            $this->productUtil->updateProductQuantity(
              $purchase_transfer->location_id,
              $sell_line->product_id,
              $sell_line->variation_id,
              $sell_line->quantity,
              0,
              null,
              false
            );
          }
        }

        //Adjust stock over selling if found
        $this->productUtil->adjustStockOverSelling($purchase_transfer);

        //Map sell lines with purchase lines
        $business = ['id' => $business_id,
          'accounting_method' => $request->session()->get('business.accounting_method'),
          'location_id' => $sell_transfer->location_id
        ];
        $this->transactionUtil->mapPurchaseSell($business, $sell_transfer->sell_lines, 'purchase');
      }
      $purchase_transfer->status = $status == 'completed' ? 'received' : $status;
      $purchase_transfer->save();
      $sell_transfer->status = $status == 'completed' ? 'final' : $status;
      $sell_transfer->save();

      DB::commit();

      $output = ['success' => 1,
        'msg' => __('lang_v1.updated_succesfully')
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

      $output = ['success' => 0,
        'msg' => "File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage()
      ];
    }

    return $output;
  }
}
