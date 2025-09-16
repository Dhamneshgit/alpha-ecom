<?php

namespace App\Http\Controllers\api\v3\seller;

use App\CPU\BackEndHelper;
use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Model\Category;
use App\Model\Order;
use App\Model\SellerWallet;
use App\Model\Seller;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\Admin;
use App\Model\Plan;
use App\Model\Zipcode;
use App\Model\City;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;

class POSController extends Controller
{

    public function get_categories()
    {
        $categories = Category::with(['childes.childes'])->where(['position' => 0])->priority()->get();
        return response()->json($categories, 200);
    }
    public function customer_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'unique:users',
            'country' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
            'address' => 'required',
        ],[
            'f_name.required' => 'First name is required!',
            'l_name.required' => 'Last name is required!'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        User::create([
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'country' => $request['country'],
            'city' => $request['city'],
            'zip' => $request['zip_code'],
            'street_address' =>$request['address'],
            'is_active' => 1,
            'password' => bcrypt('password')
        ]);

        return response()->json(['message' => translate('customer added successfully!')], 200);
    }

    public function customers(Request $request)
    {
        $seller = $request->seller;
        $customers = User::when($request['name'], function ($query) use ($request) {
                $name_array = explode(' ', $request->name);
                    foreach ($name_array as $name) {
                        $query->orWhere('f_name', 'like', "%{$name}%")
                            ->orWhere('l_name', 'like', "%{$name}%");
                    }
                })
            ->whereNotNull(['f_name', 'l_name', 'phone'])
            ->take(10)
            ->get();
        $data = array(
            'customers'=>$customers
        );
        return response()->json($data, 200);
    }

    public function get_product_by_barcode(Request $request)
    {
        $seller = $request->seller;
        $product = Product::where([
            'added_by'=>'seller',
            'user_id'=>$seller->id,
            'code' => $request->code
        ])->first();

        $final_product = array();
        if($product) {
            $final_product = Helpers::product_data_formatting($product, false);
        }

        return response()->json($final_product, 200);
    }

    public function product_list(Request $request)
    {
        $seller = $request->seller;
        $search = $request['name'];

        $products = Product::where(['added_by' => 'seller', 'user_id' => $seller['id']])
            ->when($request->has('category_id') && $request['category_id'] != 0, function ($query) use ($request) {
                $category_ids = json_decode($request->category_id);
                $query->where(function ($query) use ($category_ids) {
                    foreach ($category_ids as $category_id) {
                        $query->orWhereJsonContains('category_ids', [[['id' => $category_id]]]);
                    }
                });
            })
            ->when($request->has('name') && $search!=null,function ($query) use ($search) {
                $key = $search ? explode(' ', $search) : '';
                foreach ($key as $value) {
                    $query->where('name', 'like', "%{$value}%");
                }
            })
            ->orderBy('id', 'DESC')
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $products_final = Helpers::product_data_formatting($products, true);

        $data = array();
        $data['total_size'] = $products->total();
        $data['limit'] = $request['limit'];
        $data['offset'] = $request['offset'];
        $data['products'] = $products_final;
        return response()->json($data, 200);
    }

    public function place_order(Request $request)
    {
        $seller = $request->seller;

        $customer_id = $request->customer_id;
        $cart = $request->cart;
        $extra_discount = $request->extra_discount;
        $extra_discount_type = $request->extra_discount_type;
        $coupon_discount_amount = $request->coupon_discount_amount;
        $coupon_code = $request->coupon_code;
        $order_amount = $request->order_amount;
        $payment_method = $request->payment_method;

        $total_tax_amount = 0;
        $product_price = 0;
        $order_details = [];

        
        
        $seller_data = Seller::find($seller->id);
        $seller_wallet = SellerWallet::find($seller->id);
        
       
        if(empty($seller_data->sales_commission_percentage)){
            return response()->json(['message'=>'you can not create order without admin set your commission'], 403);
        }
        $maintainBalance = DB::table('business_settings')->where('type','minimum_seller_wallet')->first();

        if($maintainBalance->value > $seller_wallet->total_earning){
            return response()->json(['message'=>'you have short balance for wallet maintain amount'], 403);
        }
        $sellerCommission = $seller_data->sales_commission_percentage;
        $adminCommission = ($sellerCommission/100) * $request->order_amount;

        // dd($adminCommission);

        $seller_wallet->total_earning = $seller_wallet->total_earning - round($adminCommission,2);
        $seller_wallet->save();

        $order_id = 100000 + Order::all()->count() + 1;
        if (Order::find($order_id)) {
            $order_id = Order::orderBy('id', 'DESC')->first()->id + 1;
        }

        $product_subtotal = 0;
        foreach($cart as $c)
        {
            if(is_array($c))
            {
                $product = Product::find($c['id']);

                $discount_on_product = 0;
                $product_subtotal = ($c['price']) * $c['quantity'];
                $discount_on_product += ($c['discount'] * $c['quantity']);
                if($product)
                {
                    $tax = Helpers::tax_calculation($c['price'], $product['tax'], $product['tax_type']);
                    $price = $product['tax_model']=='include' ? $c['price']-$tax : $c['price'];

                    //$product = Helpers::product_data_formatting($product);
                    $or_d = [
                        'order_id' => $order_id,
                        'product_id' => $c['id'],
                        'product_details' => $product,
                        'qty' => $c['quantity'],
                        'price' => $price,
                        'seller_id' => $product['user_id'],
                        'tax' => $tax*$c['quantity'],
                        'tax_model' => $product['tax_model'],
                        'discount' => $c['discount']*$c['quantity'],
                        'discount_type' => 'discount_on_product',
                        'delivery_status' => 'delivered',
                        'payment_status' => 'paid',
                        'variant' => $c['variant'],
                        'variation' => json_encode($c['variation']),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $total_tax_amount += $or_d['tax'] * $c['quantity'];
                    $product_price += $product_subtotal - $discount_on_product;
                    $order_details[] = $or_d;

                    if ($c['variant'] != null) {
                        $type = $c['variant'];
                        $var_store = [];

                        foreach (json_decode($product['variation'],true) as $var) {
                            if ($type == $var['type']) {
                                $var['qty'] -= $c['quantity'];
                            }
                            array_push($var_store, $var);
                        }
                        Product::where(['id' => $product['id']])->update([
                            'variation' => json_encode($var_store),
                        ]);
                    }

                    if($product['product_type'] == 'physical') {
                        Product::where(['id' => $product['id']])->update([
                            'current_stock' => $product['current_stock'] - $c['quantity']
                        ]);
                    }

                    DB::table('order_details')->insert($or_d);
                }

            }

        }

        $total_price = $product_price;
        if (isset($cart['ext_discount'])) {
            $extra_discount = $extra_discount_type == 'percent' && $extra_discount > 0 ? (($total_price * $extra_discount) / 100) : $extra_discount;
            $total_price -= $extra_discount;
        }
        $or = [
            'id' => $order_id,
            'customer_id' => $customer_id,
            'customer_type' => 'customer',
            'payment_status' => 'paid',
            'order_status' => 'delivered',
            'seller_id' => $seller->id,
            'seller_is' => 'seller',
            'payment_method' => $payment_method,
            'order_type' => 'POS',
            'checked' =>1,
            'extra_discount' =>$extra_discount??0,
            'extra_discount_type' => $extra_discount_type??null,
            'order_amount' => BackEndHelper::currency_to_usd($order_amount),
            'discount_amount' => $coupon_discount_amount??0,
            'coupon_code' => $coupon_code??null,
            'discount_type' => (isset($cart['coupon_code']) && $cart['coupon_code']) ? 'coupon_discount' : NULL,
            'coupon_discount_bearer' => $cart['coupon_bearer']??'inhouse',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('orders')->insertGetId($or);

        $userdata = User::find($customer_id);

        // dd($userdata);
        
        if((!empty($userdata->plan_id)) && ($userdata->plan_expire_date > date('Y-m-d'))){

            $plan_data = Plan::find($userdata->plan_id);
            $remainingAmount = $adminCommission;
            
            $frenchise = Admin::where('zipcode',$userdata->zipcode)->where('admin_role_id', 2)->first();
            $admin = Admin::where('admin_role_id', 1)->first();
            // $shopData = Seller::where('zipcode',$userdata->zipcode)->first();
            
            $customerBonus = (5/100) * $adminCommission;
            $customerSelfBonus = ($plan_data->self_purchase_bonus/100) * $adminCommission;
            $frenchiseBonus = ($plan_data->repurchase_frenchise_bonus/100) * $adminCommission;
            $districtBonus = ($plan_data->repurchase_district_bonus/100) * $adminCommission;
            $stateBonus = ($plan_data->repurchase_state_bonus/100) * $adminCommission;
           
            $userdata->wallet_balance = $userdata->wallet_balance - $customerBonus; 
            $userdata->withdrawal_wallet = $userdata->withdrawal_wallet + round(($customerBonus + $customerSelfBonus),2); 
            $userdata->save();   
            
            
            $zipcode = Zipcode::where('zipcode',$userdata->zipcode)->first();
            $city = City::find($zipcode->city_id);

            $district = Admin::where('city_id', $zipcode->city_id)->first();
            if($district){
                $district->user_bonus = $district->user_bonus + $districtBonus;
                $district->save();
                $d4 = Helpers::repurchaseTransactions($district->id, $customer_id, $districtBonus, 'distirct', $userdata->zipcode, 'district_repurchase_bonus');

            }

            $state = Admin::where('state', $city->state)->first();
            if($state){
                $state->user_bonus = $state->user_bonus + $stateBonus;
                $state->save();
                $d5 = Helpers::repurchaseTransactions($state->id, $customer_id, $stateBonus, 'state', $userdata->zipcode, 'state_repurchase_bonus');
            }
    
    
            $d1 = Helpers::repurchaseTransactions($userdata->id, null, $customerBonus, 'user', $userdata->zipcode, 'bonus', 'Debit');
            $d2 = Helpers::repurchaseTransactions($userdata->id, null, $customerSelfBonus, 'user', $userdata->zipcode, 'self_purchase_bonus','Credit');
    
            if($frenchise){
                $frenchise->user_bonus = $frenchise->user_bonus + $frenchiseBonus;
                $frenchise->save();
                $d3 = Helpers::repurchaseTransactions($frenchise->id, $customer_id, $frenchiseBonus, 'frenchise', $userdata->zipcode, 'frenchise_repurchase_bonus');
            }
    
    
            $remainingAmount = $remainingAmount - ($customerBonus + $customerSelfBonus + $frenchiseBonus + $districtBonus + $stateBonus);
            $remainingAmount = sprintf("%.2f",$remainingAmount); 

            $data = Helpers::get_bonus_referral($customer_id, 1, $remainingAmount);
            
        }

        Helpers::sellerWalletTransaction($seller->id, 'admin_commission', 'Debit', $adminCommission, "Admin Commissiom on Order ID:".$order_id);

        return response()->json(['order_id'=>$order_id], 200);

    }

    public function get_invoice(Request $request)
    {
        $seller = $request->seller;
        $id = $request->id;

        $seller_pos = BusinessSetting::where('type','seller_pos')->first()->value;
        if($seller->pos_status == 0 || $seller_pos == 0)
        {
            return response()->json(['message' => translate('access_denied!')], 403);
        }

        $orders = Order::with('details', 'shipping')->where(['seller_id' => $seller['id']])->find($id);

        if($orders) {
            foreach ($orders['details'] as $order) {
                $order['product_details'] = Helpers::product_data_formatting(json_decode($order['product_details'], true));
            }
        }

        return response()->json($orders, 200);
    }



}
