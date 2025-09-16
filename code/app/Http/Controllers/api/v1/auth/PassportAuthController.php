<?php

namespace App\Http\Controllers\api\v1\auth;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use App\CPU\ImageManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;
use DB;
use App\Model\Plan;
use App\Model\PlanTransaction;
use App\Model\PlanLevel;
use App\Model\Seller;
use App\Model\Admin;
use App\Model\Zipcode;

class PassportAuthController extends Controller
{
    public function register(Request $request)
    {
        // dd($_POST);
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|min:8',
        ], [
            'f_name.required' => 'The first name field is required.',
            'l_name.required' => 'The last name field is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $temporary_token = Str::random(40);
        $referal_code = Str::random(15);
        // dd($request);
        $phone_verification = Helpers::get_business_settings('signup_bonus');

        $user = User::create([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => 1,
            'password' => bcrypt($request->password),
            'temporary_token' => $temporary_token,
            'cm_firebase_token' => $request->device_token,
            // 'referral_code' => $request->phone, //$referal_code,
            'zipcode' => $request->zipcode,
            'area' => $request->area,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            // 'wallet_balance' => $phone_verification,
            // 'friend_referral' => !empty($request->friend_code) ? $request->friend_code : '',
            // 'zipcode' => !empty($request->zipcode) ? $request->zipcode : '',
        ]);

        // if ($request->zipcode) {
        //     $data = Admin::where('zipcode', $request->zipcode)->first();
        //     if ($data) {
        //         $zipcode = Zipcode::where('zipcode', $request->zipcode)->first();
        //         $data->user_bonus = $data->user_bonus + (isset($zipcode) ? $zipcode->bonus : 0);
        //         $data->save();
        //     }
        // }

        // $phone_verification = Helpers::get_business_settings('signup_bonus');

        // $userdata->wallet_balance = ($userdata->wallet_balance) ? $userdata->wallet_balance + $phone_verification : $phone_verification;
        //         $userdata->save();
        // $transaction = [
        //     'user_id' => $user->id,
        //     'transaction_id' => 'sing_up_bonus',
        //     'credit' => $phone_verification,
        //     'transaction_type' => 'Sign_up_bonus',
        //     'type' => 'credit'
        // ];
        // DB::table('wallet_transactions')->insert($transaction);

        $phone_verification = Helpers::get_business_settings('phone_verification');
        $email_verification = Helpers::get_business_settings('email_verification');
        if ($phone_verification && !$user->is_phone_verified) {
            return response()->json(['temporary_token' => $temporary_token], 200);
        }
        if ($email_verification && !$user->is_email_verified) {
            return response()->json(['temporary_token' => $temporary_token], 200);
        }

        $token = $user->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token, 'user' => $user], 200);
    }
    public function registerDoctor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'license_number' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required',
            'phone' => 'required',
            'pan_card' => 'required|unique:admins',
            'address' => 'required',
            'gender' => 'required',

        ], [
            'name.required' => 'name is required!',
            'email.required' => 'Email id is Required',
            'email.unique' => 'Email id is Already taken',
            'pan_card.required' => 'Pan Card No. is Required',
            'address.required' => 'address is Required',
            'gender.required' => 'Gender is Required',
            'license_number.required' => 'Medical Registration is Required',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $doctor = DB::table('admins')->insert([
            'name' => $request->name,
            'phone' => $request->phone,
            'qualification' => ($request->qualification) ? implode(',',$request->qualification) : null,
            'passing_year' => ($request->passing_year) ? implode(',',$request->passing_year) : null,
            'clinic_name' => $request->clinic_name,
            'clinic_address' => $request->clinic_address,
            'license_number' => $request->license_number,
            'medical_council' => $request->medical_council,
            'email' => $request->email,
            'pan_card' => $request->pan_card,
            'address' => $request->address,
            'gender' => $request->gender,
            'admin_role_id' => 5,//$request->role_id,
            'password' => bcrypt($request->password),
            'status' => 0,
            'certificate_image' => ImageManager::upload('admin/', 'png', $request->file('certificate')),
            'standard_aggrement' => ImageManager::upload('admin/', 'pdf', $request->file('standard_aggrement')),
            'image' => ImageManager::upload('admin/', 'png', $request->file('image')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if($doctor){
            return response()->json(['status' => true, 'message' => 'Registered successfully! Please wait for Approval'], 200);
        }else {
            return response()->json(['status' => false, 'message' => 'Somthing went wrong'], 200);
        }
        
    }
    public function registerManufacture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required',
            'phone' => 'required',
            'pan_card' => 'required|unique:admins',
            'address' => 'required',
            'gst' => 'required|unique:admins',

        ], [
            'name.required' => 'Name is required!',
            'email.required' => 'Email id is Required',
            'pan_card.required' => 'Pan Card No. is Required',
            'address.required' => 'address is Required',
            'gst.required' => 'Gst is Required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $manufacture =  DB::table('admins')->insert([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'pan_card' => $request->pan_card,
            'address' => $request->address,
            'gst' => $request->gst,
            'specialization' => $request->specialization,
            'admin_role_id' => 2,
            'password' => bcrypt($request->password),
            'status' => 0,
            'image' => ImageManager::upload('admin/', 'png', $request->file('image')),
            'created_at' => now(),
            'updated_at' => now(),

        ]);

        if($manufacture){
            return response()->json(['status' => true, 'message' => 'Registered successfully! Please wait for Approval'], 200);
        }else {
            return response()->json(['status' => false, 'message' => 'Somthing went wrong'], 200);
        }
        
    }
    public function registerAggregator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'bank_statement' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required',
            'phone' => 'required',
            'pan_card' => 'required|unique:admins',
            'address' => 'required',
            'gst' => 'required|unique:admins',
            'aadhar_number' => 'required|unique:admins',
            'zipcode'=>'required|unique:admins'

        ], [
            'name.required' => 'Name is required!',
            'bank_statement.required' => 'Bank statement is required!',
            'email.required' => 'Email id is Required',
            'email.unique' => 'This Email is already in use',
            'pan_card.required' => 'Pan Card No. is Required',
            'address.required' => 'address is Required',
            'gst.required' => 'Gst is Required',
            'aadhar_number.required' => 'Aadhar is Required',
            'aadhar_number.unique' => 'This Aadhar Number is already in use',
            'zipcode.required' => 'Zipcode is Required',
            'zipcode.unique' => 'This zipcode is already in use',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $aggregator =   DB::table('admins')->insert([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'pan_card' => $request->pan_card,
            'address' => $request->address,
            'gst' => $request->gst,
            'zipcode' => $request->zipcode,
            'aadhar_number' => $request->aadhar_number,
            'admin_role_id' => 3,
            'password' => bcrypt($request->password),
            'status' => 0,
            'image' => ImageManager::upload('admin/', 'png', $request->file('image')),
            'bank_statement' => ImageManager::upload('admin/', 'pdf', $request->file('bank_statement')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if($aggregator){
            return response()->json(['status' => true, 'message' => 'Registered successfully! Please wait for Approval'], 200);
        }else {
            return response()->json(['status' => false, 'message' => 'Somthing went wrong'], 200);
        }
        
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user_id = $request['email'];
        $medium = 'phone';
        // if (filter_var($user_id, FILTER_VALIDATE_EMAIL)) {
        //     $medium = 'email';
        // } else {
        //     $count = strlen(preg_replace("/[^\d]/", "", $user_id));
        //     if ($count >= 9 && $count <= 15) {
        //         $medium = 'phone';
        //     } else {
        //         $errors = [];
        //         array_push($errors, ['code' => 'email', 'message' => 'Invalid email address or phone number']);
        //         return response()->json([
        //             'errors' => $errors
        //         ], 403);
        //     }
        // }

        $data = [
            $medium => $user_id,
            'password' => $request->password
        ];

        $user = User::where([$medium => $user_id])->first();
        $max_login_hit = Helpers::get_business_settings('maximum_login_hit') ?? 5;
        $temp_block_time = Helpers::get_business_settings('temporary_login_block_time') ?? 5; //minute

        if (isset($user)) {
            $user->temporary_token = Str::random(40);
            $user->cm_firebase_token = $request->device_token ?? null;
            $user->save();

            $phone_verification = Helpers::get_business_settings('phone_verification');
            $email_verification = Helpers::get_business_settings('email_verification');
            if ($phone_verification && !$user->is_phone_verified) {
                return response()->json(['temporary_token' => $user->temporary_token], 200);
            }
            if ($email_verification && !$user->is_email_verified) {
                return response()->json(['temporary_token' => $user->temporary_token], 200);
            }

            if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->diffInSeconds() <= $temp_block_time) {
                $time = $temp_block_time - Carbon::parse($user->temp_block_time)->diffInSeconds();

                $errors = [];
                array_push($errors, ['code' => 'auth-001', 'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()]);
                return response()->json([
                    'errors' => $errors
                ], 401);
            }

            // if($user->plan_status){
            //     $errors = [];
            //     array_push($errors, ['code' => 'auth-001', 'message' => translate('You_have_no_active_plans_,_please_purchase_plan')]);
            //     return response()->json([
            //         'errors' => $errors
            //     ], 401);
            // }

            if ($user->is_active && auth()->attempt($data)) {
                $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;

                $user->login_hit_count = 0;
                $user->is_temp_blocked = 0;
                $user->temp_block_time = null;
                $user->updated_at = now();
                $user->save();

                return response()->json(['token' => $token, 'plan_status' => $user->plan_status, 'user' => $user], 200);
            } else {
                //login attempt check start
                if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->diffInSeconds() <= $temp_block_time) {
                    $time = $temp_block_time - Carbon::parse($user->temp_block_time)->diffInSeconds();

                    $errors = [];
                    array_push($errors, ['code' => 'auth-001', 'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()]);
                    return response()->json([
                        'errors' => $errors
                    ], 401);
                } elseif ($user->is_temp_blocked == 1 && Carbon::parse($user->temp_block_time)->diffInSeconds() >= $temp_block_time) {

                    $user->login_hit_count = 0;
                    $user->is_temp_blocked = 0;
                    $user->temp_block_time = null;
                    $user->updated_at = now();
                    $user->save();

                    $errors = [];
                    array_push($errors, ['code' => 'auth-001', 'message' => translate('credentials_do_not_match_or_account_has_been_suspended')]);
                    return response()->json([
                        'errors' => $errors
                    ], 401);
                } elseif ($user->login_hit_count >= $max_login_hit &&  $user->is_temp_blocked == 0) {
                    $user->is_temp_blocked = 1;
                    $user->temp_block_time = now();
                    $user->updated_at = now();
                    $user->save();

                    $time = $temp_block_time - Carbon::parse($user->temp_block_time)->diffInSeconds();

                    $errors = [];
                    array_push($errors, ['code' => 'auth-001', 'message' => translate('too_many_attempts. please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()]);
                    return response()->json([
                        'errors' => $errors
                    ], 401);
                } else {

                    $user->login_hit_count += 1;
                    $user->save();

                    $errors = [];
                    array_push($errors, ['code' => 'auth-001', 'message' => translate('credentials_do_not_match_or_account_has_been_suspended')]);
                    return response()->json([
                        'errors' => $errors
                    ], 401);
                }
                //login attempt check end
            }
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => translate('Customer_not_found_or_Account_has_been_suspended')]);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }

    public function addkyc(Request $request)
    {

        $data = [
            'user_id' => $request->input('user_id'),
            'type' => $request->input('type'),
            'pan_number' => $request->input('pan_number'),
            'adhar_number' => $request->input('adhar_number'),
            'nomini_name' => $request->input('nomini_name'),
            'nomini_relation' => $request->input('nomini_relation'),
            'holder_name' => $request->input('holder_name'),
            'account_number' => $request->input('account_number'),
            'ifsc' => $request->input('ifsc'),
            'status' => 0,
            'bank_name' => $request->input('bank_name'),
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

        $checkuser = DB::table('kyc_details')->where('user_id', $request->input('user_id'))->first();
        if ($checkuser) {
            $check = DB::table('kyc_details')->where('user_id', $request->input('user_id'))->update($data);
        } else {
            $check = DB::table('kyc_details')->insert($data);
        }

        if ($check) {
            return response()->json([
                'status' => true,
                'data' => $check
            ], 200);
        } else {
            return response()->json([
                'status' => false,

            ], 200);
        }
    }
    public function getKyc(Request $request)
    {

        $data = DB::table('kyc_details')->where('user_id', $request['user_id'])->where('type', $request['type'])->first();

        if ($data) {
            if ($data->pan_image) {
                $data->pan_image = asset('public/images/' . $data->pan_image);
            }
            if ($data->adhar_front) {
                $data->adhar_front = asset('public/images/' . $data->adhar_front);
            }
            if ($data->adhar_back) {
                $data->adhar_back = asset('public/images/' . $data->adhar_back);
            }
            if ($data->passbook_image) {
                $data->passbook_image = asset('public/images/' . $data->passbook_image);
            }
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data not Found',
                'data' => $data
            ], 200);
        }
    }


    public function plans()
    {
        $plans = Plan::where('status', 1)->get();
        if ($plans) {
            foreach ($plans as $key => $value) {
                // $levels = [];
                $levels = PlanLevel::where('plan_id', $value['id'])->get();
                if ($levels) {
                    foreach ($levels as $k => $val) {
                        $levels[$k]['amount'] = number_format($val['amount'], 2);
                    }
                }
                $plans[$key]['levels'] = $levels;
            }

            return response()->json([
                'status' => true,
                'data' => $plans
            ], 200);
        } else {
            return response()->json(['message' => translate('No_plans_available')], 422);
        }
    }
    public function cities(Request $request)
    {
        $id = $request['zipcode_id'] ?? '';
        if ($id != '') {
            $zipcode = Zipcode::find($id);
            $plans = DB::table('cities')->where('id', $zipcode['city_id'])->first();
            return response()->json([
                'status' => true,
                'data' => $plans
            ], 200);
        } else {
            return response()->json(['message' => translate('No_cities_available')], 422);
        }
    }
    public function getUserByRefferal(Request $request)
    {
        $code = $request['code'] ?? '';
        if ($code != '') {

            $user = User::where('referral_code', $code)->first();
            if ($user) {
                return response()->json([
                    'status' => true,
                    'message' => 'Success',
                    'data' => $user
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'user not Success',
                    'data' => []
                ], 200);
            }
        } else {
            return response()->json(['message' => translate('No_cities_available')], 422);
        }
    }
    public function my_plan(Request $request)
    {
        $userData = Seller::find($request->user_id);
        if ($userData) {
            if ($userData->plan_id) {
                $plans = Plan::find($userData->plan_id);
                if ($plans) {
                    $data = array();
                    // $plans->levels = PlanLevel::where('plan_id', $plans->id)->get();

                    $plan_purchase_data = PlanTransaction::with('plan')->where('seller_id', $request->user_id)->orderBy('id', 'DESC')->get();
                    $data['purchase_date'] =$userData->start_date;
                    $data['expire_date'] = $userData->end_date;
                    $data['active_plan']= $plans;
                    $data['plan_history'] = $plan_purchase_data;

                    return response()->json([
                        'status' => true,
                        'data' => $data
                    ], 200);
                } else {
                    return response()->json(['status' => false, 'message' => translate('plan_not_found')]);
                }
            } else {
                return response()->json(['status' => false, 'message' => translate('no_plans_available')]);
            }
        } else {
            return response()->json(['status' => false, 'message' => translate('seller_not_found')]);
        }


        // $plans = Plan::find();
        // if($plans){
        //     foreach($plans as $key=>$value){
        //         // $levels = [];
        //         $levels = PlanLevel::where('plan_id',$value['id'])->get();
        //         if($levels){
        //             foreach($levels as $k=>$val){
        //                 $levels[$k]['amount'] = number_format($val['amount'],2);
        //             }
        //         }
        //         $plans[$key]['levels'] = $levels;
        //     }

        //     return response()->json([
        //                 'status'=> true,
        //                 'data'=> $plans
        //             ],200);

        // } else {
        //     return response()->json(['message' => translate('No_plans_available')], 422);
        // }

    }

    public function testNotification(Request $request)
    {
        $friendData = [
            'user_id' => 1,
            'friend_id' => 1,
        ];

        $token = $request->token; 
        $title = 'Calling';
        $body = 'Sourabh is Calling you';
      
        $data = []; //$friendData;
        $sendnotification  = Helpers::sendNotification($token, $title, $body, $data);

        
          
            return response()->json([
                'data' => $sendnotification
            ], 200);
        
    }
    public function testshipmozo(Request $request)
    {
        $data = Helpers::send_to_shipmozo($request->order_id,10);

        
          
            return response()->json([
                'data' => $data
            ], 200);
        
    }
}
