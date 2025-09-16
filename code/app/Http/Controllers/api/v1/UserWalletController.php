<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WalletTransaction;
use App\CPU\Helpers;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class UserWalletController extends Controller
{
    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $wallet_status = Helpers::get_business_settings('wallet_status');

        if($wallet_status == 1)
        {
            $user = $request->user();
            $total_wallet_balance = $user->wallet_balance;
            $total_referral_bonus = $user->referral_bonus;
            $total_daily_bonus = $user->daily_bonus_amount;
            $total_fund_wallet = $user->fund_wallet;
            $wallet_transactio_list = WalletTransaction::where('user_id',$user->id)
                                                    ->latest()
                                                    ->paginate($request['limit'], ['*'], 'page', $request['offset']);
        
            return response()->json([
                'limit'=>(integer)$request->limit,
                'offset'=>(integer)$request->offset,
                'total_wallet_balance'=>$total_wallet_balance,
                'total_referral_bonus'=>$total_referral_bonus,
                'total_daily_bonus'=>$total_daily_bonus,
                'total_fund_wallet'=>$total_fund_wallet,
                'total_withdrawal_wallet' => $user->withdrawal_wallet,
                'total_repurchase_wallet' => $user->repurchase_wallet,
                'total_wallet_transactio'=>$wallet_transactio_list->total(),
                'wallet_transactio_list'=>$wallet_transactio_list->items()
            ],200);
            
        }else{
            
            return response()->json(['message' => translate('access_denied!')], 422);
        }
    }
}
