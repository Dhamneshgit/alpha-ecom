<?php

namespace App\Http\Controllers\Seller\Auth;

use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\Seller;
use App\Model\Shop;
use App\Model\Zipcode;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\CPU\Helpers;
use Illuminate\Support\Facades\Session;
use function App\CPU\translate;

class RegisterController extends Controller
{
    public function create()
    {
        $business_mode = Helpers::get_business_settings('business_mode');
        $seller_registration = Helpers::get_business_settings('seller_registration');
        $zipcodes = Zipcode::all();

        if ((isset($business_mode) && $business_mode == 'single') || (isset($seller_registration) && $seller_registration == 0)) {
            Toastr::warning(translate('access_denied!!'));
            return redirect('/');
        }
        return view(VIEW_FILE_NAMES['seller_registration'], compact(
            'zipcodes'
        ));
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'image'         => 'required|mimes: jpg,jpeg,png,gif',
            // 'aadhar_front_img'   => 'required|mimes: jpg,jpeg,png,gif',
            // 'aadhar_back_img'   => 'required|mimes: jpg,jpeg,png,gif',
            'logo'          => 'required|mimes: jpg,jpeg,png,gif',
            'banner'        => 'required|mimes: jpg,jpeg,png,gif',
            'bottom_banner' => 'mimes: jpg,jpeg,png,gif',
            'email'         => 'required|unique:sellers',
            'shop_address'  => 'required',
            'f_name'        => 'required',
            'l_name'        => 'required',
            'shop_name'     => 'required',
            'phone'         => 'required',
            'type'         => 'required',

            // 'dob' => 'required',
            // 'pan_card' => 'required|unique:sellers',
            // 'address' => 'required',
            // 'gst' => 'required|unique:sellers',
            // 'aadhar' => 'required|unique:sellers',
            // 'gender' => 'required',
            // 'zipcode'       => 'required',
            'password'      => 'required|min:8',
        ]);

        // if ($request['from_submit'] != 'admin') {
        //     //recaptcha validation
        //     $recaptcha = Helpers::get_business_settings('recaptcha');
        //     if (isset($recaptcha) && $recaptcha['status'] == 1) {
        //         try {
        //             $request->validate([
        //                 'g-recaptcha-response' => [
        //                     function ($attribute, $value, $fail) {
        //                         $secret_key = Helpers::get_business_settings('recaptcha')['secret_key'];
        //                         $response = $value;
        //                         $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response;
        //                         $response = \file_get_contents($url);
        //                         $response = json_decode($response);
        //                         if (!$response->success) {
        //                             $fail(\App\CPU\translate('ReCAPTCHA Failed'));
        //                         }
        //                     },
        //                 ],
        //             ]);
        //         } catch (\Exception $exception) {
        //         }
        //     } else {
        //         if (strtolower($request->default_recaptcha_id_seller_regi) != strtolower(Session('default_recaptcha_id_seller_regi'))) {
        //             Session::forget('default_recaptcha_id_seller_regi');
        //             return back()->withErrors(\App\CPU\translate('Captcha Failed'));
        //         }
        //     }
        // }

        $check = Seller::where('zipcode', $request->zipcode)->first();
        if (!empty($check)) {
            Toastr::error('Seller is already registered on ' . $request->zipcode . 'this zipcode!');
            return back();
        }

        $request->vendorID = $this->createVendorId($request->zipcode, $request->type);




        DB::transaction(function ($r) use ($request) {
            // $zipcodeData = DB::table('zipcode')->where('zipcode', $request->zipcode)->first();
            // $cityData = DB::table('cities')->where('id', $zipcodeData->city_id)->first();

            $seller = new Seller();
            $seller->f_name = $request->f_name;
            $seller->l_name = $request->l_name;
            $seller->unique_code = $request->vendorID;
            $seller->phone = $request->phone;
            $seller->email = $request->email;
            $seller->type = $request->type ?? 'goods';
            // $seller->dob = $request->dob;
            $seller->pan_card = $request->pan_card;
            $seller->address = $request->shop_address;
            $seller->gst = $request->gst;
            // $seller->gender = $request->gender;
            $seller->aadhar = $request->aadhar;
            $seller->zipcode = $request->zipcode;
            $seller->image = ImageManager::upload('seller/', 'png', $request->file('image'));
            $seller->aadhar_front_img = ImageManager::upload('seller/', 'png', $request->file('aadhar_front_img'));
            $seller->aadhar_back_img = ImageManager::upload('seller/', 'png', $request->file('aadhar_front_img'));
            $seller->password = bcrypt($request->password);
            $seller->status =  $request->status == 'approved' ? 'approved' : "pending";
            $seller->city =  $request->city;
            $seller->state =  $request->state;
            $seller->area =  $request->area;
            $seller->latitude =  $request->latitude;
            $seller->longitude =  $request->longitude;
            $seller->save();

            $shop = new Shop();
            $shop->seller_id = $seller->id;
            $shop->seller_type = $request->type ?? 'goods';
            $shop->name = $request->shop_name;
            $shop->address = $request->shop_address;
            $shop->contact = $request->phone;
            $shop->image = ImageManager::upload('shop/', 'png', $request->file('logo'));
            $shop->banner = ImageManager::upload('shop/banner/', 'png', $request->file('banner'));
            $shop->bottom_banner = ImageManager::upload('shop/banner/', 'png', $request->file('bottom_banner'));
            $shop->save();

            DB::table('seller_wallets')->insert([
                'seller_id' => $seller['id'],
                'withdrawn' => 0,
                'commission_given' => 0,
                'total_earning' => 0,
                'pending_withdraw' => 0,
                'delivery_charge_earned' => 0,
                'collected_cash' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        if ($request->status == 'approved') {
            Toastr::success('Shop apply successfully!');
            return back();
        } else {
            Toastr::success('Shop apply successfully!');
            return redirect()->route('seller.auth.login');
        }
    }

    public function getSellers()
    {
        $sellers = Seller::get();
        foreach ($sellers as $key => $value) {
            $pincode = 600001;
            $vendorType = $value->type;
            $vendorID = $this->createVendorId($pincode, $vendorType);
            // dd($vendorID);

            $update = Seller::where('id', $value->id)->update(['unique_code' => $vendorID, 'zipcode' => $pincode]);
        }
        return true;
    }


    public function createVendorId($pincode, $vendorType)
    {

        $vendorTypeMap = [
            'both' => 'B',
            'goods' => 'G',
            'service' => 'S'
        ];

        // Extract Required Values
        $townway = 'TW'; // Static
        $vendorCode = $vendorTypeMap[$vendorType];
        $pincodePrefix = substr($pincode, 0, 2);

        // Generate Serial Number
        $lastVendor = Seller::where('zipcode', 'LIKE', $pincodePrefix . '%')
            ->where('type', $vendorType)
            ->orderBy('id', 'desc')
            ->first();


        // Determine Next Serial Number
        if (($lastVendor) && ($lastVendor->unique_code)) {
            $lastSerial = (int)substr($lastVendor->unique_code, 5);
            $nextSerial = $lastSerial + 1;
        } else {
            $nextSerial = 1001; // Start Serial at 1001
        }
        // dd($nextSerial);

        $vendorId = $townway . $pincodePrefix . $vendorCode . $nextSerial;

        return $vendorId;
    }

    // public function store(Request $request)
    // {

    //     $this->validate($request, [
    //         'image'         => 'required|mimes: jpg,jpeg,png,gif',
    //         'logo'          => 'required|mimes: jpg,jpeg,png,gif',
    //         'banner'        => 'required|mimes: jpg,jpeg,png,gif',
    //         'bottom_banner' => 'mimes: jpg,jpeg,png,gif',
    //         'email'         => 'required|unique:sellers',
    //         'shop_address'  => 'required',
    //         'f_name'        => 'required',
    //         'l_name'        => 'required',
    //         'shop_name'     => 'required',
    //         'phone'         => 'required',
    //         'zipcode'       => 'required|unique:sellers',
    //         'password'      => 'required|min:8',
    //     ]);

    //     if($request['from_submit'] != 'admin') {
    //         //recaptcha validation
    //         $recaptcha = Helpers::get_business_settings('recaptcha');
    //         if (isset($recaptcha) && $recaptcha['status'] == 1) {
    //             try {
    //                 $request->validate([
    //                     'g-recaptcha-response' => [
    //                         function ($attribute, $value, $fail) {
    //                             $secret_key = Helpers::get_business_settings('recaptcha')['secret_key'];
    //                             $response = $value;
    //                             $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response;
    //                             $response = \file_get_contents($url);
    //                             $response = json_decode($response);
    //                             if (!$response->success) {
    //                                 $fail(\App\CPU\translate('ReCAPTCHA Failed'));
    //                             }
    //                         },
    //                     ],
    //                 ]);
    //             } catch (\Exception $exception) {
    //             }
    //         } else {
    //             if (strtolower($request->default_recaptcha_id_seller_regi) != strtolower(Session('default_recaptcha_id_seller_regi'))) {
    //                 Session::forget('default_recaptcha_id_seller_regi');
    //                 return back()->withErrors(\App\CPU\translate('Captcha Failed'));
    //             }
    //         }
    //     }
    //     // dd($request->zipcode);
    //     $check = Seller::where('zipcode',$request->zipcode)->first();
    //     if(!empty($check)){
    //         Toastr::error('Seller is already registered on '. $request->zipcode .'this zipcode!');
    //         return back();
    //     }


    //     DB::transaction(function ($r) use ($request) {
    //         $zipcodeData = DB::table('zipcode')->where('zipcode',$request->zipcode)->first();
    //         $cityData = DB::table('cities')->where('id',$zipcodeData->city_id)->first(); 

    //         $seller = new Seller();
    //         $seller->f_name = $request->f_name;
    //         $seller->l_name = $request->l_name;
    //         $seller->phone = $request->phone;
    //         $seller->email = $request->email;
    //         $seller->zipcode = $request->zipcode;
    //         $seller->image = ImageManager::upload('seller/', 'png', $request->file('image'));
    //         $seller->password = bcrypt($request->password);
    //         $seller->status =  $request->status == 'approved'?'approved': "pending";
    //         $seller->city =  $cityData->city ?? '';
    //         $seller->state =  $cityData->state ?? '';
    //         $seller->save();

    //         $shop = new Shop();
    //         $shop->seller_id = $seller->id;
    //         $shop->name = $request->shop_name;
    //         $shop->address = $request->shop_address;
    //         $shop->contact = $request->phone;
    //         $shop->image = ImageManager::upload('shop/', 'png', $request->file('logo'));
    //         $shop->banner = ImageManager::upload('shop/banner/', 'png', $request->file('banner'));
    //         $shop->bottom_banner = ImageManager::upload('shop/banner/', 'png', $request->file('bottom_banner'));
    //         $shop->save();

    //         DB::table('seller_wallets')->insert([
    //             'seller_id' => $seller['id'],
    //             'withdrawn' => 0,
    //             'commission_given' => 0,
    //             'total_earning' => 0,
    //             'pending_withdraw' => 0,
    //             'delivery_charge_earned' => 0,
    //             'collected_cash' => 0,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //     });

    //     if($request->status == 'approved'){
    //         Toastr::success('Shop apply successfully!');
    //         return back();
    //     }else{
    //         Toastr::success('Shop apply successfully!');
    //         return redirect()->route('seller.auth.login');
    //     }


    // }
}
