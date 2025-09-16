<?php

namespace App\Http\Controllers\api\v1\auth;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;
use App\Model\BusinessSetting;
use App\Model\Plan;
use App\Model\PlanTransaction;
use App\Model\Seller;
use App\Model\Order;
use App\Model\Admin;
use App\Model\Team;
use App\Model\WalletTransaction;
use Carbon\Carbon;
use App\Model\Zipcode;
use App\Model\City;
use DB;
use App\Model\PlanLevel;
use App\CPU\ImageManager;
use Illuminate\Support\Facades\View;
class SocialAuthController extends Controller
{
    public function social_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'unique_id' => 'required',
            'email' => 'required',
            'medium' => 'required|in:google,facebook,apple',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $client = new Client();
        $token = $request['token'];
        $email = $request['email'];
        $unique_id = $request['unique_id'];

        try {
            if ($request['medium'] == 'google') {
                $res = $client->request('GET', 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $token);
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'facebook') {
                $res = $client->request('GET', 'https://graph.facebook.com/' . $unique_id . '?access_token=' . $token . '&&fields=name,email');
                $data = json_decode($res->getBody()->getContents(), true);
            } elseif ($request['medium'] == 'apple') {
//                $res = $client->request('GET', 'https://graph.facebook.com/' . $unique_id . '?access_token=' . $token . '&&fields=name,email');
//                $data = json_decode($res->getBody()->getContents(), true);
                $socialLogin = BusinessSetting::where('type', 'social_login')->first();
                $client_id = '';
                $client_secret = '';
                foreach(json_decode($socialLogin['value'], true) as $key => $social){
                    if($social['login_medium'] == 'apple'){
                        $client_id = $social['service_id'];
                        $client_secret = $social['client_secret'];
                    }
                }
                $apple_data = [
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => 'www.test.com',
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'code' => $request['token']
                ];
                $response = Request::create('/oauth/token', 'POST', $apple_data);
                $data = json_decode($response->getBody()->getContent(), true);
                dd($data);
            }
        } catch (\Exception $exception) {
            return response()->json(['error' => 'wrong credential.']);
        }

        if (strcmp($email, $data['email']) === 0) {
            $name = explode(' ', $data['name']);
            if (count($name) > 1) {
                $fast_name = implode(" ", array_slice($name, 0, -1));
                $last_name = end($name);
            } else {
                $fast_name = implode(" ", $name);
                $last_name = '';
            }
            $user = User::where('email', $email)->first();
            if (isset($user) == false) {
                $user = User::create([
                    'f_name' => $fast_name,
                    'l_name' => $last_name,
                    'email' => $email,
                    'phone' => '',
                    'password' => bcrypt($data['id']),
                    'is_active' => 1,
                    'login_medium' => $request['medium'],
                    'social_id' => $data['id'],
                    'is_phone_verified' => 0,
                    'is_email_verified' => 1,
                    'temporary_token' => Str::random(40)
                ]);
            } else {
                $user->temporary_token = Str::random(40);
                $user->save();
            }
            if(!isset($user->phone))
            {
                return response()->json([
                    'token_type' => 'update phone number',
                    'temporary_token' => $user->temporary_token ]);
            }

            $token = self::login_process_passport($user, $user->email, $data['id']);
            if ($token != null) {
                return response()->json(['token' => $token]);
            }
            return response()->json(['error_message' => translate('Customer_not_found_or_Account_has_been_suspended')]);
        }

        return response()->json(['error' => translate('email_does_not_match')]);
    }

    public static function login_process_passport($user, $email, $password)
    {
        $data = [
            'email' => $email,
            'password' => $password
        ];

        if (isset($user) && $user->is_active && auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
        } else {
            $token = null;
        }

        return $token;
    }
    public function update_phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'temporary_token' => 'required',
            'phone' => 'required|min:11|max:14'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = User::where(['temporary_token' => $request->temporary_token])->first();
        $user->phone = $request->phone;
        $user->save();


        $phone_verification = BusinessSetting::where('type', 'phone_verification')->first();

        if($phone_verification->value == 1)
        {
            return response()->json([
                'token_type' => 'phone verification on',
                'temporary_token' => $request->temporary_token
            ]);

        }else{
            return response()->json(['message' =>'Phone number updated successfully']);
        }
    }

    public function purchase_plan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'plan_id' => 'required',
            'transaction_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userdata = Seller::find($request->user_id);

        $plan_data = Plan::find($request->plan_id);

        $current_date = Carbon::now(); //->format('Y-m-d');
        $new_date = $current_date->addDays($plan_data->days);
        
        $insert = [
            // 'user_id' => $request->user_id,
            'seller_id' => $request->user_id,
            'plan_id' => $request->plan_id,
            'transaction_id' => $request->transaction_id,
            'amount' => $request->amount,
            'remark' => $request->remark ?? '',
            'status' => 'Success',
            'expire_date' => $new_date->format('Y-m-d'),
            'created_at' => Carbon::now()
            
        ];

        // $userdata->fund_wallet = $userdata->fund_wallet - $request->amount;
        // $userdata->save();

        $check = PlanTransaction::insert($insert);
        
        if($check){

            // Team::where('parent_id',$request->user_id)->update(['daily_bonus_count' => 0,'status' => 0]); // for daily bonus 

            $userdata->plan_id = $request->plan_id;
            $userdata->plan_status = 1;
            $userdata->start_date = Carbon::now()->format('Y-m-d');
            $userdata->end_date = $new_date->format('Y-m-d');
            $userdata->save();
            
            // $plan_expire_date = ($userdata->end_date) ? Carbon::parse($userdata->end_date) : null;
            // if($plan_expire_date && $plan_expire_date->greaterThan(Carbon::now())){
            //     $userdata->plan_id = $request->plan_id;
            //     $userdata->plan_status = 1;
            //     $userdata->plan_expire_date = $new_date->format('Y-m-d');
            //     $userdata->save();
            // } else {
            //     $userdata->plan_id = $request->plan_id;
            //     $userdata->plan_status = 1;
            //     $userdata->plan_expire_date = $new_date->format('Y-m-d');
            //     $userdata->wallet_balance =  ($userdata->wallet_balance) ? $userdata->wallet_balance + $request->amount : $userdata->wallet_balance;
            //     $userdata->save();
    
            //     $shopData = Seller::where('zipcode',$userdata->zipcode)->first();
            //     $frenchise = Admin::where('zipcode',$userdata->zipcode)->where('admin_role_id', 2)->first();
            //     $zipcode = Zipcode::where('zipcode',$userdata->zipcode)->first();
            //     $city = City::find($zipcode->city_id);
            //     $admin = Admin::where('admin_role_id', 1)->first();
    
            //     $district = Admin::where('city_id', $userdata->city_id)->first();
            //     if($district){
            //         $district->user_bonus = $district->user_bonus + $plan_data->district_bonus;
            //         $district->save();
            //         Helpers::repurchaseTransactions($district->id, $request->user_id, $plan_data->district_bonus, 'district', $userdata->zipcode, 'plan_purchase_bonus','Credit');
            //     }
    
            //     $state = Admin::where('state', $city->state)->first();
            //     if($state){
            //         $state->user_bonus = $state->user_bonus + $plan_data->state_bonus;
            //         $state->save();
    
            //         Helpers::repurchaseTransactions($state->id, $request->user_id, $plan_data->state_bonus, 'state', $userdata->zipcode, 'plan_purchase_bonus','Credit');
            //     }
        
            //     if($frenchise){
    
            //         $frenchise->user_bonus = $frenchise->user_bonus + $plan_data->frenchise_bonus;
            //         $frenchise->save();
    
            //         Helpers::repurchaseTransactions($frenchise->id, $request->user_id, $plan_data->frenchise_bonus, 'frenchise', $userdata->zipcode, 'plan_purchase_bonus','Credit');
            //     }
    
            //     if($shopData){
    
            //         $shopData->earning_wallet = $shopData->earning_wallet + $plan_data->shop_bonus ;
            //         $shopData->save();
    
            //         Helpers::repurchaseTransactions($shopData->id, $request->user_id, $plan_data->shop_bonus, 'shop', $userdata->zipcode, 'plan_purchase_bonus','Credit');
            //     }
        
    
            //     // $is_frenchise = $userdata->is_frenchise;
            //     $is_frenchise = 0;
            //     $userId = $userdata->id;
            //     $this->get_bonus_referral($is_frenchise, $userdata->id, $userId);
            
             // }

            
            // if($request->type == '1'){  //1 for signup 0 for after signup 
            //     $phone_verification = Helpers::get_business_settings('signup_bonus');
            //     $userdata->wallet_balance = ($userdata->wallet_balance) ? $userdata->wallet_balance + $phone_verification : $phone_verification;
            //     $userdata->save();

            //     $transaction = [
            //         'user_id' => $request->user_id,
            //         'transaction_id' => $request->transaction_id,
            //         'credit' => $phone_verification,
            //         'transaction_type'=> 'Sign_up_bonus',
            //         'type' => 'credit'
            //     ];
            //     DB::table('wallet_transactions')->insert($transaction);
            // }

                // $transaction1 = [
                //     'seller_id' => $request->user_id,
                //     'transaction_id' => $request->transaction_id,
                //     'credit' => $request->amount,
                //     'transaction_type'=> 'subscrption_purchase_bonus',
                //     'type' => 'credit'
                // ];
                // DB::table('wallet_transactions')->insert($transaction1);
            
            return response()->json([
                'status'=> true,
                'message'=>'Subscrption purchase Successfully'
            ]);
        } else {
            return response()->json([
                'status'=> false,
                'message'=>'Subscrption purchase unSuccessfull'
            ]);
        }

    }
    public function gettest()
    {
        $is_frenchise = 0;
        $userId = 248;
        $this->get_bonus_referral($is_frenchise, $userId, $userId);
        
            
        return response()->json([
            'status'=> true,
            'message'=>'Plan purchase Successfully'
        ]);
      

    }

    public function get_referral_history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = DB::table('referral_transactions')->where('parent_id',$request['user_id'])->where('type',$request['type'])->orderBy('id','DESC')->get();
        if($data){
            foreach($data as $key=>$value){
                if($value->referral_id){
                    $userdata = User::find($value->referral_id);
                    $data[$key]->referral_name = @$userdata->f_name.' '.@$userdata->l_name;
                }else {
                    $data[$key]->referral_name = null;
                }

            }
            return response()->json([
                'status'=> true,
                'message'=>'Successfully',
                'data'=>$data
            ]);
        } else {
            return response()->json([
                'status'=> false,
                'message'=>'unSuccessfull',
                'data'=>$data
            ]);
        }

    }
    public function sendMoney(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'amount' => 'required',
            'user_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $senderData = User::find($request['user_id']);
        $userData = User::where('phone',$request['user_number'])->first();
        if(empty($userData)){
            return response()->json([
                'status'=> false,
                'message'=>'user not found',
            ]);
        }

        $senderData->fund_wallet = $senderData->fund_wallet - $request['amount'];
        $senderData->save();

        $transaction1 = [
            'user_id' => $request['user_id'],
            'transaction_id' => 'send_money',
            'debit' => $request['amount'],
            'transaction_type'=> 'fund_money',
            'type' => 'debit',
            'credit_user_id' => $userData->id ,
            'credit_type' => 'user',
        ];
        DB::table('wallet_transactions')->insert($transaction1);

        $userData->fund_wallet = $userData->fund_wallet + $request['amount'];
        $userData->save();

        $transaction1 = [
            'user_id' => $userData->id,
            'transaction_id' => 'send_money',
            'credit' => $request['amount'],
            'transaction_type'=> 'fund_money',
            'type' => 'credit',
            'credit_user_id' => $request['user_id'],
            'credit_type' => 'user',
        ];
        DB::table('wallet_transactions')->insert($transaction1);

        return response()->json([
            'status'=> true,
            'message'=>'Money send Successfully',
        ]);

    }
    public function getUserByReferral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $code = $request->code;
        $user = User::where('phone',$code)->first();
       
        if($user){
            $data['name'] = $user->f_name.' '.$user->l_name;
            $data['phone'] = $user->phone;
            $data['email'] = $user->email;
            $data['referral_code'] = $user->referral_code;
            return response()->json([
                'status'=> true,
                'message'=>'Successfully',
                'data'=>$data
            ]);
            
        } else {
            return response()->json([
                'status'=> false,
                'message'=>'User not Found',
            ]);
        }

    }
    public function myTeam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            // 'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user_id = $request->user_id;
        $user = User::find($user_id);
        $referr = [];
        if($user->friend_referral){
            $refer_data = User::where('referral_code',$user->friend_referral)->first();
            if($refer_data){
                $referr['name'] = $refer_data->f_name.' '.$refer_data->l_name; 
                $referr['number'] = $refer_data->phone; 
                $referr['email'] = $refer_data->email; 
            }
        }
        if($user && $user->plan_id){
            $totalCount = Team::where('parent_id',$user_id)->get();
            $check = Plan::find($user->plan_id);
            if($check){
                $data = [];
                for($i = 1; $i <= $check->level; $i++){
                    $team = Helpers::teamCount($user_id, $i);
                    $data['level'.$i] = $team;
                }
                return response()->json([
                    'status'=> true,
                    'message'=>'Successfully',
                    'referr' => $referr,
                    'level_count'=>$check->level,
                    'total_count'=>count($totalCount),
                    'data'=>$data
                ]);

            } else{
                return response()->json([
                    'status'=> false,
                    'message'=>'Plan not Found',
                ]);
            }
        } else {
            return response()->json([
                'status'=> false,
                'message'=>'User not Found',
            ]);
        }

    }
    public function fundRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'amount' => 'required',
            'transaction_id' => 'required',
            // 'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user_id = $request->user_id;
        $user = User::find($user_id);
        if($user){

            $data =[
                'user_id' => $user_id,
                'amount' => $request['amount'],
                'transaction_id' => $request['transaction_id'],
                'image' => ImageManager::upload('users/', 'png', $request->file('image')),
            ];

            $check = DB::table('fund_payments')->insert($data);
            if($check){
                return response()->json([
                    'status'=> true,
                    'message'=>'Success, waiting for Approval',
                ]);
            }else {
                return response()->json([
                    'status'=> false,
                    'message'=>'Something went wrong',
                ]);
            }

        } else {
            return response()->json([
                'status'=> false,
                'message'=>'User not Found',
            ]);
        }

    }
    public function getFundRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user_id = $request->user_id;
        $user = User::find($user_id);
        if($user){
            $check = DB::table('fund_payments')->where('user_id',$user_id)->orderBy('id','DESC')->get();
            if($check){
                return response()->json([
                    'status'=> true,
                    'message'=>'Success',
                    'data' => $check
                ]);
            }else {
                return response()->json([
                    'status'=> false,
                    'message'=>'no data found',
                ]);
            }

        } else {
            return response()->json([
                'status'=> false,
                'message'=>'User not Found',
            ]);
        }

    }
    public function get_repurchase_history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = DB::table('repurchase_transactions')->where('parent_id',$request['user_id'])->where('type',$request['type'])->orderBy('id','DESC')->get();
        if($data){
            foreach($data as $key=>$value){
                if($value->referral_id){
                    $userdata = User::find($value->referral_id);
                    $data[$key]->referral_name = $userdata->f_name.' '.$userdata->l_name;
                } else {
                    $data[$key]->referral_name = '';
                }

            }
            return response()->json([
                'status'=> true,
                'message'=>'Successfully',
                'data'=>$data
            ]);
        } else {
            return response()->json([
                'status'=> false,
                'message'=>'unSuccessfull',
                'data'=>$data
            ]);
        }

    }
    // public function get_user_wallet(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::error_processor($validator)], 403);
    //     }

    //     $data = DB::table('referral_transactions')->where('parent_id',$request['user_id'])->where('type',$request['type'])->orderBy('id','DESC')->get();
    //     if($data){
    //         foreach($data as $key=>$value){
    //             $userdata = User::find($value->referral_id);
    //             $data[$key]->referral_name = $userdata->f_name.' '.$userdata->l_name;

    //         }
    //         return response()->json([
    //             'status'=> true,
    //             'message'=>'Successfully',
    //             'data'=>$data
    //         ]);
    //     } else {
    //         return response()->json([
    //             'status'=> false,
    //             'message'=>'unSuccessfull',
    //             'data'=>$data
    //         ]);
    //     }

    // }
    public function get_zipcode()
    {
        
        $data = DB::table('zipcode')->get();
        if($data){
            
            return response()->json([
                'status'=> true,
                'message'=>'Successfully',
                'data'=>$data
            ]);
        } else {
            return response()->json([
                'status'=> false,
                'message'=>'unSuccessfull',
                'data'=>$data
            ]);
        }

    }
    public function withdrawal_request(Request $request)
    {
        $user = User::find($request->user_id);
        $amount = $request->amount;
        $wallet_type = $request->wallet_type; //referral, daily, withdrawal

        $tds = DB::table('business_settings')->where('type','tds_customer')->first(); 
        $admin_commission = DB::table('business_settings')->where('type','admin_commission_customer')->first(); 
        $repurchase_income = DB::table('business_settings')->where('type','repurchase_income')->first();
        
        $tds = sprintf("%.2f",(($tds->value/100) * $amount));
        $admin_commission = sprintf("%.2f",(($admin_commission->value/100) * $amount));
        $repurchase_income = sprintf("%.2f",(($repurchase_income->value/100) * $amount));

        $insert = [
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'wallet_type' => $request->wallet_type,
            'tds' => $tds ?? 0,
            'admin_commission' => $admin_commission ?? 0,
            'repurchase_income' => $repurchase_income ?? 0,
            'remaning_amount' => $request->amount - ($tds + $admin_commission + $repurchase_income),
            'holder_name' => $request->holder_name ?? null,
            'bank_name' => $request->bank_name ?? null,
            'branch' => $request->branch ?? null,
            'ifsc' => $request->ifsc ?? null,
            'account_number' => $request->account_number ?? null,
        ];
        
        $data = DB::table('withdrawal_request_user')->insert($insert);

        if($wallet_type == "referral"){
            $user->referral_bonus = $user->referral_bonus - $amount;
        } else if($wallet_type == "daily"){
            $user->daily_bonus_amount = $user->daily_bonus_amount - $amount;
        } else {
            $user->withdrawal_wallet = $user->withdrawal_wallet - $amount;
        }
        $user->repurchase_wallet = $user->repurchase_wallet + $repurchase_income ?? 0;
        $user->save();

        $admin = Admin::find(1);
        $admin->user_bonus = $admin->user_bonus + $tds + $admin_commission;
        $admin->save();

        $repurchase_bonus_add = Helpers::repurchaseTransactions(1, $request->user_id,  $tds, 'admin', null, 'withdrwal_tds_bonus','Credit');
        $repurchase_bonus_add1 = Helpers::repurchaseTransactions(1, $request->user_id,  $admin_commission, 'admin', null, 'withdrwal_commission_bonus','Credit');

        if($data){
            
            return response()->json([
                'status'=> true,
                'message'=>'Successfully',
                'data'=>$data
            ]);
        } else {
            return response()->json([
                'status'=> false,
                'message'=>'unSuccessfull',
                'data'=>$data
            ]);
        }

    }
    public function get_withdrawal_request(Request $request)
    {
        $user = User::find($request->user_id);
       
        
        $data = DB::table('withdrawal_request_user')->where('user_id', $request->user_id)->orderBy('id','DESC')->get();

        if($data){
            
            return response()->json([
                'status'=> true,
                'message'=>'Successfully',
                'data'=>$data
            ]);
        } else {
            return response()->json([
                'status'=> false,
                'message'=>'unSuccessfull',
                'data'=>$data
            ]);
        }

    }
    public function daily_bonus()
    {

        $defaultArray = [
            '1' => 1,
            '2' => 3,
            '3' => 3,
            '4' => 5,
            '5' => 5,
            '6' => 7,
            '7' => 7,
            '8' => 9,
            '9' => 9,
            '10' => 11,
            '11' => 20,
            '12' => 20,
            '13' => 20,
            '14' => 20,
            '15' => 20,
            '16' => 20,
            '17' => 20,
            '18' => 20,
            '19' => 20,
            '20' => 20,
        ];
        $current = date('Y-m-d');
        $allUserIds = User::whereNotNull('plan_expire_date')
                        ->whereRaw('friend_referral IS NOT NULL AND friend_referral != ""')
                        ->where('plan_expire_date', '>=', $current)
                        ->where('is_active', 1)
                        ->get();

        if(!empty($allUserIds)){
            foreach($allUserIds as $key=>$user){
                $planData = Plan::find($user['plan_id']);

                $user_id = $user['id'];
                $limit = $planData->daily_bonus_till_days ?? 0;
                $limitAmount = $planData->daily_bonus_limit ?? 0;
                $planId = $user['plan_id'];
                $amount = 0;

                $team = Helpers::teamCount($user_id, 1);

                // if($team['user_count'] && $team['user_count'] != 1){
                if($team['user_count']){
                    $incomeLevel = $defaultArray[$team['user_count']]; 
                    for($i=1; $i<=$incomeLevel; $i++){
                        $team = Team::where('parent_id',$user_id)
                                    ->where('level',$i)
                                    ->where('daily_bonus_count', '<=', $limit)
                                    ->get();
                        $amount += $this->planDataDailyBonus($planId, $i, $user_id, count($team));
                        foreach ($team as $teams) {
                            $dailyBonus = $teams->daily_bonus_count + 1;
                            $teams->daily_bonus_count = $dailyBonus;
                            $teams->status = ($dailyBonus <= $limit) ? 0 : 1;
                            $teams->save();
                        }            
                    }
                    $user->daily_bonus_amount += ($amount <= $limitAmount) ? $amount : $limitAmount;
                    $user->save();
        
                    $bonus_add = $this->referralTransaction($user_id, null, ($amount <= $limitAmount) ? $amount : $limitAmount , null , 'daily_bonus');
                }
            }
        }                
        return true;

    }

    public function planDataDailyBonus($planId, $level, $parent_id, $count)
    {
        $amount = 0;
        $planlevel = PlanLevel::where('plan_id', $planId)->where('level_id',$level)->first();
        if($planlevel){
            $amount  = $planlevel->daily_bonus * $count; 
        } 
        return $amount;
    }

    public function get_bonus_referral($is_frenchise, $user_id, $id, $level = 1){ // id is Actual user id  used to in some function because user_id is change on the basis of refferal code 

        $code = $this->get_friends_code($user_id);
        if($code['status']){
            $check = $this->checkPlan($code['code'], $level, $user_id, $is_frenchise, $id);
            $user = User::where('referral_code', $code['code'])->first();

            if($user){
                $this->get_bonus_referral($is_frenchise, $user->id, $id, $level + 1);
            }
        }
        return $code;
    }

    private function get_friends_code($user_id)
    {
        $user = User::find($user_id);
        $response['code'] = '';
        $response['status'] = false;
        if ($user) {
            $response['code'] = $user['friend_referral'];
            $response['status'] = !empty($user['friend_referral']) ? true : false;
        }
        return $response;
    }

    private function checkPlan($referral_code, $level, $user_id, $is_frenchise, $id)
    {
        $today = Carbon::now()->format('Y-m-d');
        $user = User::where('referral_code', $referral_code)->first();
        if ($user) {
            if(($user->plan_status == 1) && ($user->plan_expire_date > $today) && !empty($user->plan_id)){
              $plan = $this->planData($user->plan_id, $level, $user_id, $user->id, $is_frenchise, $id);
              return true; 
            }
        }
        return false;
    }

    public function planData($planId, $level, $user_id, $parent_id, $is_frenchise, $id)
    {
        $plan = Plan::find($planId);

        if($plan){
            $planlevel = PlanLevel::where('plan_id', $planId)->where('level_id',$level)->first();
            if($planlevel){
                $refferal_add = $this->referralTransaction($parent_id, $id, $planlevel->amount, $level, 'refer_bonus');

                $userData = User::find($parent_id);
                $userData->referral_bonus = $userData->referral_bonus + $planlevel->amount;
                $userData->save();

                Helpers::teamCreated($id, $parent_id, $level);

                if($is_frenchise == '1'){
                    $userData->referral_bonus = $userData->referral_bonus + $planlevel->frenchise_income;
                    $userData->save();

                    $refferal_add1 = $this->referralTransaction($parent_id, $id, $planlevel->frenchise_income, $level, 'frenchise_refer_bonus');
                }
                return true;  
            } 
        }
        return false;
    }

    public function referralTransaction($parent_id, $referrel_id, $amount, $level, $type=''){
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


    public function get_daily_bonus_referral($parent_amount, $user_id, $level = 1){

        $code = $this->get_friends_code($user_id);

        if($code['status']){
            $check = $this->checkPlan1($code['code'], $level, $user_id, $parent_amount);
            $user = User::where('referral_code', $code['code'])->first();

            $this->get_daily_bonus_referral($parent_amount, $user->id, $level + 1);
        }
        return $code;
    }

    private function checkPlan1($referral_code, $level, $user_id, $is_frenchise)
    {
        $today = Carbon::now()->format('Y-m-d');
        $user = User::where('referral_code', $referral_code)->first();
        if ($user) {
            if(($user->plan_status == 1) && ($user->plan_expire_date > $today) && !empty($user->plan_id)){
              $plan = $this->planData1($user->plan_id, $level, $user_id, $user->id, $is_frenchise);
              return true; 
            }
        }
        return false;
    }

    public function planData1($planId, $level, $user_id, $parent_id, $is_frenchise)
    {
        $plan = Plan::find($planId);

        if($plan){
            $planlevel = PlanLevel::where('plan_id', $planId)->where('level_id',$level)->first();
            if($planlevel){
                $refferal_add = $this->referralTransaction($parent_id, $user_id, $planlevel->amount, $level, 'refer_bonus');

                $userData = User::find($parent_id);
                $userData->referral_bonus = $userData->referral_bonus + $planlevel->amount;
                $userData->save();

                if($is_frenchise == '1'){
                    $userData->referral_bonus = $userData->referral_bonus + $planlevel->frenchise_income;
                    $userData->save();

                    $refferal_add1 = $this->referralTransaction($parent_id, $user_id, $planlevel->frenchise_income, $level, 'frenchise_refer_bonus');
                }
                return true;  
            } 
        }
        return false;
    }


    public function generateInvoice($id)
    {
        $company_phone = BusinessSetting::where('type', 'company_phone')->first()->value;
        $company_email = BusinessSetting::where('type', 'company_email')->first()->value;
        $company_name = BusinessSetting::where('type', 'company_name')->first()->value;
        $company_web_logo = BusinessSetting::where('type', 'company_web_logo')->first()->value;

        $order = Order::with('seller')->with('shipping')->with('details')->where('id', $id)->first();
        $seller = Seller::find($order->details->first()->seller_id);
        $data["email"] = $order->customer != null ? $order->customer["email"] : \App\CPU\translate('email_not_found');
        $data["client_name"] = $order->customer != null ? $order->customer["f_name"] . ' ' . $order->customer["l_name"] : \App\CPU\translate('customer_not_found');
        $data["order"] = $order;
        $mpdf_view = View::make(
            'admin-views.order.invoice',
            compact('order', 'seller', 'company_phone', 'company_name', 'company_email', 'company_web_logo')
        );
        Helpers::gen_mpdf($mpdf_view, 'order_invoice_', $order->id);
    }

}
