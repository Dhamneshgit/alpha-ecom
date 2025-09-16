<?php

namespace App\Http\Controllers\Seller;

use App\CPU\BackEndHelper;
use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\CPU\OrderManager;
use App\Http\Controllers\Controller;
use App\Model\Admin;
use App\Model\AdminWallet;
use App\Model\BusinessSetting;
use App\Model\DeliveryMan;
use App\Model\DeliveryManTransaction;
use App\Model\DeliverymanWallet;
use App\Model\Warehouse;
use App\Model\Order;
use App\Model\Seller;
use App\Model\Plan;
use App\Model\Zipcode;
use App\Model\City;
use App\User;
use App\Model\OrderDetail;
use App\Model\ItemWeight;
use App\Model\Product;
use App\Model\SellerWallet;
use App\Model\ShippingAddress;
use App\Model\ShippingMethod;
use App\Traits\CommonTrait;
use Barryvdh\DomPDF\Facade as PDF;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use function App\CPU\translate;
use Rap2hpoutre\FastExcel\FastExcel;
use App\CPU\CustomerManager;
use App\CPU\Convert;

class OrderController extends Controller
{
    use CommonTrait;
    public function list(Request $request, $status)
    {
        $seller = auth('seller')->user();
        $sellerId = $seller->id;

        Order::where(['seller_id' => $sellerId,'checked' => 0])->update(['checked' => 1]);

        $seller_pos=\App\Model\BusinessSetting::where('type','seller_pos')->first()->value;

        $search = $request['search'];
        $filter = $request['filter'];
        $from = $request['from'];
        $to = $request['to'];
        $status = $request['status'];
        $key = $request['search'] ? explode(' ', $request['search']) : '';
        $delivery_man_id = $request['delivery_man_id'];

        $orders = Order::with(['customer','shipping','shippingAddress','delivery_man','billingAddress'])
            ->where('seller_is','seller')
            ->where(['seller_id'=>$sellerId])
            ->when($filter == 'POS', function ($q){
                $q->where('order_type', 'POS');
            })
            ->when($status !='all', function($q) use($status){
                $q->where(function($query) use ($status){
                    $query->orWhere('order_status',$status);
                });
            })
            ->when(!empty($from) && !empty($to),function($query) use($from,$to){
                $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            })
            ->when($request->has('search') && $search!=null,function ($q) use ($key) {
                $q->where(function($qq) use ($key){
                    foreach ($key as $value) {
                        $qq->where('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_ref', 'like', "%{$value}%");
                    }
                });
            })
            ->when($delivery_man_id, function ($q) use($delivery_man_id){
                $q->where(['delivery_man_id'=> $delivery_man_id, 'seller_is'=>'seller']);
            })
            ->latest()
            ->paginate(Helpers::pagination_limit())
            ->appends(['search'=>$request['search'],'filter'=>$request['filter'],'from'=>$request['from'],'to'=>$request['to'],'delivery_man_id'=>$request['delivery_man_id']]);

        $pending_query = Order::where(['seller_is'=>'seller','order_status'=>'pending','seller_id'=>$sellerId]);
        $pending = $this->common_query_status_count($pending_query, $request);

        $confirmed_query = Order::where(['seller_is'=>'seller','order_status'=>'confirmed','seller_id'=>$sellerId]);
        $confirmed = $this->common_query_status_count($confirmed_query, $request);

        $processing_query = Order::where(['seller_is'=>'seller','order_status'=>'processing','seller_id'=>$sellerId]);
        $processing = $this->common_query_status_count($processing_query, $request);

        $out_for_delivery_query = Order::where(['seller_is'=>'seller','order_status'=>'out_for_delivery','seller_id'=>$sellerId]);
        $out_for_delivery = $this->common_query_status_count($out_for_delivery_query, $request);

        $delivered_query = Order::where(['seller_is'=>'seller','order_status'=>'delivered','seller_id'=>$sellerId]);
        $delivered = $this->common_query_status_count($delivered_query, $request);

        $canceled_query = Order::where(['seller_is'=>'seller','order_status'=>'canceled','seller_id'=>$sellerId]);
        $canceled = $this->common_query_status_count($canceled_query, $request);

        $returned_query = Order::where(['seller_is'=>'seller','order_status'=>'returned','seller_id'=>$sellerId]);
        $returned = $this->common_query_status_count($returned_query, $request);

        $failed_query = Order::where(['seller_is'=>'seller','order_status'=>'failed','seller_id'=>$sellerId]);
        $failed = $this->common_query_status_count($failed_query, $request);

        return view(
            'seller-views.order.list',
            compact(
                'orders',
                'search','from','to',
                'status','sellerId',
                'filter',
                'pending',
                'confirmed',
                'processing',
                'out_for_delivery',
                'delivered',
                'canceled',
                'returned',
                'failed',
                'seller_pos',
                'seller'
            )
        );
    }

    public function common_query_status_count($query, $request){
        $search = $request['search'];
        $filter = $request['filter'];
        $from = $request['from'];
        $to = $request['to'];
        $key = $request['search'] ? explode(' ', $request['search']) : '';

        return $query->when($filter == 'POS', function ($q){
                $q->where('order_type', 'POS');
            })
            ->when(!empty($from) && !empty($to),function($query) use($from,$to){
                $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            })
            ->when($request->has('search') && $search!=null,function ($q) use ($key) {
                $q->where(function($qq) use ($key){
                    foreach ($key as $value) {
                        $qq->where('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_ref', 'like', "%{$value}%");
                    }
                });
            })->count();
    }

    public function details($id)
    {
        $sellerId = auth('seller')->id();
        $order = Order::with(['details' => function ($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        }])->with('customer', 'shipping')
            ->where('id', $id)->first();

        $itemWeight = ItemWeight::where('order_id', $id)->first();    

        $physical_product = false;
        foreach($order->details as $product){
            if(isset($product->product) && $product->product->product_type == 'physical'){
                $physical_product = true;
            }
        }

        $total_delivered = Order::where(['seller_id' => $sellerId, 'order_status' => 'delivered'])->count();

        $shipping_method = Helpers::get_business_settings('shipping_method');
        $delivery_men = DeliveryMan::where('is_active',1)->when($shipping_method == 'inhouse_shipping', function ($query) {
            $query->where(['seller_id' => 0]);
        })->when($shipping_method == 'sellerwise_shipping', function ($query) use ($order) {
            $query->where(['seller_id' => $order['seller_id']]);
        })->get();

        $shipping_address = ShippingAddress::find($order->shipping_address);

        if($order->order_type == 'default_type') {
            return view('seller-views.order.order-details', compact('shipping_address', 'order', 'itemWeight','delivery_men', 'shipping_method', 'total_delivered', 'physical_product'));
        }else{
            return view('seller-views.pos.order.order-details', compact('order', 'physical_product'));
        }
    }

    /**
     *  Digital file upload after sell
     */
    public function digital_file_upload_after_sell(Request $request)
    {
        $request->validate([
            'digital_file_after_sell'    => 'required|mimes:jpg,jpeg,png,gif,zip,pdf'
        ], [
            'digital_file_after_sell.required' => 'Digital file upload after sell is required',
            'digital_file_after_sell.mimes' => 'Digital file upload after sell upload must be a file of type: pdf, zip, jpg, jpeg, png, gif.',
        ]);

        $order_details = OrderDetail::find($request->order_id);
        $order_details->digital_file_after_sell = ImageManager::update('product/digital-product/', $order_details->digital_file_after_sell, $request->digital_file_after_sell->getClientOriginalExtension(), $request->file('digital_file_after_sell'));

        if($order_details->save()){
            Toastr::success('Digital file upload successfully!');
        }else{
            Toastr::error('Digital file upload failed!');
        }
        return back();
    }

    public function add_delivery_man($order_id, $delivery_man_id)
    {
        if ($delivery_man_id == 0) {
            return response()->json([], 401);
        }
        $order = Order::where(['seller_id' => auth('seller')->id(), 'id' => $order_id])->first();
        if($order->order_status == 'delivered') {
            return response()->json(['status' => false], 200);
        }
        $order->delivery_man_id = $delivery_man_id;
        $order->delivery_type = 'self_delivery';
        $order->delivery_service_name = null;
        $order->third_party_delivery_tracking_id = null;
        $order->save();

        $fcm_token = isset($order->delivery_man) ? $order->delivery_man->fcm_token : null;
        $value = Helpers::order_status_update_message('del_assign');
        if(!empty($fcm_token)) {
            try {
                if ($value) {
                    $data = [
                        'title' => translate('order'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                    ];
                    if ($order->delivery_man_id) {
                        self::add_deliveryman_push_notification($data, $order['delivery_man_id']);
                    }
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
            }
        }

        return response()->json(['status' => true], 200);
    }

    public function generate_invoice($id)
    {
        $company_phone =BusinessSetting::where('type', 'company_phone')->first()->value;
        $company_email =BusinessSetting::where('type', 'company_email')->first()->value;
        $company_name =BusinessSetting::where('type', 'company_name')->first()->value;
        $company_web_logo =BusinessSetting::where('type', 'company_web_logo')->first()->value;

        $sellerId = auth('seller')->id();
        $seller = Seller::find($sellerId)->gst;

        $order = Order::with(['details' => function ($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        }])->with('customer', 'shipping')
            ->with('seller')
            ->where('id', $id)->first();

        $data["email"] = $order->customer !=null?$order->customer["email"]:\App\CPU\translate('email_not_found');
        $data["client_name"] = $order->customer !=null? $order->customer["f_name"] . ' ' . $order->customer["l_name"]:\App\CPU\translate('customer_not_found');
        $data["order"] = $order;

//        return view('seller-views.order.invoice',compact('order', 'seller', 'company_phone', 'company_name', 'company_email', 'company_web_logo'));
      $mpdf_view = \View::make('seller-views.order.invoice', compact('order', 'seller', 'company_phone', 'company_email', 'company_name', 'company_web_logo'));
        Helpers::gen_mpdf($mpdf_view, 'order_invoice_', $order->id);
    }

    public function payment_status(Request $request)
    {
        if ($request->ajax()) {
            $order = Order::find($request->id);

            if(!isset($order->customer))
            {
                return response()->json(['customer_status'=>0],200);
            }

            $order = Order::find($request->id);
            $order->payment_status = $request->payment_status;
            $order->save();
            $data = $request->payment_status;
            return response()->json($data);
        }
    }

    public function status(Request $request)
    {
        $order = Order::find($request->id);

        if(!isset($order->customer))
        {
            return response()->json(['customer_status'=>0],200);
        }

        $wallet_status = Helpers::get_business_settings('wallet_status');
        $loyalty_point_status = Helpers::get_business_settings('loyalty_point_status');

        if($request->order_status=='delivered' && $order->payment_status !='paid'){

            return response()->json(['payment_status'=>0],200);
        }
        $fcm_token = isset($order->customer) ? $order->customer->cm_firebase_token : null;
        $value = Helpers::order_status_update_message($request->order_status);

        if ($order->order_status == 'delivered') {
            return response()->json(['success' => 0, 'message' => 'order is already delivered.'], 200);
        }
        if (!empty($fcm_token)) {
            try {
                if ($value) {
                    $data = [
                        'title' => translate('Order'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
                return response()->json([]);
            }
        }

        try {
            $fcm_token_delivery_man = $order->delivery_man->fcm_token;
            if ($request->order_status == 'canceled' && $value != null && !empty($fcm_token_delivery_man)) {
                $data = [
                    'title' => translate('order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                ];
                if($order->delivery_man_id) {
                    self::add_deliveryman_push_notification($data, $order['delivery_man_id']);
                }
                Helpers::send_push_notif_to_device($fcm_token_delivery_man, $data);
            }
        } catch (\Exception $e) {}


        $order->order_status = $request->order_status;
        OrderManager::stock_update_on_order_status_change($order, $request->order_status);

        if ($request->order_status == 'delivered' && $order['seller_id'] != null) {
            OrderManager::wallet_manage_on_order_status_change($order, 'seller');
            OrderDetail::where('order_id', $order->id)->update(
                ['delivery_status'=>'delivered']
            );
        }

        $order->save();

        if($wallet_status == 1 && $loyalty_point_status == 1)
        {
            if($request->order_status == 'delivered' && $order->payment_status =='paid'){
                CustomerManager::create_loyalty_point_transaction($order->customer_id, $order->id, Convert::default($order->order_amount-$order->shipping_cost), 'order_place');
            }
        }

        if ($order->delivery_man_id && $request->order_status == 'delivered') {
            $dm_wallet = DeliverymanWallet::where('delivery_man_id', $order->delivery_man_id)->first();
            $cash_in_hand = $order->payment_method == 'cash_on_delivery' ? $order->order_amount : 0;

            if (empty($dm_wallet)) {
                DeliverymanWallet::create([
                    'delivery_man_id' => $order->delivery_man_id,
                    'current_balance' => BackEndHelper::currency_to_usd($order->deliveryman_charge) ?? 0,
                    'cash_in_hand' => BackEndHelper::currency_to_usd($cash_in_hand),
                    'pending_withdraw' => 0,
                    'total_withdraw' => 0,
                ]);
            } else {
                $dm_wallet->current_balance += BackEndHelper::currency_to_usd($order->deliveryman_charge) ?? 0;
                $dm_wallet->cash_in_hand += BackEndHelper::currency_to_usd($cash_in_hand);
                $dm_wallet->save();
            }

            if($order->deliveryman_charge && $request->order_status == 'delivered'){
                DeliveryManTransaction::create([
                    'delivery_man_id' => $order->delivery_man_id,
                    'user_id' => auth('seller')->id(),
                    'user_type' => 'seller',
                    'credit' => BackEndHelper::currency_to_usd($order->deliveryman_charge) ?? 0,
                    'transaction_id' => Uuid::uuid4(),
                    'transaction_type' => 'deliveryman_charge'
                ]);
            }
        }

        if ($request->order_status == 'delivered'){
            $seller_data = Seller::find(auth('seller')->id());
            $maintainBalance = DB::table('business_settings')->where('type','minimum_seller_wallet')->first();
            if(!empty($seller_data->sales_commission_percentage) && $maintainBalance->value < $seller_data->wallet_maintain){
                
                $sellerCommission = $seller_data->sales_commission_percentage;
                $adminCommission = ($sellerCommission/100) * $order->order_amount;

                // dd('amount-'.$order->admin_commission.'commission-'.$adminCommission.'sellercommission-'.$seller_data->sales_commission_percentage,);

                $seller_data->wallet_maintain =  $seller_data->wallet_maintain - round($adminCommission,2);
                $seller_data->save();
                
                $userdata = User::find($order->customer_id);
        
                if((!empty($userdata->plan_id)) && ($userdata->plan_expire_date > date('Y-m-d'))){
        
                    $plan_data = Plan::find($userdata->plan_id);
                    $remainingAmount = $adminCommission;
                    
                    // $frenchise = Admin::where('zipcode',$userdata->zipcode)->where('admin_role_id', 2)->first();
                    // $admin = Admin::where('admin_role_id', 1)->first();
                    // $shopData = Seller::where('zipcode',$userdata->zipcode)->first();
                    $frenchise = Admin::where('zipcode',$userdata->zipcode)->where('admin_role_id', 2)->first();
                    $zipcode = Zipcode::where('zipcode',$userdata->zipcode)->first();
                    $city = City::find($zipcode->city_id);
                    $admin = Admin::where('admin_role_id', 1)->first();
                    
                    $customerBonus = (5/100) * $adminCommission;
                    $customerSelfBonus = ($plan_data->self_purchase_bonus/100) * $adminCommission;
                    $frenchiseBonus = ($plan_data->repurchase_frenchise_bonus/100) * $adminCommission;
                    $districtBonus = ($plan_data->repurchase_district_bonus/100) * $adminCommission;
                    $stateBonus = ($plan_data->repurchase_state_bonus/100) * $adminCommission;
                   
                    $district = Admin::where('city_id', $userdata->city_id)->first();
                    if($district){
                        $district->user_bonus = $district->user_bonus + $districtBonus;
                        $district->save();
                        Helpers::repurchaseTransactions($district->id, $order->customer_id, $districtBonus, 'district', $userdata->zipcode, 'district_repurchase_bonus','Credit');
                    }  
                    
                    $state = Admin::where('state', $city->state)->first();
                    if($state){
                        $state->user_bonus = $state->user_bonus + $stateBonus;
                        $state->save();
        
                        Helpers::repurchaseTransactions($state->id, $order->customer_id, $stateBonus, 'state', $userdata->zipcode, 'state_repurchase_bonus','Credit');
                    }

                    $userdata->wallet_balance = $userdata->wallet_balance - $customerBonus; 
                    $userdata->withdrawal_wallet = $userdata->withdrawal_wallet + round(($customerBonus + $customerSelfBonus),2); 
                    $userdata->save();       
            
            
                    $d1 = Helpers::repurchaseTransactions($userdata->id, null, $customerBonus, 'user', $userdata->zipcode, 'bonus', 'Debit');
                    $d2 = Helpers::repurchaseTransactions($userdata->id, null, $customerSelfBonus, 'user', $userdata->zipcode, 'self_purchase_bonus','Credit');
                    
                    if($frenchise){
                        $frenchise->user_bonus = $frenchise->user_bonus + $frenchiseBonus;
                        $frenchise->save();
                
                        $d3 = Helpers::repurchaseTransactions($frenchise->id, $order->customer_id, $frenchiseBonus, 'frenchise', $userdata->zipcode, 'frenchise_repurchase_bonus');
                    }
            
                    $remainingAmount = $remainingAmount - ($customerBonus + $customerSelfBonus + $frenchiseBonus);
                    $remainingAmount = sprintf("%.2f",$remainingAmount); 
        
                    $data = Helpers::get_bonus_referral($order->customer_id, 1, $remainingAmount);
                    
                }

            }
        }

        CommonTrait::add_order_status_history($request->id, auth('seller')->id(), $request->order_status, 'seller');

        $data = $request->order_status;
        return response()->json($data);
    }

    public function amount_date_update(Request $request){
        $field_name = $request->field_name;
        $field_val = $request->field_val;
        $user_id = auth('seller')->id();

        $order = Order::find($request->order_id);
        $order->$field_name = $field_val;

        try {
            DB::beginTransaction();

            if($field_name == 'expected_delivery_date'){
                CommonTrait::add_expected_delivery_date_history($request->order_id, $user_id, $field_val, 'seller');
            }
            $order->save();

            DB::commit();
        }catch(\Exception $ex){
            DB::rollback();
            return response()->json(['status' => false], 403);
        }

        $fcm_token = isset($order->delivery_man) ? $order->delivery_man->fcm_token : null;
        if($field_name == 'expected_delivery_date' && !empty($fcm_token)) {
            $value = Helpers::order_status_update_message($field_name) . " ID: " . $order['id'];
            try {
                if ($value != null) {
                    $data = [
                        'title' => translate('order'),
                        'description' => $value,
                        'order_id' => $order['id'],
                        'image' => '',
                    ];

                    if ($order->delivery_man_id) {
                        self::add_deliveryman_push_notification($data, $order->delivery_man_id);
                    }
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => false], 200);
            }
        }
        return response()->json(['status' => true], 200);
    }

    public function update_deliver_info(Request $request)
    {
        $order = Order::find($request->order_id);
        if(!$order->seller->warehouse_id){
            Toastr::warning(\App\CPU\translate('first_update_seller_warehouse_id'));
            return back(); 
        }
        $check = ItemWeight::where('order_id', $request->order_id)->first();
        if(!$check){

            $dimensional_factor = 4000;

            $volumetric_weight = ($request->length * $request->width * $request->height) / $dimensional_factor;
            $volumetric_weight = round($volumetric_weight, 2);

            $item = new ItemWeight();
            $item->length = $request->length;
            $item->width = $request->width;
            $item->height = $request->height;
            $item->order_id = $request->order_id;
            $item->volumetric_weight = $volumetric_weight ?? $request->weight;
            $item->save();

            $data = Helpers::send_to_shipmozo($request->order_id, $volumetric_weight ?? $request->weight);

            $data = json_decode($data);
            // dd($data);
            if($data->result == 1){
                $order->delivery_type = 'third_party_delivery';
                $order->delivery_service_name = 'Shipmozo';
                $order->third_party_delivery_tracking_id = $data->data->order_id;
                $order->delivery_man_id = null;
                $order->deliveryman_charge = 0;
                $order->expected_delivery_date = null;
                $order->save();

                Toastr::success(\App\CPU\translate($data->data->Info.' on shipmozo'));
                return back();
            }

            $delete = ItemWeight::where('order_id', $request->order_id)->delete();

            Toastr::warning(\App\CPU\translate($data->data->error));
            return back();


        }

        // $order = Order::find($request->order_id);
        // $order->delivery_type = 'third_party_delivery';
        // $order->delivery_service_name = $request->delivery_service_name;
        // $order->third_party_delivery_tracking_id = $request->third_party_delivery_tracking_id;
        // $order->delivery_man_id = null;
        // $order->deliveryman_charge = 0;
        // $order->expected_delivery_date = null;
        // $order->save();

        // Toastr::success(\App\CPU\translate('updated_successfully!'));
        // return back();
    }

    public function add_warehouse(Request $request)
    {
        
        $check = Warehouse::where('seller_id', $request->seller_id)->first();
        if(!$check){
            $item = new Warehouse();
            $item->seller_id = $request->seller_id;
            $item->address_title = $request->address_title;
            $item->name = $request->name;
            $item->phone = $request->phone;
            $item->alternate_phone = $request->alternate_phone;
            $item->email = $request->email;
            $item->address_line_one = $request->address_line_one;
            $item->address_line_two = $request->address_line_two;
            $item->pin_code = $request->pin_code;
            $item->save();

            $data = Helpers::create_warehouse($request->address_title, $request->name, $request->phone, $request->alternate_phone, $request->email, $request->address_line_one, $request->address_line_two, $request->pin_code);

            $data = json_decode($data);
            if($data->result == 1){
                
                $seller = Seller::find($request->seller_id);
                $seller->warehouse_id = $data->data->warehouse_id;
                $seller->save();

                Toastr::success(\App\CPU\translate('Success'));
                return back();
            }

            $delete = Warehouse::where('seller_id', $request->seller_id)->delete();

            Toastr::warning(\App\CPU\translate($data->message));
            return back();

        }

        Toastr::success(\App\CPU\translate('success'));
        return back();
       
    }
    public function bulk_export_data(Request $request, $status)
    {
        $sellerId = auth('seller')->id();

        $search = $request['search'];
        $filter = $request['filter'];
        $from = $request['from'];
        $to = $request['to'];
        $status = $request['status'];
        $delivery_man_id = $request['delivery_man_id'];

        $key = $request['search'] ? explode(' ', $request['search']) : '';

        $orders = Order::with(['customer','shipping','shippingAddress','delivery_man','billingAddress'])
            ->where('seller_is','seller')
            ->where(['seller_id'=>$sellerId])
            ->when($filter == 'POS', function ($q){
                $q->where('order_type', 'POS');
            })
            ->when($request->has('delivery_man_id') && $delivery_man_id, function($query) use($delivery_man_id){
                $query->where('delivery_man_id', $delivery_man_id);
            })
            ->when($status !='all', function($q) use($status){
                $q->where(function($query) use ($status){
                    $query->orWhere('order_status',$status)
                        ->orWhere('payment_status',$status);
                });
            })
            ->when(!empty($from) && !empty($to),function($query) use($from,$to){
                $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            })
            ->when($request->has('search') && $search!=null,function ($q) use ($key) {
                $q->where(function($qq) use ($key){
                    foreach ($key as $value) {
                        $qq->where('id', 'like', "%{$value}%")
                            ->orWhere('order_status', 'like', "%{$value}%")
                            ->orWhere('transaction_ref', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->get();

        if ($orders->count()==0) {
            Toastr::warning(\App\CPU\translate('Data is Not available !!!'));
            return back();
        }

        $storage = [];

        foreach ($orders as $item) {

            $order_amount = $item->order_amount;
            $discount_amount = $item->discount_amount;
            $shipping_cost = $item->shipping_cost;
            $extra_discount = $item->extra_discount;

            if($item->order_status == 'processing'){
                $order_status = 'packaging';
            }elseif($item->order_status == 'failed'){
                $order_status = 'Failed To Deliver';
            }else{
                $order_status = $item->order_status;
            }

            $storage[] = [
                'order_id'=>$item->id,
                'Customer Id' => $item->customer_id,
                'Customer Name'=> isset($item->customer) ? $item->customer->f_name. ' '.$item->customer->l_name:'not found',
                'Order Group Id' => $item->order_group_id,
                'Order Status' => $order_status,
                'Order Amount' => Helpers::currency_converter($order_amount),
                'Order Type' => $item->order_type,
                'Coupon Code' => $item->coupon_code,
                'Discount Amount' => Helpers::currency_converter($discount_amount),
                'Discount Type' => $item->discount_type,
                'Extra Discount' => Helpers::currency_converter($extra_discount),
                'Extra Discount Type' => $item->extra_discount_type,
                'Payment Status' => $item->payment_status,
                'Payment Method' => $item->payment_method,
                'Transaction_ref' => $item->transaction_ref,
                'Verification Code' => $item->verification_code,
                'Billing Address' => isset($item->billingAddress)? $item->billingAddress->address:'not found',
                'Billing Address Data' => $item->billing_address_data,
                'Shipping Type' => $item->shipping_type,
                'Shipping Address' => isset($item->shippingAddress)? $item->shippingAddress->address:'not found',
                'Shipping Method Id' => $item->shipping_method_id,
                'Shipping Method Name' => isset($item->shipping)? $item->shipping->title:'not found',
                'Shipping Cost' => Helpers::currency_converter($shipping_cost),
                'Seller Id' => $item->seller_id,
                'Seller Name' => isset($item->seller)? $item->seller->f_name. ' '.$item->seller->l_name:'not found',
                'Seller Email'  => isset($item->seller)? $item->seller->email:'not found',
                'Seller Phone'  => isset($item->seller)? $item->seller->phone:'not found',
                'Seller Is' => $item->seller_is,
                'Shipping Address Data' => $item->shipping_address_data,
                'Delivery Type' => $item->delivery_type,
                'Delivery Man Id' => $item->delivery_man_id,
                'Delivery Service Name' => $item->delivery_service_name,
                'Third Party Delivery Tracking Id' => $item->third_party_delivery_tracking_id,
                'Checked' => $item->checked,

            ];
        }


            return (new FastExcel($storage))->download('Order_All_details.xlsx');

    }
}
