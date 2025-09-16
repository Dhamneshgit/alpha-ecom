<?php

namespace App\CPU;

use App\Model\Admin;
use App\Model\BusinessSetting;
use App\Model\Category;
use App\Model\Color;
use App\Model\Notification;
use App\Model\Coupon;
use App\Model\Currency;
use App\Model\Order;
use App\Model\OrderStatusHistory;
use App\Model\Review;
use App\Model\Seller;
use App\Model\ShippingMethod;
use App\Model\Shop;
use App\Model\Team;
use App\Model\ItemWeight;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Model\Plan;
use App\Model\PlanLevel;
use App\Providers\FirebaseService;

class Helpers
{
    public static function status($id)
    {
        if ($id == 1) {
            $x = 'active';
        } elseif ($id == 0) {
            $x = 'in-active';
        }

        return $x;
    }

    public static function transaction_formatter($transaction)
    {
        if ($transaction['paid_by'] == 'customer') {
            $user = User::find($transaction['payer_id']);
            $payer = $user->f_name . ' ' . $user->l_name;
        } elseif ($transaction['paid_by'] == 'seller') {
            $user = Seller::find($transaction['payer_id']);
            $payer = $user->f_name . ' ' . $user->l_name;
        } elseif ($transaction['paid_by'] == 'admin') {
            $user = Admin::find($transaction['payer_id']);
            $payer = $user->name;
        }

        if ($transaction['paid_to'] == 'customer') {
            $user = User::find($transaction['payment_receiver_id']);
            $receiver = $user->f_name . ' ' . $user->l_name;
        } elseif ($transaction['paid_to'] == 'seller') {
            $user = Seller::find($transaction['payment_receiver_id']);
            $receiver = $user->f_name . ' ' . $user->l_name;
        } elseif ($transaction['paid_to'] == 'admin') {
            $user = Admin::find($transaction['payment_receiver_id']);
            $receiver = $user->name;
        }

        $transaction['payer_info'] = $payer;
        $transaction['receiver_info'] = $receiver;

        return $transaction;
    }

    public static function get_customer($request = null)
    {
        $user = null;
        if (auth('customer')->check()) {
            $user = auth('customer')->user(); // for web
        } elseif ($request != null && $request->user() != null) {
            $user = $request->user(); //for api
        } elseif (session()->has('customer_id')) {
            $user = User::find(session('customer_id'));
        }

        if ($user == null) {
            $user = 'offline';
        }

        return $user;
    }

    public static function coupon_discount($request)
    {
        $discount = 0;
        $user = Helpers::get_customer($request);
        $couponLimit = Order::where('customer_id', $user->id)
            ->where('coupon_code', $request['coupon_code'])->count();

        $coupon = Coupon::where(['code' => $request['coupon_code']])
            ->where('limit', '>', $couponLimit)
            ->where('status', '=', 1)
            ->whereDate('start_date', '<=', Carbon::parse()->toDateString())
            ->whereDate('expire_date', '>=', Carbon::parse()->toDateString())->first();

        if (isset($coupon)) {
            $total = 0;
            foreach (CartManager::get_cart(CartManager::get_cart_group_ids($request)) as $cart) {
                $product_subtotal = $cart['price'] * $cart['quantity'];
                $total += $product_subtotal;
            }
            if ($total >= $coupon['min_purchase']) {
                if ($coupon['discount_type'] == 'percentage') {
                    $discount = (($total / 100) * $coupon['discount']) > $coupon['max_discount'] ? $coupon['max_discount'] : (($total / 100) * $coupon['discount']);
                } else {
                    $discount = $coupon['discount'];
                }
            }
        }

        return $discount;
    }

    public static function default_lang()
    {
        if (strpos(url()->current(), '/api')) {
            $lang = App::getLocale();
        } elseif (session()->has('local')) {
            $lang = session('local');
        } else {
            $data = Helpers::get_business_settings('language');
            $code = 'en';
            $direction = 'ltr';
            foreach ($data as $ln) {
                if (array_key_exists('default', $ln) && $ln['default']) {
                    $code = $ln['code'];
                    if (array_key_exists('direction', $ln)) {
                        $direction = $ln['direction'];
                    }
                }
            }
            session()->put('local', $code);
            Session::put('direction', $direction);
            $lang = $code;
        }
        return $lang;
    }

    public static function rating_count($product_id, $rating)
    {
        return Review::where(['product_id' => $product_id, 'rating' => $rating])->whereNull('delivery_man_id')->count();
    }
    public static function service_rating_count($product_id, $rating)
    {
        return Review::where(['service_id' => $product_id, 'rating' => $rating])->count();
    }

    public static function get_business_settings($name)
    {
        $config = null;
        $check = ['currency_model', 'currency_symbol_position', 'system_default_currency', 'language', 'company_name', 'decimal_point_settings', 'product_brand', 'digital_product', 'company_email', 'recaptcha'];

        if (in_array($name, $check) == true && session()->has($name)) {
            $config = session($name);
        } else {
            $data = BusinessSetting::where(['type' => $name])->first();
            if (isset($data)) {
                $config = json_decode($data['value'], true);
                if (is_null($config)) {
                    $config = $data['value'];
                }
            }

            if (in_array($name, $check) == true) {
                session()->put($name, $config);
            }
        }

        return $config;
    }

    public static function get_settings($object, $type)
    {
        $config = null;
        foreach ($object as $setting) {
            if ($setting['type'] == $type) {
                $config = $setting;
            }
        }
        return $config;
    }

    public static function get_shipping_methods($seller_id, $type)
    {
        if ($type == 'admin') {
            return ShippingMethod::where(['status' => 1])->where(['creator_type' => 'admin'])->get();
        } else {
            return ShippingMethod::where(['status' => 1])->where(['creator_id' => $seller_id, 'creator_type' => $type])->get();
        }
    }

    public static function get_image_path($type)
    {
        $path = asset('storage/app/public/brand');
        return $path;
    }

    public static function set_data_format($data)
    {
        $colors = is_array($data['colors']) ? $data['colors'] : json_decode($data['colors']);
        $query_data = Color::whereIn('code', $colors)->pluck('name', 'code')->toArray();
        $color_final = [];
        foreach ($query_data as $key => $color) {
            $color_final[] = array(
                'name' => $color,
                'code' => $key,
            );
        }

        $variation = [];
        $data['category_ids'] = is_array($data['category_ids']) ? $data['category_ids'] : json_decode($data['category_ids']);
        $data['images'] = is_array($data['images']) ? $data['images'] : json_decode($data['images']);
        $data['color_image'] = isset($data['color_image']) ? (is_array($data['color_image']) ? $data['color_image'] : json_decode($data['color_image'])) : null;
        $data['colors_formatted'] = $color_final;
        $attributes = [];
        if ((is_array($data['attributes']) ? $data['attributes'] : json_decode($data['attributes'])) != null) {
            $attributes_arr = is_array($data['attributes']) ? $data['attributes'] : json_decode($data['attributes']);
            foreach ($attributes_arr as $attribute) {
                $attributes[] = (int)$attribute;
            }
        }
        $data['attributes'] = $attributes;
        $data['choice_options'] = is_array($data['choice_options']) ? $data['choice_options'] : json_decode($data['choice_options']);
        $variation_arr = is_array($data['variation']) ? $data['variation'] : json_decode($data['variation'], true);
        foreach ($variation_arr as $var) {
            $variation[] = [
                'type' => $var['type'],
                'price' => (float)$var['price'],
                'sku' => $var['sku'],
                'qty' => (int)$var['qty'],
            ];
        }
        $data['variation'] = $variation;

        return $data;
    }


    public static function product_data_formatting($data, $multi_data = false)
    {
        if ($data) {
            $storage = [];
            if ($multi_data == true) {
                foreach ($data as $item) {
                    $storage[] = Helpers::set_data_format($item);
                }
                $data = $storage;
            } else {
                $data = Helpers::set_data_format($data);;
            }

            return $data;
        }
        return null;
    }

    public static function units()
    {
        $x = ['kg', 'pc', 'gms', 'ltrs'];
        return $x;
    }

    public static function remove_invalid_charcaters($str)
    {
        return str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', preg_replace('/\s\s+/', ' ', $str));
    }

    public static function saveJSONFile($code, $data)
    {
        ksort($data);
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(base_path('resources/lang/en/messages.json'), stripslashes($jsonData));
    }

    public static function combinations($arrays)
    {
        $result = [[]];
        foreach ($arrays as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    public static function error_processor($validator)
    {
        $err_keeper = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $err_keeper[] = ['code' => $index, 'message' => $error[0]];
        }
        return $err_keeper;
    }

    public static function currency_load()
    {
        $default = Helpers::get_business_settings('system_default_currency');
        $current = \session('system_default_currency_info');
        if (session()->has('system_default_currency_info') == false || $default != $current['id']) {
            $id = Helpers::get_business_settings('system_default_currency');
            $currency = Currency::find($id);
            session()->put('system_default_currency_info', $currency);
            session()->put('currency_code', $currency->code);
            session()->put('currency_symbol', $currency->symbol);
            session()->put('currency_exchange_rate', $currency->exchange_rate);
        }
    }

    public static function currency_converter($amount)
    {
        $currency_model = Helpers::get_business_settings('currency_model');
        if ($currency_model == 'multi_currency') {
            if (session()->has('usd')) {
                $usd = session('usd');
            } else {
                $usd = Currency::where(['code' => 'USD'])->first()->exchange_rate;
                session()->put('usd', $usd);
            }
            $my_currency = \session('currency_exchange_rate');
            $rate = $my_currency / $usd;
        } else {
            $rate = 1;
        }

        return Helpers::set_symbol(round($amount * $rate, 2));
    }

    public static function language_load()
    {
        if (\session()->has('language_settings')) {
            $language = \session('language_settings');
        } else {
            $language = BusinessSetting::where('type', 'language')->first();
            \session()->put('language_settings', $language);
        }
        return $language;
    }

    public static function tax_calculation($price, $tax, $tax_type)
    {
        $amount = ($price / 100) * $tax;
        return $amount;
    }

    public static function get_price_range($product)
    {
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        foreach (json_decode($product->variation) as $key => $variation) {
            if ($lowest_price > $variation->price) {
                $lowest_price = round($variation->price, 2);
            }
            if ($highest_price < $variation->price) {
                $highest_price = round($variation->price, 2);
            }
        }

        $lowest_price = Helpers::currency_converter($lowest_price - Helpers::get_product_discount($product, $lowest_price));
        $highest_price = Helpers::currency_converter($highest_price - Helpers::get_product_discount($product, $highest_price));

        if ($lowest_price == $highest_price) {
            return $lowest_price;
        }
        return $lowest_price . ' - ' . $highest_price;
    }

    public static function get_product_discount($product, $price)
    {
        $discount = 0;
        if ($product['discount_type'] == 'percent') {
            $discount = ($price * $product['discount']) / 100;
        } elseif ($product['discount_type'] == 'flat') {
            $discount = $product['discount'];
        }

        return floatval($discount);
    }

    public static function module_permission_check($mod_name)
    {
        $user_role = auth('admin')->user()->role;
        $permission = $user_role->module_access;
        if (isset($permission) && $user_role->status == 1 && in_array($mod_name, (array)json_decode($permission)) == true) {
            return true;
        }

        if (auth('admin')->user()->admin_role_id == 1) {
            return true;
        }
        return false;
    }

    public static function convert_currency_to_usd($price)
    {
        $currency_model = Helpers::get_business_settings('currency_model');
        if ($currency_model == 'multi_currency') {
            Helpers::currency_load();
            $code = session('currency_code') == null ? 'USD' : session('currency_code');
            if ($code == 'USD') {
                return $price;
            }
            $currency = Currency::where('code', $code)->first();
            $price = floatval($price) / floatval($currency->exchange_rate);

            $usd_currency = Currency::where('code', 'USD')->first();
            $price = $usd_currency->exchange_rate < 1 ? (floatval($price) * floatval($usd_currency->exchange_rate)) : (floatval($price) / floatval($usd_currency->exchange_rate));
        } else {
            $price = floatval($price);
        }

        return $price;
    }

    public static function convert_manual_currency_to_usd($price, $currency = null)
    {
        $currency_model = Helpers::get_business_settings('currency_model');
        if ($currency_model == 'multi_currency') {
            $code = $currency == null ? 'USD' : $currency;
            if ($code == 'USD') {
                return $price;
            }
            $currency = Currency::where('code', $code)->first();
            $price = floatval($price) / floatval($currency->exchange_rate);

            $usd_currency = Currency::where('code', 'USD')->first();
            $price = $usd_currency->exchange_rate < 1 ? (floatval($price) * floatval($usd_currency->exchange_rate)) : (floatval($price) / floatval($usd_currency->exchange_rate));
        } else {
            $price = floatval($price);
        }

        return $price;
    }

    public static function order_status_update_message($status)
    {
        if ($status == 'pending') {
            $data = BusinessSetting::where('type', 'order_pending_message')->first()->value;
        } elseif ($status == 'confirmed') {
            $data = BusinessSetting::where('type', 'order_confirmation_msg')->first()->value;
        } elseif ($status == 'processing') {
            $data = BusinessSetting::where('type', 'order_processing_message')->first()->value;
        } elseif ($status == 'out_for_delivery') {
            $data = BusinessSetting::where('type', 'out_for_delivery_message')->first()->value;
        } elseif ($status == 'delivered') {
            $data = BusinessSetting::where('type', 'order_delivered_message')->first()->value;
        } elseif ($status == 'returned') {
            $data = BusinessSetting::where('type', 'order_returned_message')->first()->value;
        } elseif ($status == 'failed') {
            $data = BusinessSetting::where('type', 'order_failed_message')->first()->value;
        } elseif ($status == 'delivery_boy_delivered') {
            $data = BusinessSetting::where('type', 'delivery_boy_delivered_message')->first()->value;
        } elseif ($status == 'del_assign') {
            $data = BusinessSetting::where('type', 'delivery_boy_assign_message')->first()->value;
        } elseif ($status == 'ord_start') {
            $data = BusinessSetting::where('type', 'delivery_boy_start_message')->first()->value;
        } elseif ($status == 'expected_delivery_date') {
            $data = BusinessSetting::where('type', 'delivery_boy_expected_delivery_date_message')->first()->value;
        } elseif ($status == 'canceled') {
            $data = BusinessSetting::where('type', 'order_canceled')->first()->value;
        } else {
            $data = '{"status":"0","message":""}';
        }



        $res = json_decode($data, true);

        if ($res['status'] == 0) {
            return 0;
        }
        return $res['message'];
    }

    /**
     * Device wise notification send
     */
    public static function send_push_notif_to_device($fcm_token, $data)
    {
        $key = BusinessSetting::where(['type' => 'push_notification_key'])->first()->value;
        $url = "https://fcm.googleapis.com/fcm/send";
        $header = array(
            "authorization: key=" . $key . "",
            "content-type: application/json"
        );

        if (isset($data['order_id']) == false) {
            $data['order_id'] = null;
        }

        $postdata = '{
            "to" : "' . $fcm_token . '",
            "data" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $data['image'] . '",
                "order_id":"' . $data['order_id'] . '",
                "is_read": 0
              },
              "notification" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $data['image'] . '",
                "order_id":"' . $data['order_id'] . '",
                "title_loc_key":"' . $data['order_id'] . '",
                "is_read": 0,
                "icon" : "new",
                "sound" : "default"
              }
        }';

        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Get URL content
        $result = curl_exec($ch);
        // close handle to release resources
        curl_close($ch);

        return $result;
    }

    public static function send_push_notif_to_topic($data)
    {
        $key = BusinessSetting::where(['type' => 'push_notification_key'])->first()->value;

        $url = "https://fcm.googleapis.com/fcm/send";
        $header = [
            "authorization: key=" . $key . "",
            "content-type: application/json",
        ];

        $image = asset('storage/app/public/notification') . '/' . $data['image'];
        $postdata = '{
            "to" : "/topics/sixvalley",
            "data" : {
                "title":"' . $data->title . '",
                "body" : "' . $data->description . '",
                "image" : "' . $image . '",
                "is_read": 0
              },
              "notification" : {
                "title":"' . $data->title . '",
                "body" : "' . $data->description . '",
                "image" : "' . $image . '",
                "title_loc_key":null,
                "is_read": 0,
                "icon" : "new",
                "sound" : "default"
              }
        }';

        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Get URL content
        $result = curl_exec($ch);
        // close handle to release resources
        curl_close($ch);

        return $result;
    }

    public static function send_to_shipmozo($order_id, $weight = 0)
    {


        $item_weight_detail = ItemWeight::where('order_id', $order_id)->get();

        $final_item_weight = [];

        foreach ($item_weight_detail as $key => $value) {
            $item['no_of_box'] = $value['number_of_box'] ?? 1;
            $item['length'] = $value['length'];
            $item['width'] = $value['width'];
            $item['height'] = $value['height'];

            array_push($final_item_weight, $item);
        }

        $order_details = Order::with('details.product', 'shipping', 'seller.shop', 'itemsweight')->where(['id' => $order_id])->first();


        $amount = 0;
        if ($order_details['payment_method'] == 'cash_on_delivery') {
            $amount =  $order_details['order_amount'];
        }

        $shipping_address = json_decode($order_details['shipping_address_data']);

        // dd($shipping_address);

        $itemDetails = [];
        foreach ($order_details->details as $key => $detail) {

            $itemDetails[] =
                [
                    'name' => $detail->product->name,
                    "sku_number" => $detail->product->code ?? '123',
                    "quantity" => $detail->qty,
                    "discount" => $detail->discount,
                    "hsn" => $detail->product->code ?? '123',
                    "unit_price" => $detail->price,
                    "product_category" => $detail->product->category->name
                ];
        }

        $data = [
            "order_id" => $order_details['order_id'],
            "order_date" => Carbon::parse($order_details['created_at'])->format('Y-m-d'),
            "order_type" => "ESSENTIALS",
            "consignee_name" => $order_details->customer['f_name'] . ' ' . $order_details->customer['l_name'],
            "consignee_phone" => $order_details->customer['phone'],
            "consignee_alternate_phone" => $order_details->customer['phone'],
            "consignee_email" => $order_details->customer['email'],
            "consignee_address_line_one" => $shipping_address->address,
            "consignee_address_line_two" => $shipping_address->address,
            "consignee_pin_code" =>  $shipping_address->zip,
            "consignee_city" =>  $shipping_address->city ?? $order_details->customer['city'],
            "consignee_state" =>  $shipping_address->state ?? $order_details->customer['state'],
            "product_detail" => $itemDetails,
            "payment_type" => ($order_details['payment_method'] == 'cash_on_delivery') ? 'COD' : 'PREPAID',
            "cod_amount" => ($order_details['payment_method'] == 'cash_on_delivery') ? $amount : '',
            "weight" => $weight,
            "warehouse_id" => $order_details->seller->warehouse_id,
            "gst_ewaybill_number" => "12354678",
            "gstin_number" => "",
            "type_of_package" => "MPS",
            "dimensions" => $final_item_weight
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://shipping-api.com/app/api/v1/push-order',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'public-key: j8eFCLpZn9Y2wkuyAGri',
                'private-key: sQMeHuYIqoX37N8EFTxt',
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;
        return  $response;
    }

    public static function create_warehouse($address_title, $name, $phone, $alternate_phone, $email, $address_line_one, $address_line_two, $pin_code)
    {
        $data = [
            "address_title" => $address_title,
            "name" => $name,
            "phone" => $phone,
            "alternate_phone" => $alternate_phone,
            "email" => $email,
            "address_line_one" => $address_line_one,
            "address_line_two" => $address_line_two,
            "pin_code" => $pin_code
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://shipping-api.com/app/api/v1/create-warehouse',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'public-key: j8eFCLpZn9Y2wkuyAGri',
                'private-key: sQMeHuYIqoX37N8EFTxt',
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        // Handle response (for debugging or saving data)
        // echo $response;

        return $response;
    }

    public static function sendNotification($token, $title, $body, $data = [])
    {
        // $notification = Notification::create($title, $body);
        if ($token != '') {

            $firebase = new FirebaseService;
            return $firebase->sendNotification($token, $title, $body, $data);

            try {

                $serviceAccountPath = base_path(env('FIREBASE_CREDENTIALS'));
                $factory = (new Factory)->withServiceAccount($serviceAccountPath)->createMessaging()->send($message);
            } catch (\Kreait\Firebase\Exception\Messaging\MessagingError $e) {
                throw new \Exception('MessagingError: ' . $e->getMessage());
                return true;
            } catch (\Exception $e) {
                throw new \Exception('Error: ' . $e->getMessage());
                return true;
            }
        }
    }

    public static function createNotification($title, $body, $user_id = null, $seller_id = null, $driver_id = null)
    {

        $notification = new Notification();
        $notification->title = $title;
        $notification->description = $body;
        $notification->user_id = $user_id;
        $notification->seller_id = $seller_id;
        $notification->driver_id = $driver_id;
    }
    public static function get_seller_by_token($request)
    {
        $data = '';
        $success = 0;

        $token = explode(' ', $request->header('authorization'));
        if (count($token) > 1 && strlen($token[1]) > 30) {
            $seller = Seller::where(['auth_token' => $token['1']])->first();
            if (isset($seller)) {
                $data = $seller;
                $success = 1;
            }
        }

        return [
            'success' => $success,
            'data' => $data
        ];
    }

    public static function remove_dir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") Helpers::remove_dir($dir . "/" . $object);
                    else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static function currency_code()
    {
        Helpers::currency_load();
        if (session()->has('currency_symbol')) {
            $symbol = session('currency_symbol');
            $code = Currency::where(['symbol' => $symbol])->first()->code;
        } else {
            $system_default_currency_info = session('system_default_currency_info');
            $code = $system_default_currency_info->code;
        }
        return $code;
    }

    public static function get_language_name($key)
    {
        $values = Helpers::get_business_settings('language');
        foreach ($values as $value) {
            if ($value['code'] == $key) {
                $key = $value['name'];
            }
        }

        return $key;
    }

    public static function setEnvironmentValue($envKey, $envValue)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        if (is_bool(env($envKey))) {
            $oldValue = var_export(env($envKey), true);
        } else {
            $oldValue = env($envKey);
        }

        if (strpos($str, $envKey) !== false) {
            $str = str_replace("{$envKey}={$oldValue}", "{$envKey}={$envValue}", $str);
        } else {
            $str .= "{$envKey}={$envValue}\n";
        }
        $fp = fopen($envFile, 'w');
        fwrite($fp, $str);
        fclose($fp);
        return $envValue;
    }

    public static function requestSender()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => route(base64_decode('YWN0aXZhdGlvbi1jaGVjaw==')),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);
        $data = json_decode($response, true);
        return $data;
    }

    public static function sales_commission($order)
    {
        $discount_amount = 0;
        if ($order->coupon_code) {
            $coupon = Coupon::where(['code' => $order->coupon_code])->first();
            if ($coupon) {
                $discount_amount = $coupon->coupon_type == 'free_delivery' ? 0 : $order['discount_amount'];
            }
        }
        $order_summery = OrderManager::order_summary($order);
        $order_total = $order_summery['subtotal'] - $order_summery['total_discount_on_product'] - $discount_amount;
        $commission_amount = self::seller_sales_commission($order['seller_is'], $order['seller_id'], $order_total);

        return $commission_amount;
    }

    public static function sales_commission_before_order($cart_group_id, $coupon_discount)
    {
        $carts = CartManager::get_cart($cart_group_id);
        $cart_summery = OrderManager::order_summary_before_place_order($carts, $coupon_discount);
        $commission_amount = self::seller_sales_commission($carts[0]['seller_is'], $carts[0]['seller_id'], $cart_summery['order_total']);

        return $commission_amount;
    }

    public static function seller_sales_commission($seller_is, $seller_id, $order_total)
    {
        $commission_amount = 0;
        if ($seller_is == 'seller') {
            $seller = Seller::find($seller_id);
            if (isset($seller) && $seller['sales_commission_percentage'] !== null) {
                $commission = $seller['sales_commission_percentage'];
            } else {
                $commission = Helpers::get_business_settings('sales_commission');
            }
            $commission_amount = number_format(($order_total / 100) * $commission, 2);
        }
        return $commission_amount;
    }

    public static function categoryName($id)
    {
        return Category::select('name')->find($id)->name;
    }

    public static function set_symbol($amount)
    {
        $decimal_point_settings = Helpers::get_business_settings('decimal_point_settings');
        $position = Helpers::get_business_settings('currency_symbol_position');
        if (!is_null($position) && $position == 'left') {
            $string = currency_symbol() . '' . number_format($amount, (!empty($decimal_point_settings) ? $decimal_point_settings : 0));
        } else {
            $string = number_format($amount, !empty($decimal_point_settings) ? $decimal_point_settings : 0) . '' . currency_symbol();
        }
        return $string;
    }

    public static function pagination_limit()
    {
        $pagination_limit = BusinessSetting::where('type', 'pagination_limit')->first();
        if ($pagination_limit != null) {
            return $pagination_limit->value;
        } else {
            return 25;
        }
    }

    public static function gen_mpdf($view, $file_prefix, $file_postfix)
    {
        $mpdf = new \Mpdf\Mpdf(['default_font' => 'FreeSerif', 'mode' => 'utf-8', 'format' => [190, 250]]);
        /* $mpdf->AddPage('XL', '', '', '', '', 10, 10, 10, '10', '270', '');*/
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        $mpdf_view = $view;
        $mpdf_view = $mpdf_view->render();
        $mpdf->WriteHTML($mpdf_view);
        $mpdf->Output($file_prefix . $file_postfix . '.pdf', 'D');
    }

    // public static function get_bonus_referral($is_frenchise, $user_id, $level = 1){

    //     $code = Helpers::get_friends_code($user_id);

    //     if($code['status']){
    //         $check = Helpers::checkPlan($code['code'], $level, $user_id, $is_frenchise);
    //         $user = User::where('referral_code', $code['code'])->first();

    //         Helpers::get_bonus_referral($is_frenchise, $user->id, $level + 1);
    //     }
    //     return $code;
    // }
    public static function get_bonus_referral($amount, $user_id, $level = 1)
    {
        $code = Helpers::get_friends_code($user_id, $amount);
        if ($code['status']) {
            $check = Helpers::checkPlan($code['code'], $level, $user_id, $amount);
            $user = User::where('referral_code', $code['code'])->first();
            if ($user) {
                Helpers::get_bonus_referral($user->id, $level + 1, $check); // $check is remaing Amount
            }
        } else {
            $admin = Admin::find(1);
            $admin->user_bonus = $admin->user_bonus + $code['remaning'];
            $admin->save();
            $repurchase_bonus_add = Helpers::repurchaseTransactions(1, $user_id,  $code['remaning'], 'admin', null, 'repurchase_bonus', 'Credit');
        }
        return $code;
    }

    public static function get_friends_code($user_id, $amount)
    {
        $user = User::find($user_id);
        $response['code'] = '';
        $response['status'] = false;
        $response['remaning'] = $amount;
        if ($user) {
            $response['code'] = $user['friend_referral'];
            $response['status'] = !empty($user['friend_referral']) ? true : false;
            $response['remaning'] = $amount;
        }
        return $response;
    }

    public static function checkPlan($referral_code, $level, $user_id, $amount)
    {
        $today = Carbon::now()->format('Y-m-d');
        $user = User::where('referral_code', $referral_code)->first();
        if ($user) {
            if (($user->plan_status == 1) && ($user->plan_expire_date > $today) && !empty($user->plan_id)) {
                $plan = Helpers::planData($user->plan_id, $level, $user_id, $user->id, $amount);
                if ($plan) {
                    return $plan;
                } else {
                    return true;
                }
            }
        }
        return false;
    }

    public static function planData($planId, $level, $user_id, $parent_id, $amount)
    {
        $plan = Plan::find($planId);

        if ($plan) {
            $planlevel = PlanLevel::where('plan_id', $planId)->where('level_id', $level)->first();
            if ($planlevel) {

                $bonus = ($planlevel->repurchase_income / 100) * $amount;
                $repurchase_bonus_add = Helpers::repurchaseTransactions($parent_id, $user_id, sprintf("%.2f", $bonus), 'user', null, 'repurchase_bonus', 'Credit', $level);
                // $refferal_add = Helpers::referralTransaction($parent_id, $user_id, $bonus, $level, 'repurchase_bonus');

                $userData = User::find($parent_id);
                // $userData->referral_bonus = $userData->referral_bonus + sprintf("%.2f",$bonus);
                // $userData->repurchase_wallet = $userData->repurchase_wallet + sprintf("%.2f",$bonus);
                $userData->withdrawal_wallet = $userData->withdrawal_wallet + sprintf("%.2f", $bonus);
                $userData->save();

                $remaingAmount = $amount - $bonus;
                return sprintf("%.2f", $remaingAmount);
            }
        }
        return false;
    }

    public static function referralTransaction($parent_id, $referrel_id, $amount, $level, $type = '')
    {
        $insert = [
            'parent_id' => $parent_id,
            'referral_id' => $referrel_id,
            'amount' => $amount,
            'level' => $level,
            'type' => $type,
        ];
        DB::table('referral_transactions')->insert($insert);

        return true;
    }
    public static function repurchaseTransactions($parent_id, $amount, $parent_type, $referrel_id = null,  $zipcode = null, $type = null, $transaction = "Credit", $level = null, $user_type = null)
    {
        $insert = [
            'parent_id' => $parent_id,
            'referral_id' => $referrel_id,
            'amount' => $amount,
            'parent_type' => $parent_type,
            'zipcode' => $zipcode,
            'type' => $type,
            'transaction' => $transaction,
            'level' => $level,
            'user_type' => $user_type,
        ];
        DB::table('repurchase_transactions')->insert($insert);

        return true;
    }


    public static function get_withdrawal_bonus($amount, $user_id, $level = 1)
    {
        $code = Helpers::get_friends_code1($user_id, $amount);
        if ($code['status']) {
            $check = Helpers::checkPlan1($code['code'], $level, $user_id, $amount);
            $user = User::where('referral_code', $code['code'])->first();

            Helpers::get_withdrawal_bonus($user->id, $level + 1, $check); // $check is remaing Amount
        } else {
            $admin = Admin::find(1);
            $admin->user_bonus = $admin->user_bonus + $code['remaning'];
            $admin->save();
            $repurchase_bonus_add = Helpers::repurchaseTransactions(1, $user_id,  $code['remaning'], 'admin', null, 'withdrwal_remaining_bonus', 'Credit');
        }
        return $code;
    }

    public static function get_friends_code1($user_id, $amount)
    {
        $user = User::find($user_id);
        $response['code'] = '';
        $response['status'] = false;
        $response['remaning'] = $amount;
        if ($user) {
            $response['code'] = $user['friend_referral'];
            $response['status'] = !empty($user['friend_referral']) ? true : false;
            $response['remaning'] = $amount;
        }
        return $response;
    }

    public static function checkPlan1($referral_code, $level, $user_id, $amount)
    {
        $today = Carbon::now()->format('Y-m-d');
        $user = User::where('referral_code', $referral_code)->first();
        if ($user) {
            if (($user->plan_status == 1) && ($user->plan_expire_date > $today) && !empty($user->plan_id)) {
                $plan = Helpers::planData1($user->plan_id, $level, $user_id, $user->id, $amount);
                if ($plan) {
                    return $plan;
                } else {
                    return true;
                }
            }
        }
        return false;
    }


    public static function planData1($planId, $level, $user_id, $parent_id, $amount)
    {
        $plan = Plan::find($planId);

        if ($plan) {
            $planlevel = PlanLevel::where('plan_id', $planId)->where('level_id', $level)->first();
            if ($planlevel) {

                $bonus = ($planlevel->frenchise_withdrawal_income / 100) * $amount;
                $repurchase_bonus_add = Helpers::repurchaseTransactions($parent_id, $user_id, sprintf("%.2f", $bonus), 'user', null, 'frenchise_withdrawal_income', 'Credit', $level);
                // $refferal_add = Helpers::referralTransaction($parent_id, $user_id, $bonus, $level, 'repurchase_bonus');

                $userData = User::find($parent_id);
                $userData->referral_bonus = $userData->referral_bonus + sprintf("%.2f", $bonus);
                $userData->save();

                $remaingAmount = $amount - $bonus;
                return sprintf("%.2f", $remaingAmount);
            }
        }
        return false;
    }

    public static function teamCreated($user_id, $parent_id, $level)
    {
        $check = Team::where('user_id', $user_id)
            ->where('parent_id', $parent_id)
            ->where('level', $level)
            ->first();
        if (empty($check)) {
            $team = new Team();
            $team->user_id = $user_id;
            $team->parent_id = $parent_id;
            $team->level = $level;
            $team->daily_bonus_count = 0;
            $team->status = 0;
            $team->save();
        }
        return true;
    }
    public static function teamCount($parent_id, $level)
    {
        $response['user_count'] = 0;
        $response['user_data'] = [];

        $current = date('Y-m-d');
        $check = Team::where('parent_id', $parent_id)
            ->where('level', $level)
            ->first();
        if ($check) {
            $data = Team::select('users.id as user_id', 'users.f_name as first_name', 'users.l_name as last_name', 'users.phone', 'users.created_at as registered_date', 'teams.status')
                ->leftJoin('users', 'teams.user_id', '=', 'users.id')
                ->where('teams.parent_id', $parent_id)
                ->where('teams.level', $level)
                ->where('users.plan_expire_date', '>=', $current)
                ->get();
            $response['user_count'] = count($data);
            $response['user_data'] = $data;
        }
        return $response;
    }

    public static function sellerWalletTransaction($user_id, $transaction_id, $type, $amount, $remark)
    {
        $insert = [
            'user_id' => $user_id,
            'transaction_id' => $transaction_id,
            'type' => $type,
            'amount' => $amount,
            'remark' => $remark
        ];
        DB::table('wallet_seller_transactions')->insert($insert);

        return true;
    }
}


if (!function_exists('currency_symbol')) {
    function currency_symbol()
    {
        Helpers::currency_load();
        if (\session()->has('currency_symbol')) {
            $symbol = \session('currency_symbol');
        } else {
            $system_default_currency_info = \session('system_default_currency_info');
            $symbol = $system_default_currency_info->symbol;
        }
        return $symbol;
    }
}
//formats currency
if (!function_exists('format_price')) {
    function format_price($price)
    {
        return number_format($price, 2) . currency_symbol();
    }
}

function translate($key)
{
    $local = Helpers::default_lang();
    App::setLocale($local);

    try {
        $lang_array = include(base_path('resources/lang/' . $local . '/messages.php'));
        $processed_key = ucfirst(str_replace('_', ' ', Helpers::remove_invalid_charcaters($key)));
        $key = Helpers::remove_invalid_charcaters($key);
        if (!array_key_exists($key, $lang_array)) {
            $lang_array[$key] = $processed_key;
            $str = "<?php return " . var_export($lang_array, true) . ";";
            file_put_contents(base_path('resources/lang/' . $local . '/messages.php'), $str);
            $result = $processed_key;
        } else {
            $result = __('messages.' . $key);
        }
    } catch (\Exception $exception) {
        $result = __('messages.' . $key);
    }

    return $result;
}

function auto_translator($q, $sl, $tl)
{
    $res = file_get_contents("https://translate.googleapis.com/translate_a/single?client=gtx&ie=UTF-8&oe=UTF-8&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&sl=" . $sl . "&tl=" . $tl . "&hl=hl&q=" . urlencode($q), $_SERVER['DOCUMENT_ROOT'] . "/transes.html");
    $res = json_decode($res);
    return str_replace('_', ' ', $res[0][0][0]);
}

function getLanguageCode(string $country_code): string
{
    $locales = array(
        'af-ZA',
        'am-ET',
        'ar-AE',
        'ar-BH',
        'ar-DZ',
        'ar-EG',
        'ar-IQ',
        'ar-JO',
        'ar-KW',
        'ar-LB',
        'ar-LY',
        'ar-MA',
        'ar-OM',
        'ar-QA',
        'ar-SA',
        'ar-SY',
        'ar-TN',
        'ar-YE',
        'az-Cyrl-AZ',
        'az-Latn-AZ',
        'be-BY',
        'bg-BG',
        'bn-BD',
        'bs-Cyrl-BA',
        'bs-Latn-BA',
        'cs-CZ',
        'da-DK',
        'de-AT',
        'de-CH',
        'de-DE',
        'de-LI',
        'de-LU',
        'dv-MV',
        'el-GR',
        'en-AU',
        'en-BZ',
        'en-CA',
        'en-GB',
        'en-IE',
        'en-JM',
        'en-MY',
        'en-NZ',
        'en-SG',
        'en-TT',
        'en-US',
        'en-ZA',
        'en-ZW',
        'es-AR',
        'es-BO',
        'es-CL',
        'es-CO',
        'es-CR',
        'es-DO',
        'es-EC',
        'es-ES',
        'es-GT',
        'es-HN',
        'es-MX',
        'es-NI',
        'es-PA',
        'es-PE',
        'es-PR',
        'es-PY',
        'es-SV',
        'es-US',
        'es-UY',
        'es-VE',
        'et-EE',
        'fa-IR',
        'fi-FI',
        'fil-PH',
        'fo-FO',
        'fr-BE',
        'fr-CA',
        'fr-CH',
        'fr-FR',
        'fr-LU',
        'fr-MC',
        'he-IL',
        'hi-IN',
        'hr-BA',
        'hr-HR',
        'hu-HU',
        'hy-AM',
        'id-ID',
        'ig-NG',
        'is-IS',
        'it-CH',
        'it-IT',
        'ja-JP',
        'ka-GE',
        'kk-KZ',
        'kl-GL',
        'km-KH',
        'ko-KR',
        'ky-KG',
        'lb-LU',
        'lo-LA',
        'lt-LT',
        'lv-LV',
        'mi-NZ',
        'mk-MK',
        'mn-MN',
        'ms-BN',
        'ms-MY',
        'mt-MT',
        'nb-NO',
        'ne-NP',
        'nl-BE',
        'nl-NL',
        'pl-PL',
        'prs-AF',
        'ps-AF',
        'pt-BR',
        'pt-PT',
        'ro-RO',
        'ru-RU',
        'rw-RW',
        'sv-SE',
        'si-LK',
        'sk-SK',
        'sl-SI',
        'sq-AL',
        'sr-Cyrl-BA',
        'sr-Cyrl-CS',
        'sr-Cyrl-ME',
        'sr-Cyrl-RS',
        'sr-Latn-BA',
        'sr-Latn-CS',
        'sr-Latn-ME',
        'sr-Latn-RS',
        'sw-KE',
        'tg-Cyrl-TJ',
        'th-TH',
        'tk-TM',
        'tr-TR',
        'uk-UA',
        'ur-PK',
        'uz-Cyrl-UZ',
        'uz-Latn-UZ',
        'vi-VN',
        'wo-SN',
        'yo-NG',
        'zh-CN',
        'zh-HK',
        'zh-MO',
        'zh-SG',
        'zh-TW'
    );

    foreach ($locales as $locale) {
        $locale_region = explode('-', $locale);
        if (strtoupper($country_code) == $locale_region[1]) {
            return $locale_region[0];
        }
    }

    return "en";
}

function hex2rgb($colour)
{
    if ($colour[0] == '#') {
        $colour = substr($colour, 1);
    }
    if (strlen($colour) == 6) {
        list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
    } elseif (strlen($colour) == 3) {
        list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
    } else {
        return false;
    }
    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);
    return array('red' => $r, 'green' => $g, 'blue' => $b);
}

if (!function_exists('customer_info')) {
    function customer_info()
    {
        return User::where('id', auth('customer')->id())->first();
    }
}

if (!function_exists('order_status_history')) {
    function order_status_history($order_id, $status)
    {
        return OrderStatusHistory::where(['order_id' => $order_id, 'status' => $status])->latest()->pluck('created_at')->first();
    }
}

if (!function_exists('get_shop_name')) {
    function get_shop_name($seller_id)
    {
        return Shop::where(['seller_id' => $seller_id])->first()->name;
    }
}

if (!function_exists('hex_to_rgb')) {
    function hex_to_rgb($hex)
    {
        $result = preg_match('/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i', $hex, $matches);
        $data = $result ? hexdec($matches[1]) . ', ' . hexdec($matches[2]) . ', ' . hexdec($matches[3]) : null;

        return $data;
    }
}
if (!function_exists('get_color_name')) {
    function get_color_name($code)
    {
        return Color::where(['code' => $code])->first()->name;
    }
}
