<?php

namespace App\Http\Controllers\api\v2\delivery_man\auth;

use App\CPU\Helpers;
use App\CPU\ImageManager;

use App\CPU\SMS_module;
use App\Http\Controllers\Controller;
use App\Model\DeliveryMan;
use App\Model\PasswordReset;
use App\Model\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        /**
         * checking if existing delivery man has a country code or not
         */

        $d_man = DeliveryMan::where(['phone' => $request->phone])->first();

        if ($d_man && isset($d_man->country_code) && ($d_man->country_code != $request->country_code)) {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Invalid credential or account suspended']);
            return response()->json([
                'errors' => $errors
            ], 404);
        }

        if (isset($d_man) && $d_man['is_active'] == 1 && Hash::check($request->password, $d_man->password)) {
            $token = Str::random(50);
            $d_man->auth_token = $token;
            $d_man->fcm_token = $request->device_token ?? null;
            $d_man->save();
            return response()->json(['token' => $token], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Invalid credential or account suspended']);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }



    // public function add_delivery_boy(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'f_name' => 'required',
    //         'l_name' => 'required',
    //         'phone' => 'required',
    //         'email' => 'required|unique:delivery_men,email',
    //         'country_code' => 'required',
    //         'password' => 'required|min:8',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }
        

        

    //     $phone_combo_exists = DeliveryMan::where(['phone' => $request->phone, 'country_code' => $request->country_code])->exists();

    //     if ($phone_combo_exists) {
    //         return response()->json([
    //             'status' => 'error',
    //             'errors' => [
    //                 [
    //                     'code' => 'not-found',
    //                     'message' => translate('This_phone_number_is_already_taken')
    //                 ]
    //             ]
    //         ], 400); 
    //     }

       
    //     $id_img_names = [];
    //     if ($request->hasFile('identity_image')) {
    //         foreach ($request->identity_image as $img) {
    //             $id_img_names[] = ImageManager::upload('delivery-man/', 'png', $img);
    //         }
    //         $identity_image = json_encode($id_img_names);
    //     } else {
    //         $identity_image = json_encode([]);
    //     }

       
    //     try {
    //         $dm = new DeliveryMan();
    //         $dm->seller_id = 0;
    //         $dm->f_name = $request->f_name;
    //         $dm->l_name = $request->l_name;
    //         $dm->address = $request->address;
    //         $dm->email = $request->email;
    //         $dm->country_code = $request->country_code;
    //         $dm->phone = $request->phone;
    //         $dm->identity_number = $request->identity_number;
    //         $dm->identity_type = $request->identity_type;
    //         $dm->identity_image = $identity_image;
    //         $dm->image = ImageManager::upload('delivery-man/', 'png', $request->file('image'));
    //         $dm->password = bcrypt($request->password);
    //         $dm->save();

    //         dd($dm);

           
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => translate('Delivery_man_added_successfully!')
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => translate('An_error_occurred_while_adding_the_delivery_man')
    //         ], 500); 
    //     }
    // }


    public function add_delivery_boy(Request $request)
{
    $validator = Validator::make($request->all(), [
        'f_name' => 'required',
        'l_name' => 'required',
        'phone' => 'required',
        'email' => 'required|unique:delivery_men,email',
        'country_code' => 'required',
        'password' => 'required|min:8',
        'identity_number' => 'nullable',  
        'identity_type' => 'nullable',    
        'address' => 'nullable',         
        'image' => 'nullable|image|mimes:png,jpg,jpeg',  
        'identity_image' => 'nullable|array', 
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    }

    $phone_combo_exists = DeliveryMan::where(['phone' => $request->phone, 'country_code' => $request->country_code])->exists();

    if ($phone_combo_exists) {
        return response()->json([
            'status' => 'error',
            'errors' => [
                [
                    'code' => 'not-found',
                    'message' => translate('This_phone_number_is_already_taken')
                ]
            ]
        ], 400); 
    }

    // $id_img_names = [];
    // if ($request->hasFile('identity_image')) {
    //     foreach ($request->identity_image as $img) {
    //         $id_img_names[] = ImageManager::upload('delivery-man/', 'png', $img);
    //     }
    //     $identity_image = json_encode($id_img_names);
    // } else {
    //     $identity_image = json_encode([]);  
    // }
    
    // dd($request->file('identity_image'));
    $id_img_names = [];
    if (!empty($request->file('identity_image'))) {
        foreach ($request->identity_image as $img) {
            array_push($id_img_names, ImageManager::upload('delivery-man/', 'png', $img));
        }
        $identity_image = json_encode($id_img_names);
    } else {
        $identity_image = json_encode([]);
    }

    try {
        $dm = new DeliveryMan();
        $dm->seller_id = 0; 
        $dm->f_name = $request->f_name;
        $dm->l_name = $request->l_name;
        $dm->address = $request->address; 
        $dm->zipcode = $request->pincode;
        $dm->city =  $request->city; 
        $dm->state =  $request->state;
        $dm->area =  $request->area;
        $dm->latitude =  $request->latitude;
        $dm->longitude =  $request->longitude; 
        $dm->email = $request->email;
        $dm->country_code = $request->country_code;
        $dm->phone = $request->phone;
        $dm->identity_number = $request->identity_number;  
        $dm->identity_type = $request->identity_type;     
        $dm->identity_image = $identity_image;  
        $dm->image = $request->hasFile('image') ? ImageManager::upload('delivery-man/', 'png', $request->file('image')) : null;  // Upload image if provided
        $dm->password = bcrypt($request->password); 
        $dm->save();  
        return response()->json([
            'status' => 'success',
            'message' => translate('Delivery_man_added_successfully!')
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => translate('An_error_occurred_while_adding_the_delivery_man')
        ], 500); 
    }
}

    
    
    public function reset_password_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        /**
         * Delete previous unused reset request
         */
        PasswordReset::where(['user_type' => 'delivery_man', 'identity' => $request->phone])->delete();

        $delivery_man = DeliveryMan::where(['phone' => $request->phone])->first();

        if ($delivery_man && isset($delivery_man->country_code) && ($delivery_man->country_code != $request->country_code)) {
            return response()->json(['errors' => [
                ['code' => 'not-found', 'message' => translate('user_not_found')]
            ]], 404);
        }

        if (isset($delivery_man)) {
            $otp = rand(1000, 9999);

            PasswordReset::insert([
                'identity' => $delivery_man->phone,
                'token' => $otp,
                'user_type' => 'delivery_man',
                'created_at' => now(),
            ]);

            $emailServices_smtp = Helpers::get_business_settings('mail_config');

            if ($emailServices_smtp['status'] == 0) {
                $emailServices_smtp = Helpers::get_business_settings('mail_config_sendgrid');
            }
            if ($emailServices_smtp['status'] == 1) {
                Mail::to($delivery_man['email'])->send(new \App\Mail\DeliverymanPasswordResetMail($otp));
            } else {
                return response()->json(['message' => translate('email_failed')], 200);
            }

            $phone_number = $delivery_man->country_code ? '+' . $delivery_man->country_code . $delivery_man->phone : $delivery_man->phone;
            SMS_module::send($phone_number, $otp);
            return response()->json(['message' => translate('OTP_sent_successfully._Please_check_your_email_or_phone')], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'not-found', 'message' => translate('user_not_found')]
        ]], 404);
    }

    public function otp_verification_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = PasswordReset::where(['token' => $request['otp'], 'user_type' => 'delivery_man'])->first();

        if (!$data) {
            return response()->json(['message' => translate('Invalid_OTP')], 403);
        }

        $time_diff = $data->created_at->diffInMinutes(Carbon::now());

        if ($time_diff > 2) {
            PasswordReset::where(['token' => $request['otp'], 'user_type' => 'delivery_man'])->delete();

            return response()->json(['message' => translate('OTP_expired')], 403);
        }

        $phone = DeliveryMan::where(['phone' => $data->identity])->pluck('phone')->first();

        return response()->json(['message' => translate('OTP_verified_successfully'), 'phone' => $phone], 200);
    }


    public function reset_password_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|same:confirm_password|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        DeliveryMan::where(['phone' => $request['phone']])
            ->update(['password' => bcrypt(str_replace(' ', '', $request['password']))]);

        PasswordReset::where(['identity' => $request['phone'], 'user_type' => 'delivery_man'])->delete();

        return response()->json(['message' => translate('Password_changed_successfully')], 200);
    }

    // delivery_boy list according to seller city
    public function delivery_boy_list(Request $request)
{
    $city = $request->input('city');

    $delivery_men = DeliveryMan::with(['rating']);

    // City filter
    if ($city) {
        $delivery_men = $delivery_men->where('city', $city);
    }

    // Count orders and other filters
    $delivery_men = $delivery_men->withCount(['orders' => function($q) {
        return $q;
    }])
    ->where(['seller_id' => 0])
    ->latest()
    ->get();

    // Prepare response
    return response()->json($delivery_men);
}

}
