<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\FlashDeal;
use Illuminate\Http\Request;
use App\Model\FlashDealProduct;
use App\Model\Product;
use App\User;
use Carbon\Carbon;
use App\Model\Plan;
use App\Model\PlanLevel;
use DB;

class DealController extends Controller
{
    public function get_featured_deal()
    {
        $featured_deal = FlashDeal::where(['status' => 1])
            ->where(['deal_type' => 'feature_deal'])->first();

        $p_ids = array();
        if ($featured_deal) {
            $p_ids = FlashDealProduct::with(['product'])
                ->whereHas('product', function ($q) {
                    $q->active();
                })
                ->where(['flash_deal_id' => $featured_deal->id])
                ->pluck('product_id')->toArray();
        }

        return response()->json(Helpers::product_data_formatting(Product::with(['rating','tags'])->whereIn('id', $p_ids)->get(), true), 200);
    }


    public function get_bonus_referral($user_id = 19, $level = 1){
        // $user_id = $request['user_id'];

        $code = $this->get_friends_code($user_id);

        if($code['status']){
            $check = $this->checkPlan($code['code'], $level, $user_id);
            $user = User::where('referral_code', $code['code'])->first();

            $this->get_bonus_referral($user->id, $level + 1);
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

    private function checkPlan($referral_code, $level, $user_id)
    {
        $today = Carbon::now()->format('Y-m-d');
        $user = User::where('referral_code', $referral_code)->first();
        // $response['status'] = false;
        if ($user) {
            if(($user->plan_status == 1) && ($user->plan_expire_date > $today) && !empty($user->plan_id)){
              $plan = $this->planData($user->plan_id, $level, $user_id, $user->id);
              return true; 
            }
        }
        return false;
    }

    public function planData($planId, $level, $user_id, $parent_id)
    {
        $plan = Plan::find($planId);

        if($plan){
            $planlevel = PlanLevel::where('plan_id', $planId)->where('level_id',$level)->first();
            if($planlevel){
                $refferal_add = $this->referralTransaction($parent_id, $user_id, $planlevel->amount, $level, 'refer_bonus');

                $userData = User::find($parent_id);
                $userData->referral_bonus = $userData->referral_bonus + $planlevel->amount;
                $userData->save();
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

}
