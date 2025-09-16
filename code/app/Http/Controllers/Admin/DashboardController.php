<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\AdminWallet;
use App\Model\Brand;
use App\Model\Order;
use App\Model\Booking;
use App\Model\OrderDetail;
use App\Model\OrderTransaction;
use App\Model\Product;
use App\Model\SellerWallet;
use App\Model\SellerWalletHistory;
use App\Model\RepurchaseTransaction;
use Brian2694\Toastr\Facades\Toastr;
use App\Model\Shop;
use App\Model\Admin;
use App\Model\WithdrawRequest;
use App\Model\WithdrawalMethod;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // dd(auth('admin')->id());
        $top_sell = OrderDetail::with(['product'])
            ->select('product_id', DB::raw('SUM(qty) as count'))
            ->where('delivery_status', 'delivered')
            ->groupBy('product_id')
            ->orderBy("count", 'desc')
            ->take(6)
            ->get();

        $most_rated_products = Product::rightJoin('reviews', 'reviews.product_id', '=', 'products.id')
            ->groupBy('product_id')
            ->select(['product_id',
                DB::raw('AVG(reviews.rating) as ratings_average'),
                DB::raw('count(*) as total')
            ])
            ->orderBy('total', 'desc')
            ->take(6)
            ->get();


        $top_store_by_earning = SellerWallet::select('seller_id', DB::raw('SUM(total_earning) as count'))
            ->whereHas('seller', function ($query){
                return $query;
            })
            ->groupBy('seller_id')
            ->orderBy("count", 'desc')
            ->take(6)
            ->get();

        $top_customer = Order::with(['customer'])
            ->select('customer_id', DB::raw('COUNT(customer_id) as count'))
            ->whereHas('customer',function ($q){
                $q->where('id','!=',0);
            })
            ->groupBy('customer_id')
            ->orderBy("count", 'desc')
            ->take(6)
            ->get();

        $top_store_by_order_received = Order::whereHas('seller', function ($query){
                return $query;
            })
            ->where('seller_is', 'seller')
            ->select('seller_id', DB::raw('COUNT(id) as count'))
            ->groupBy('seller_id')
            ->orderBy("count", 'desc')
            ->take(6)
            ->get();

        $top_deliveryman = Order::with(['delivery_man'])
            ->select('delivery_man_id', DB::raw('COUNT(delivery_man_id) as count'))
            ->where(['seller_is'=> 'admin','order_status'=>'delivered'])
            ->whereNotNull('delivery_man_id')
            ->groupBy('delivery_man_id')
            ->orderBy("count", 'desc')
            ->take(6)
            ->get();

        $from = Carbon::now()->startOfYear()->format('Y-m-d');
        $to = Carbon::now()->endOfYear()->format('Y-m-d');

        $inhouse_data = [];
        $inhouse_earning = OrderTransaction::where([
            'seller_is' => 'admin',
            'status' => 'disburse'
        ])->select(
            DB::raw('IFNULL(sum(seller_amount),0) as sums'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month')
        )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();
        for ($inc = 1; $inc <= 12; $inc++) {
            $inhouse_data[$inc] = 0;
            foreach ($inhouse_earning as $match) {
                if ($match['month'] == $inc) {
                    $inhouse_data[$inc] = $match['sums'];
                }
            }
        }

        $seller_data = [];
        $seller_earnings = OrderTransaction::where([
            'seller_is' => 'seller',
            'status' => 'disburse'
        ])->select(
            DB::raw('IFNULL(sum(seller_amount),0) as sums'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month')
        )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();
        for ($inc = 1; $inc <= 12; $inc++) {
            $seller_data[$inc] = 0;
            foreach ($seller_earnings as $match) {
                if ($match['month'] == $inc) {
                    $seller_data[$inc] = $match['sums'];
                }
            }
        }

        $commission_data = [];
        $commission_earnings = OrderTransaction::where([
            'status' => 'disburse'
        ])->select(
            DB::raw('IFNULL(sum(admin_commission),0) as sums'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month')
        )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();
        for ($inc = 1; $inc <= 12; $inc++) {
            $commission_data[$inc] = 0;
            foreach ($commission_earnings as $match) {
                if ($match['month'] == $inc) {
                    $commission_data[$inc] = $match['sums'];
                }
            }
        }

        $admin = Admin::find(auth('admin')->id());
        
        $data = self::order_stats_data();
        $currentUserId = auth('admin')->id();

        $manufacturer_id = '';
        if (auth('admin')->user()->role->name == 'Manufacturer') {
            $manufacturer_id = $currentUserId;
            $data = self::order_stats_data_manufacturer($manufacturer_id);
        }
        $aggregator_id = '';
        if (auth('admin')->user()->role->name == 'Aggregator') {
            $aggregator_id = $currentUserId;
            $data = self::order_stats_data_aggregator($aggregator_id);
        }
        $home_visit = '';
        if (auth('admin')->user()->role->name == 'Home Checkup') {
            $home_visit = $currentUserId;
            $data = self::order_stats_data_home_visit($home_visit);
        }
        $doctor_id = '';
        if (auth('admin')->user()->role->name == 'Doctor') {
            $doctor_id = $currentUserId;
            $data = self::order_stats_data_doctor($doctor_id);
        }



        $data['order'] = Order::count();
        $data['brand'] = Brand::count();

        $data['user_bonus'] = $admin->user_bonus;
        $data['top_sell'] = $top_sell;
        $data['most_rated_products'] = $most_rated_products;
        $data['top_store_by_earning'] = $top_store_by_earning;
        $data['top_customer'] = $top_customer;
        $data['top_store_by_order_received'] = $top_store_by_order_received;
        $data['top_deliveryman'] = $top_deliveryman;

        $admin_wallet = AdminWallet::where('admin_id', 1)->first();
        $data['inhouse_earning'] = $admin_wallet!=null?$admin_wallet->inhouse_earning:0;
        $data['commission_earned'] = $admin_wallet!=null?$admin_wallet->commission_earned:0;
        $data['delivery_charge_earned'] = $admin_wallet!=null?$admin_wallet->delivery_charge_earned:0;
        $data['pending_amount'] = $admin_wallet!=null?$admin_wallet->pending_amount:0;
        $data['total_tax_collected'] = $admin_wallet!=null?$admin_wallet->total_tax_collected:0;

        $withdrawal_methods = WithdrawalMethod::ofStatus(1)->get();

        $kyc = DB::table('kyc_details')
                ->where('user_id', auth('admin')->id())
                ->where('type', '!=', 'user')
                ->where('type', '!=', 'shop')
                ->first();
        if($kyc){
            $kyc = $kyc->status;
        }else {
            $kyc = 0;
        }        

        return view('admin-views.system.dashboard', compact('data', 'inhouse_data', 'seller_data', 'commission_data','withdrawal_methods','kyc'));
    }

    public function order_stats(Request $request)
    {
        session()->put('statistics_type', $request['statistics_type']);
        $data = self::order_stats_data();

        return response()->json([
            'view' => view('admin-views.partials._dashboard-order-stats', compact('data'))->render()
        ], 200);
    }

    public function method_list(Request $request)
    {
        $method = WithdrawalMethod::ofStatus(1)->where('id', $request->method_id)->first();

        return response()->json(['content'=>$method], 200);
    }

    public function order_stats_data()
    {

        $pending_query = Order::where(['order_status' => 'pending']);
        $pending = self::common_query_order_stats($pending_query);

        $confirmed_query = Order::where(['order_status' => 'confirmed']);
        $confirmed = self::common_query_order_stats($confirmed_query);

        $processing_query = Order::where(['order_status' => 'processing']);
        $processing = self::common_query_order_stats($processing_query);

        $out_for_delivery_query = Order::where(['order_status' => 'out_for_delivery']);
        $out_for_delivery = self::common_query_order_stats($out_for_delivery_query);

        $delivered_query = Order::where(['order_status' => 'delivered']);
        $delivered = self::common_query_order_stats($delivered_query);

        $canceled_query = Order::where(['order_status' => 'canceled']);
        $canceled = self::common_query_order_stats($canceled_query);

        $returned_query = Order::where(['order_status' => 'returned']);
        $returned = self::common_query_order_stats($returned_query);

        $failed_query = Order::where(['order_status' => 'failed']);
        $failed = self::common_query_order_stats($failed_query);

        $total_sale_query = OrderDetail::where(['delivery_status' => 'delivered']);
        $total_sale = self::common_query_order_stats($total_sale_query);

        $product_query = new Product();
        $product = self::common_query_order_stats($product_query);

        $order_query = new Order();
        $order = self::common_query_order_stats($order_query);

        $customer_query = new User();
        $customer = self::common_query_order_stats($customer_query);

        $store_query = Shop::where('seller_type','goods')->whereHas('seller', function($query){
            return $query;
        });
        $store = self::common_query_order_stats($store_query);

        $service_query = Shop::where('seller_type','service')->whereHas('seller', function($query){
            return $query;
        });
        $service = self::common_query_order_stats($service_query);

        $both_query = Shop::where('seller_type','both')->whereHas('seller', function($query){
            return $query;
        });
        $both = self::common_query_order_stats($both_query);

        $data = [
            'total_sale' => $total_sale,
            'product' => $product,
            'services' => 0,
            'order' => $order,
            'customer' => $customer,
            'store' => $store,
            'store_service' => $service,
            'both' => $both,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'processing' => $processing,
            'out_for_delivery' => $out_for_delivery,
            'delivered' => $delivered,
            'canceled' => $canceled,
            'returned' => $returned,
            'failed' => $failed
        ];

        return $data;
    }
    public function order_stats_data_manufacturer($manufacturer_id)
    {

        $pending_query = Order::where(['order_status' => 'pending','manufacturer_id' => $manufacturer_id]);
        $pending = self::common_query_order_stats($pending_query);

        $confirmed_query = Order::where(['order_status' => 'confirmed','manufacturer_id' => $manufacturer_id]);
        $confirmed = self::common_query_order_stats($confirmed_query);

        $processing_query = Order::where(['order_status' => 'processing','manufacturer_id' => $manufacturer_id]);
        $processing = self::common_query_order_stats($processing_query);

        $out_for_delivery_query = Order::where(['order_status' => 'out_for_delivery','manufacturer_id' => $manufacturer_id]);
        $out_for_delivery = self::common_query_order_stats($out_for_delivery_query);

        $delivered_query = Order::where(['order_status' => 'delivered','manufacturer_id' => $manufacturer_id]);
        $delivered = self::common_query_order_stats($delivered_query);

        $canceled_query = Order::where(['order_status' => 'canceled','manufacturer_id' => $manufacturer_id]);
        $canceled = self::common_query_order_stats($canceled_query);

        $returned_query = Order::where(['order_status' => 'returned']);
        $returned = self::common_query_order_stats($returned_query);

        $failed_query = Order::where(['order_status' => 'failed']);
        $failed = self::common_query_order_stats($failed_query);

        $total_sale_query = OrderDetail::where(['delivery_status' => 'delivered']);
        $total_sale = self::common_query_order_stats($total_sale_query);

        $product_query = new Product();
        $product = self::common_query_order_stats($product_query);

        $order_query = new Order();
        $order = self::common_query_order_stats($order_query);

        $customer_query = new User();
        $customer = self::common_query_order_stats($customer_query);

        $store_query = Shop::whereHas('seller', function($query){
            return $query;
        });
        $store = self::common_query_order_stats($store_query);

        $data = [
            'total_sale' => $total_sale,
            'product' => $product,
            'order' => $order,
            'customer' => $customer,
            'store' => $store,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'processing' => $processing,
            'out_for_delivery' => $out_for_delivery,
            'delivered' => $delivered,
            'canceled' => $canceled,
            'returned' => $returned,
            'failed' => $failed
        ];

        return $data;
    }
    public function order_stats_data_aggregator($aggregator_id)
    {

        $pending_query = Order::where(['order_status' => 'pending','aggregator_id' => $aggregator_id]);
        $pending = self::common_query_order_stats($pending_query);

        $confirmed_query = Order::where(['order_status' => 'confirmed','aggregator_id' => $aggregator_id]);
        $confirmed = self::common_query_order_stats($confirmed_query);

        $processing_query = Order::where(['order_status' => 'processing','aggregator_id' => $aggregator_id]);
        $processing = self::common_query_order_stats($processing_query);

        $out_for_delivery_query = Order::where(['order_status' => 'out_for_delivery','aggregator_id' => $aggregator_id]);
        $out_for_delivery = self::common_query_order_stats($out_for_delivery_query);

        $delivered_query = Order::where(['order_status' => 'delivered','aggregator_id' => $aggregator_id]);
        $delivered = self::common_query_order_stats($delivered_query);

        $canceled_query = Order::where(['order_status' => 'canceled','aggregator_id' => $aggregator_id]);
        $canceled = self::common_query_order_stats($canceled_query);

        $returned_query = Order::where(['order_status' => 'returned']);
        $returned = self::common_query_order_stats($returned_query);

        $failed_query = Order::where(['order_status' => 'failed']);
        $failed = self::common_query_order_stats($failed_query);

        $total_sale_query = OrderDetail::where(['delivery_status' => 'delivered']);
        $total_sale = self::common_query_order_stats($total_sale_query);

        $product_query = new Product();
        $product = self::common_query_order_stats($product_query);

        $order_query = new Order();
        $order = self::common_query_order_stats($order_query);

        $customer_query = new User();
        $customer = self::common_query_order_stats($customer_query);

        $store_query = Shop::whereHas('seller', function($query){
            return $query;
        });
        $store = self::common_query_order_stats($store_query);

        $data = [
            'total_sale' => $total_sale,
            'product' => $product,
            'order' => $order,
            'customer' => $customer,
            'store' => $store,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'processing' => $processing,
            'out_for_delivery' => $out_for_delivery,
            'delivered' => $delivered,
            'canceled' => $canceled,
            'returned' => $returned,
            'failed' => $failed
        ];

        return $data;
    }
    public function order_stats_data_doctor($aggregator_id)
    {


        $pending_count = $this->common_query_status_count(Booking::where(['status' => 0, 'employee_id' => $aggregator_id, 'booking_type' => 'doctor'])) ;
        $confirmed_count = $this->common_query_status_count(Booking::where(['status' => 1, 'employee_id' => $aggregator_id , 'booking_type' => 'doctor']));
        $completed_count = $this->common_query_status_count(Booking::where(['status' => 2, 'employee_id' => $aggregator_id , 'booking_type' => 'doctor']));
        $delivered_count = $this->common_query_status_count(Booking::where(['status' => 3, 'employee_id' => $aggregator_id , 'booking_type' => 'doctor']));
        $canceled_count = $this->common_query_status_count(Booking::where(['status' => 4, 'employee_id' => $aggregator_id , 'booking_type' => 'doctor']));



        $pending_query = Order::where(['order_status' => 'pending','aggregator_id' => $aggregator_id]);
        $pending = self::common_query_order_stats($pending_query);

        $confirmed_query = Order::where(['order_status' => 'confirmed','aggregator_id' => $aggregator_id]);
        $confirmed = self::common_query_order_stats($confirmed_query);

        $processing_query = Order::where(['order_status' => 'processing','aggregator_id' => $aggregator_id]);
        $processing = self::common_query_order_stats($processing_query);

        $out_for_delivery_query = Order::where(['order_status' => 'out_for_delivery','aggregator_id' => $aggregator_id]);
        $out_for_delivery = self::common_query_order_stats($out_for_delivery_query);

        $delivered_query = Order::where(['order_status' => 'delivered','aggregator_id' => $aggregator_id]);
        $delivered = self::common_query_order_stats($delivered_query);

        $canceled_query = Order::where(['order_status' => 'canceled','aggregator_id' => $aggregator_id]);
        $canceled = self::common_query_order_stats($canceled_query);

        $returned_query = Order::where(['order_status' => 'returned']);
        $returned = self::common_query_order_stats($returned_query);

        $failed_query = Order::where(['order_status' => 'failed']);
        $failed = self::common_query_order_stats($failed_query);

        $total_sale_query = OrderDetail::where(['delivery_status' => 'delivered']);
        $total_sale = self::common_query_order_stats($total_sale_query);

        $product_query = new Product();
        $product = self::common_query_order_stats($product_query);

        $order_query = new Order();
        $order = self::common_query_order_stats($order_query);

        $customer_query = new User();
        $customer = self::common_query_order_stats($customer_query);

        $store_query = Shop::whereHas('seller', function($query){
            return $query;
        });
        $store = self::common_query_order_stats($store_query);

        $data = [
            'total_sale' => $total_sale,
            'product' => $product,
            'order' => $order,
            'customer' => $customer,
            'store' => $store,
            'pending' => $confirmed_count, //assign order
            'confirmed' => $completed_count,
            'processing' => $processing,
            'out_for_delivery' => $out_for_delivery,
            'delivered' => $delivered,
            'canceled' => $canceled_count,
            'returned' => $returned,
            'failed' => $failed
        ];

        return $data;
    }
    public function order_stats_data_home_visit($aggregator_id)
    {


        $pending_count = $this->common_query_status_count(Booking::where(['status' => 0, 'employee_id' => $aggregator_id, 'booking_type' => 'home_visit'])) ;
        $confirmed_count = $this->common_query_status_count(Booking::where(['status' => 1, 'employee_id' => $aggregator_id , 'booking_type' => 'home_visit']));
        $completed_count = $this->common_query_status_count(Booking::where(['status' => 2, 'employee_id' => $aggregator_id , 'booking_type' => 'home_visit']));
        $delivered_count = $this->common_query_status_count(Booking::where(['status' => 3, 'employee_id' => $aggregator_id , 'booking_type' => 'home_visit']));
        $canceled_count = $this->common_query_status_count(Booking::where(['status' => 4, 'employee_id' => $aggregator_id , 'booking_type' => 'home_visit']));



        $pending_query = Order::where(['order_status' => 'pending','aggregator_id' => $aggregator_id]);
        $pending = self::common_query_order_stats($pending_query);

        $confirmed_query = Order::where(['order_status' => 'confirmed','aggregator_id' => $aggregator_id]);
        $confirmed = self::common_query_order_stats($confirmed_query);

        $processing_query = Order::where(['order_status' => 'processing','aggregator_id' => $aggregator_id]);
        $processing = self::common_query_order_stats($processing_query);

        $out_for_delivery_query = Order::where(['order_status' => 'out_for_delivery','aggregator_id' => $aggregator_id]);
        $out_for_delivery = self::common_query_order_stats($out_for_delivery_query);

        $delivered_query = Order::where(['order_status' => 'delivered','aggregator_id' => $aggregator_id]);
        $delivered = self::common_query_order_stats($delivered_query);

        $canceled_query = Order::where(['order_status' => 'canceled','aggregator_id' => $aggregator_id]);
        $canceled = self::common_query_order_stats($canceled_query);

        $returned_query = Order::where(['order_status' => 'returned']);
        $returned = self::common_query_order_stats($returned_query);

        $failed_query = Order::where(['order_status' => 'failed']);
        $failed = self::common_query_order_stats($failed_query);

        $total_sale_query = OrderDetail::where(['delivery_status' => 'delivered']);
        $total_sale = self::common_query_order_stats($total_sale_query);

        $product_query = new Product();
        $product = self::common_query_order_stats($product_query);

        $order_query = new Order();
        $order = self::common_query_order_stats($order_query);

        $customer_query = new User();
        $customer = self::common_query_order_stats($customer_query);

        $store_query = Shop::whereHas('seller', function($query){
            return $query;
        });
        $store = self::common_query_order_stats($store_query);

        $data = [
            'total_sale' => $total_sale,
            'product' => $product,
            'order' => $order,
            'customer' => $customer,
            'store' => $store,
            'pending' => $confirmed_count, //assign order
            'confirmed' => $completed_count,
            'processing' => $processing,
            'out_for_delivery' => $out_for_delivery,
            'delivered' => $delivered,
            'canceled' => $canceled_count,
            'returned' => $returned,
            'failed' => $failed
        ];

        return $data;
    }

    public function common_query_status_count($query)
    {
        return $query->count();
    }

    public function common_query_order_stats($query){
        $today = session()->has('statistics_type') && session('statistics_type') == 'today' ? 1 : 0;
        $this_month = session()->has('statistics_type') && session('statistics_type') == 'this_month' ? 1 : 0;

        return $query->when($today, function ($query) {
            return $query->whereDate('created_at', Carbon::today());
        })
            ->when($this_month, function ($query) {
                return $query->whereMonth('created_at', Carbon::now());
            })
            ->count();
    }
    public function withdrawal_request(Request $request){
       
        $admin_id = auth('admin')->id();
        $adminData = Admin::find($admin_id);
        if($adminData->user_bonus < '500.00')
        {
            Toastr::warning(\App\CPU\translate('for_withdrawal_you_have_to_more_than_500_bonus_amount!!'));
            return back();
        }
        if($request->amount < '500')
        {
            Toastr::warning(\App\CPU\translate('for_withdrawal_you_have_to_more_than_500_bonus_amount!!'));
            return back();
        }

        $tds = DB::table('business_settings')->where('type','tds_frenchise')->first(); 
        $admin_commission = DB::table('business_settings')->where('type','admin_commission_frenchise')->first(); 
        $team_level_income = DB::table('business_settings')->where('type','team_level_income')->first();
        
        $tds = sprintf("%.2f",(($tds->value/100) * $request->amount));
        $admin_commission = sprintf("%.2f",(($admin_commission->value/100) * $request->amount));
        $team_level_income = 0;
        if(($adminData->user_id) && ($adminData->admin_role_id == 2)){
            $team_level_income = sprintf("%.2f",(($team_level_income->value/100) * $request->amount));
        }

        if($adminData->admin_role_id == 2){
            $type = 'frenchise';
        }elseif ($adminData->admin_role_id == 5) {
            $type = 'district';
        }else{
            $type = 'state';
        }

        $insert = [
            'user_id' => $admin_id,
            'type' => $type,
            'amount' => $request->amount,
            'tds' => $tds ?? 0,
            'admin_commission' => $admin_commission ?? 0,
            'team_level_income' => $team_level_income ?? 0,
            'remaning_amount' => $request->amount - ($tds + $admin_commission + $team_level_income)
        ];
        // dd($insert);
        
        $data = DB::table('withdrawal_request_frenchise')->insert($insert);

        $adminData->user_bonus = $adminData->user_bonus -  $request->amount;
        $adminData->save();

        $admin = Admin::find(1);
        $admin->user_bonus = $admin->user_bonus + $tds + $admin_commission;
        $admin->save();

        if($adminData->admin_role_id == 2){
            $repurchase_bonus_add = Helpers::repurchaseTransactions(1, $admin_id,  $tds, 'admin', null, 'frenchise_withdrwal_tds_bonus','Credit','frenchise');
            $repurchase_bonus_add1 = Helpers::repurchaseTransactions(1, $admin_id,  $admin_commission, 'admin', null, 'frenchise_withdrwal_commission_bonus','Credit','frenchise');
        } elseif($adminData->admin_role_id == 5){
            $repurchase_bonus_add = Helpers::repurchaseTransactions(1, $admin_id,  $tds, 'admin', null, 'district_withdrwal_tds_bonus','Credit','district');
            $repurchase_bonus_add1 = Helpers::repurchaseTransactions(1, $admin_id,  $admin_commission, 'admin', null, 'district_withdrwal_commission_bonus','Credit','district');
        } else {
            $repurchase_bonus_add = Helpers::repurchaseTransactions(1, $admin_id,  $tds, 'admin', null, 'state_withdrwal_tds_bonus','Credit','state');
            $repurchase_bonus_add1 = Helpers::repurchaseTransactions(1, $admin_id,  $admin_commission, 'admin', null, 'state_withdrwal_commission_bonus','Credit','state');
        }

        if(($adminData->user_id) && ($adminData->admin_role_id == 2)){
            $data = Helpers::get_withdrawal_bonus($adminData->user_id, $level = 1, $team_level_income);
        }

        Toastr::success(\App\CPU\translate('withdrawal_request_sent_succesfully!!'));
        return back();
    }

    /**
     * get earning statistics by ajax
     */
    public function get_earning_statitics(Request $request){
        $dateType = $request->type;

        $inhouse_data = array();
        if($dateType == 'yearEarn') {
            $number = 12;
            $from = Carbon::now()->startOfYear()->format('Y-m-d');
            $to = Carbon::now()->endOfYear()->format('Y-m-d');

            $inhouse_earnings = OrderTransaction::where([
                'seller_is'=>'admin',
                'status'=>'disburse'
            ])->select(
                DB::raw('IFNULL(sum(seller_amount),0) as sums'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $inhouse_data[$inc] = 0;
                foreach ($inhouse_earnings as $match) {
                    if ($match['month'] == $inc) {
                        $inhouse_data[$inc] = $match['sums'];
                    }
                }
            }
            $key_range = array("Jan","Feb","Mar","April","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

        }elseif($dateType == 'MonthEarn') {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $number = date('d',strtotime($to));
            $key_range = range(1, $number);

            $inhouse_earnings = OrderTransaction::where([
                'seller_is' => 'admin',
                'status' => 'disburse'
            ])->select(
                DB::raw('seller_amount'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->groupby('day')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $inhouse_data[$inc] = 0;
                foreach ($inhouse_earnings as $match) {
                    if ($match['day'] == $inc) {
                        $inhouse_data[$inc] = $match['seller_amount'];
                    }
                }
            }

        }elseif($dateType == 'WeekEarn') {

            $from = Carbon::now()->startOfWeek()->format('Y-m-d');
            $to = Carbon::now()->endOfWeek()->format('Y-m-d');

            $number_start =date('d',strtotime($from));
            $number_end =date('d',strtotime($to));

            $inhouse_earnings = OrderTransaction::where([
                'seller_is' => 'admin',
                'status' => 'disburse'
            ])->select(
                DB::raw('seller_amount'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->get()->toArray();

            for ($inc = $number_start; $inc <= $number_end; $inc++) {
                $inhouse_data[$inc] = 0;
                foreach ($inhouse_earnings as $match) {
                    if ($match['day'] == $inc) {
                        $inhouse_data[$inc] = $match['seller_amount'];
                    }
                }
            }

            $key_range = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        }

        $inhouse_label = $key_range;

        $inhouse_data_final = $inhouse_data;

        $seller_data = array();
        if($dateType == 'yearEarn') {
            $number = 12;
            $from = Carbon::now()->startOfYear()->format('Y-m-d');
            $to = Carbon::now()->endOfYear()->format('Y-m-d');

            $seller_earnings = OrderTransaction::where([
                'seller_is'=>'seller',
                'status'=>'disburse'
            ])->select(
                DB::raw('IFNULL(sum(seller_amount),0) as sums'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $seller_data[$inc] = 0;
                foreach ($seller_earnings as $match) {
                    if ($match['month'] == $inc) {
                        $seller_data[$inc] = $match['sums'];
                    }
                }
            }
            $key_range = array("Jan","Feb","Mar","April","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

        }elseif($dateType == 'MonthEarn') {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $number = date('d',strtotime($to));
            $key_range = range(1, $number);

            $seller_earnings = OrderTransaction::where([
                'seller_is' => 'seller',
                'status' => 'disburse'
            ])->select(
                DB::raw('seller_amount'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->groupby('day')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $seller_data[$inc] = 0;
                foreach ($seller_earnings as $match) {
                    if ($match['day'] == $inc) {
                        $seller_data[$inc] = $match['seller_amount'];
                    }
                }
            }

        }elseif($dateType == 'WeekEarn') {

            $from = Carbon::now()->startOfWeek()->format('Y-m-d');
            $to = Carbon::now()->endOfWeek()->format('Y-m-d');

            $number_start =date('d',strtotime($from));
            $number_end =date('d',strtotime($to));

            $seller_earnings = OrderTransaction::where([
                'seller_is' => 'seller',
                'status' => 'disburse'
            ])->select(
                DB::raw('seller_amount'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->get()->toArray();

            for ($inc = $number_start; $inc <= $number_end; $inc++) {
                $seller_data[$inc] = 0;
                foreach ($seller_earnings as $match) {
                    if ($match['day'] == $inc) {
                        $seller_data[$inc] = $match['seller_amount'];
                    }
                }
            }

            $key_range = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        }

        $seller_label = $key_range;

        $seller_data_final = $seller_data;

        $commission_data = array();
        if($dateType == 'yearEarn') {
            $number = 12;
            $from = Carbon::now()->startOfYear()->format('Y-m-d');
            $to = Carbon::now()->endOfYear()->format('Y-m-d');

            $commission_earnings = OrderTransaction::where([
                'status'=>'disburse'
            ])->select(
                DB::raw('IFNULL(sum(admin_commission),0) as sums'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )->whereBetween('created_at', [$from, $to])->groupby('year', 'month')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $commission_data[$inc] = 0;
                foreach ($commission_earnings as $match) {
                    if ($match['month'] == $inc) {
                        $commission_data[$inc] = $match['sums'];
                    }
                }
            }

            $key_range = array("Jan","Feb","Mar","April","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

        }elseif($dateType == 'MonthEarn') {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
            $number = date('d',strtotime($to));
            $key_range = range(1, $number);

            $commission_earnings = OrderTransaction::where([
                'seller_is' => 'seller',
                'status' => 'disburse'
            ])->select(
                DB::raw('admin_commission'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->groupby('day')->get()->toArray();

            for ($inc = 1; $inc <= $number; $inc++) {
                $commission_data[$inc] = 0;
                foreach ($commission_earnings as $match) {
                    if ($match['day'] == $inc) {
                        $commission_data[$inc] = $match['admin_commission'];
                    }
                }
            }

        }elseif($dateType == 'WeekEarn') {

            $from = Carbon::now()->startOfWeek()->format('Y-m-d');
            $to = Carbon::now()->endOfWeek()->format('Y-m-d');

            $number_start =date('d',strtotime($from));
            $number_end =date('d',strtotime($to));

            $commission_earnings = OrderTransaction::where([
                'status' => 'disburse'
            ])->select(
                DB::raw('admin_commission'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day')
            )->whereBetween('created_at', [$from, $to])->get()->toArray();

            for ($inc = $number_start; $inc <= $number_end; $inc++) {
                $commission_data[$inc] = 0;
                foreach ($commission_earnings as $match) {
                    if ($match['day'] == $inc) {
                        $commission_data[$inc] = $match['admin_commission'];
                    }
                }
            }
            $key_range = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        }

        $commission_label = $key_range;

        $commission_data_final = $commission_data;

        $data = array(
            'inhouse_label' => $inhouse_label,
            'inhouse_earn' => array_values($inhouse_data_final),
            'seller_label' => $seller_label,
            'seller_earn' => array_values($seller_data_final),
            'commission_label' => $commission_label,
            'commission_earn' => array_values($commission_data_final)
        );

        return response()->json($data);
    }

    public function bonusList()
    {
        
        $query_param = [];

        $cou = RepurchaseTransaction::leftJoin('users as u2', 'u2.id', '=', 'repurchase_transactions.referral_id')
            ->where('repurchase_transactions.parent_type','!=','shop')
            ->where('repurchase_transactions.parent_type','!=','admin')
            ->where('repurchase_transactions.parent_type','!=','user')
            ->where('repurchase_transactions.parent_id','=',auth('admin')->id())
            ->select('repurchase_transactions.*','u2.f_name as referral_f_name', 'u2.l_name as referral_l_name')
            ->latest()
            ->paginate(Helpers::pagination_limit());

        return view('admin-views.system.bonus_list', compact('cou'));
    }

    public function myKyc()
    {
        // if (auth('admin')->id() != $id) {
        //     Toastr::warning(translate('you_can_not_change_others_info'));
        //     return back();
        // }
        $data = DB::table('kyc_details')->where('user_id', auth('admin')->id())->where('type','admin')->first();
        // dd($data);
        return view('admin-views.system.bankEdit', compact('data'));
    }
    public function updateKyc(Request $request)
    {
        $admin = Admin::find(auth('admin')->id());

        if($admin->admin_role_id == 2){
            $type = 'frenchise';
        }elseif ($admin->admin_role_id == 5) {
            $type = 'district';
        }else{
            $type = 'state';
        }


        $data = [
            'user_id' => $request->input('user_id'),
            'type' => 'admin',
            'pan_number' => $request->input('pan_number'),
            'adhar_number' => $request->input('adhar_number'),
            'nomini_name' => $request->input('nomini_name'),
            'nomini_relation' => $request->input('nomini_relation'),
            'holder_name' => $request->input('holder_name'),
            'account_number' => $request->input('account_number'),
            'ifsc' => $request->input('ifsc'),
            'bank_name' => $request->input('bank_name'),
            'status' => 0,
            'admin_type' => $type
        ];
    
        if ($request->hasFile('pan_image')) {
            $imageName = 'pan_' . time() . '.' . $request->file('pan_image')->getClientOriginalExtension();
            $request->file('pan_image')->move(public_path('images'), $imageName);
            $data['pan_image'] = $imageName;
        }
        if ($request->hasFile('adhar_front')) {
            $imageName1 = 'adhar_front_' . time() . '.' . $request->file('adhar_front')->getClientOriginalExtension();
            $request->file('adhar_front')->move(public_path('images'), $imageName1);
            $data['adhar_front'] = $imageName1;
        }
        if ($request->hasFile('adhar_back')) {
            $imageName2 = 'adhar_back_' . time() . '.' . $request->file('adhar_back')->getClientOriginalExtension();
            $request->file('adhar_back')->move(public_path('images'), $imageName2);
            $data['adhar_back'] = $imageName2;
        }
        if ($request->hasFile('passbook_image')) {
            $imageName3 = 'passbook_' . time() . '.' . $request->file('passbook_image')->getClientOriginalExtension();
            $request->file('passbook_image')->move(public_path('images'), $imageName3);
            $data['passbook_image'] = $imageName3;
        }

        if($request->input('kyc_id')){
            $check = DB::table('kyc_details')->where('id', $request->input('kyc_id'))->update($data);
        } else {
            $check = DB::table('kyc_details')->insert($data);
        }
        
        $data = DB::table('kyc_details')->where('id', auth('admin')->id())->where('type','admin')->first();

        Toastr::success(translate('kyc_update_successfully'));
        return back();
    }
}
