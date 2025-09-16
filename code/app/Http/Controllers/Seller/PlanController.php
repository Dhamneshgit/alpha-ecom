<?php

namespace App\Http\Controllers\seller;

use App\CPU\BackEndHelper;
use App\CPU\Convert;
use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Coupon;
use App\Model\Plan;
use App\Model\PlanTransaction;
use App\Model\ReferralTransaction;
use App\Model\RepurchaseTransaction;
use App\Model\PlanLevel;
use App\Model\Seller;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use function App\CPU\translate;

class PlanController extends Controller
{
    public function plan_list(Request $request)
    {

        $seller_type = auth('seller')->user()->type;

        $query_param = [];
        $search = $request['search'];

        $cou = Plan::when(isset($request['search']) && !empty($request['search']), function ($query) use ($search) {
            $key = explode(' ', $search);
            foreach ($key as $value) {
                $query->where('title', 'like', "%{$value}%")
                    ->orWhere('amount', 'like', "%{$value}%");
            }
        })
            ->when($seller_type != 'both', function ($query) use ($seller_type) {
                $query->where('seller_type', $seller_type); 
            })
            ->where('status', 1)
            ->latest()
            ->paginate(Helpers::pagination_limit())
            ->appends($query_param);



        return view('seller-views.plan.plan-list', compact('cou', 'search'));
    }


    public function purchase_plan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'user_id' => 'required',
            'plan_id' => 'required',
            // 'transaction_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userdata = Seller::find(auth('seller')->user()->id);

        $plan_data = Plan::find($request->plan_id);

        $current_date = Carbon::now(); //->format('Y-m-d');
        $new_date = $current_date->addDays($plan_data->days);
        
        $insert = [
            'seller_id' => auth('seller')->user()->id,
            'plan_id' => $request->plan_id,
            'transaction_id' => $request->transaction_id ?? rand(1000000,9999999),
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

    public function plantransactions(Request $request)
    {
        $query_param = [];
        $search = $request['search'];

        $cou = PlanTransaction::with('seller','plan')
                    ->when(isset($request['search']) && !empty($request['search']), function ($query) use ($search) {
                    $key = explode(' ', $search);
                    foreach ($key as $value) {
                        $query->where(function($q) use ($value) {
                            $q->where('plan_transactions.amount', 'like', "%{$value}%");
                            // ->orWhere('plans.title', 'like', "%{$value}%");
                        });
                    }
                })
                ->where('seller_id',auth('seller')->user()->id)
                ->latest()
                ->paginate(Helpers::pagination_limit())
                ->appends($query_param);

        return view('seller-views.plan.plan_transaction', compact('cou', 'search'));
    }


    // to track plan status
    // public function track_plan_status(Request $request)
    // {
    //     $userdata = Seller::find(auth('seller')->user()->id);
    //     dd($userdata);

    // }

    // public function track_plan_status(Request $request)
    // {
    //     $userdata = Seller::find(auth('seller')->user()->id);
    
    //     if ($userdata && $userdata->plan_status == 1 && Carbon::parse($userdata->end_date)->isFuture()) {
    //         return response()->json([
    //             'status' => 'active',
    //             'message' => 'The plan is active and valid.',
    //         ]);
    //     } else {
    //         return response()->json([
    //             'status' => 'inactive',
    //             'message' => 'The plan is either inactive or expired.',
    //         ]);
    //     }
    // }
    

public function track_plan_status(Request $request)
{
    $userdata = Seller::find(auth('seller')->user()->id);

    if ($userdata && $userdata->plan_status == 1) {
        if (Carbon::parse($userdata->end_date)->isPast()) {
            $userdata->plan_status = 0;
            $userdata->save(); 

            return response()->json([
                'status' => 'expired',
                'message' => 'The plan has expired and the status has been updated.',
            ]);
        } else {
            return response()->json([
                'status' => 'active',
                'message' => 'The plan is active and valid.',
            ]);
        }
    }

    return response()->json([
        'status' => 'inactive',
        'message' => 'The plan is inactive, please purchase a new plan',
    ]);
}



    
}
