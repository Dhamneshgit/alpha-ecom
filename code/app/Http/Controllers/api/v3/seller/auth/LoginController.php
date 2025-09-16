<?php

namespace App\Http\Controllers\api\v3\seller\auth;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Seller;
use App\Model\SellerWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $data = [
            // 'email' => $request->email,
            'phone' => $request->email,
            'password' => $request->password
        ];

        // $seller = Seller::where(['email' => $request['email']])->first();
        $seller = Seller::where(['phone' => $request['email']])->first();
        if (isset($seller) && $seller['status'] == 'approved' && auth('seller')->attempt($data)) {
            $token = Str::random(50);
            $cm_firebase_token = $request->device_token ?? null;
            Seller::where(['id' => auth('seller')->id()])->update(['auth_token' => $token,'cm_firebase_token' => $cm_firebase_token]);
            if (SellerWallet::where('seller_id', $seller['id'])->first() == false) {
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
            }
            return response()->json(['token' => $token], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => translate('Invalid credential or account no verified yet')]);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:10|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = Seller::where(['phone' => $request->phone])->first();

        if (isset($user) == false) {
            return response()->json([
                'message' => translate('Seller not found'),
            ], 200);
        }
        if (isset($user) && $user->status != 'approved') {
            return response()->json([
                'message' => translate('Account is not Approved'),
            ], 200);
        }

        $token = rand(1000, 9999);

        $user->otp = $token;
        $user->save();

        return response()->json([
            'message' => 'Send OTP Successfully',
            'otp' => $token,
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $verify = Seller::where(['phone' => $request['phone'], 'otp' => $request['otp']])->first();

        if (isset($verify)) {
            $seller = Seller::where(['phone' => $request['phone']])->first();
            $token = Str::random(50);
            $cm_firebase_token = $request->device_token ?? null;
            Seller::where(['id' => $seller->id])->update(['auth_token' => $token,'cm_firebase_token' => $cm_firebase_token]);

            return response()->json([
                'message' => translate('OTP_verified'),
                'token' => $token,
            ], 200);
        }else{
            
            $message = translate(' OTP_not_found');
            
        }

        return response()->json(['errors' => [
            ['message' => $message]
        ]], 403);
    }
}
