<?php

namespace App\Http\Controllers\Admin;

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
use Carbon\Carbon;
use function App\CPU\translate;

class PlanController extends Controller
{
    public function add_new(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        $cou = Plan::when(isset($request['search']) && !empty($request['search']), function ($query) use ($search) {
                    $key = explode(' ', $search);
                    foreach ($key as $value) {
                        $query->where('title', 'like', "%{$value}%")
                            ->orWhere('amount', 'like', "%{$value}%");
                    }
            })->latest()->paginate(Helpers::pagination_limit())->appends($query_param);

        

        return view('admin-views.plan.add-new', compact('cou', 'search'));
    }
    public function plantransactions(Request $request)
    {
        $query_param = [];
        $search = $request['search'];

        $cou = PlanTransaction::when(isset($request['search']) && !empty($request['search']), function ($query) use ($search) {
                $key = explode(' ', $search);
                foreach ($key as $value) {
                    $query->where(function($q) use ($value) {
                        $q->where('plan_transactions.amount', 'like', "%{$value}%")
                        ->orWhere('users.f_name', 'like', "%{$value}%")
                        ->orWhere('users.l_name', 'like', "%{$value}%")
                        ->orWhere('plans.title', 'like', "%{$value}%");
                    });
                }
            })
            ->leftJoin('sellers', 'sellers.id', '=', 'plan_transactions.seller_id')
            ->leftJoin('plans', 'plans.id', '=', 'plan_transactions.plan_id')
            ->select('plan_transactions.*', 'sellers.f_name', 'sellers.l_name','plans.days', 'plans.title as plan_title')
            ->latest()
            ->paginate(Helpers::pagination_limit())
            ->appends($query_param);

        return view('admin-views.plan.plan_transaction', compact('cou', 'search'));
    }
    public function userReferralTransactions(Request $request)
    {
        $query_param = [];
        $search = $request['search'];

        $cou = ReferralTransaction::when(isset($request['search']) && !empty($request['search']), function ($query) use ($search) {
                $key = explode(' ', $search);
                foreach ($key as $value) {
                    $query->where(function($q) use ($value) {
                        $q->where('referral_transactions.amount', 'like', "%{$value}%")
                        ->orWhere('u1.f_name', 'like', "%{$value}%")
                        ->orWhere('u1.l_name', 'like', "%{$value}%")
                        ->orWhere('referral_transactions.type', 'like', "%{$value}%");
                    });
                }
            })
            ->leftJoin('users as u1', 'u1.id', '=', 'referral_transactions.parent_id')
            ->leftJoin('users as u2', 'u2.id', '=', 'referral_transactions.referral_id')
            ->select('referral_transactions.*', 'u1.f_name as parent_f_name', 'u1.l_name as parent_l_name', 'u2.f_name as referral_f_name', 'u2.l_name as referral_l_name')
            ->latest()
            ->paginate(Helpers::pagination_limit())
            ->appends($query_param);

        return view('admin-views.plan.user_transaction', compact('cou', 'search'));
    }
    // public function userDailyTransactions(Request $request)
    // {
    //     $query_param = [];
    //     $search = $request['search'];

    //     $cou = ReferralTransaction::when(isset($request['search']) && !empty($request['search']), function ($query) use ($search) {
    //             $key = explode(' ', $search);
    //             foreach ($key as $value) {
    //                 $query->where(function($q) use ($value) {
    //                     $q->where('referral_transactions.amount', 'like', "%{$value}%")
    //                     ->orWhere('u1.f_name', 'like', "%{$value}%")
    //                     ->orWhere('u1.l_name', 'like', "%{$value}%");
    //                 });
    //             }
    //         })
    //         ->leftJoin('users as u1', 'u1.id', '=', 'referral_transactions.parent_id')
    //         ->leftJoin('users as u2', 'u2.id', '=', 'referral_transactions.referral_id')
    //         ->select('referral_transactions.*', 'u1.f_name as parent_f_name', 'u1.l_name as parent_l_name', 'u2.f_name as referral_f_name', 'u2.l_name as referral_l_name')
    //         ->latest()
    //         ->paginate(Helpers::pagination_limit())
    //         ->appends($query_param);

    //     return view('admin-views.plan.user_transaction', compact('cou', 'search'));
    // }
    public function repurchasetransactions(Request $request)
    {
        $query_param = [];
        $search = $request['search'];

        $cou = RepurchaseTransaction::when(isset($request['search']) && !empty($request['search']), function ($query) use ($search) {
                $key = explode(' ', $search);
                foreach ($key as $value) {
                    $valueWithUnderscores = str_replace(' ', '_', $value);
                    $query->where(function($q) use ($value, $valueWithUnderscores) {
                        $q->where('repurchase_transactions.amount', 'like', "%{$value}%")
                        ->orWhere('repurchase_transactions.type', 'like', "%{$valueWithUnderscores}%")
                        ->orWhere('u2.f_name', 'like', "%{$value}%")
                        ->orWhere('u2.l_name', 'like', "%{$value}%");
                    });
                }
            })
            // ->leftJoin('users as u1', 'u1.id', '=', 'referral_transactions.parent_id')
            ->leftJoin('users as u2', 'u2.id', '=', 'repurchase_transactions.referral_id')
            ->where('repurchase_transactions.parent_type','!=','admin')
            ->select('repurchase_transactions.*','u2.f_name as referral_f_name', 'u2.l_name as referral_l_name')
            ->latest()
            ->paginate(Helpers::pagination_limit())
            ->appends($query_param);

        return view('admin-views.plan.repurchase_transaction', compact('cou', 'search'));
    }
    public function tdsTransactions(Request $request)
    {
        $query_param = [];
        $search = $request['search'];

        $cou = RepurchaseTransaction::when(isset($request['search']) && !empty($request['search']), function ($query) use ($search) {
                $key = explode(' ', $search);
                foreach ($key as $value) {
                    $query->where(function($q) use ($value) {
                        $q->where('repurchase_transactions.amount', 'like', "%{$value}%")
                        ->orWhere('u2.f_name', 'like', "%{$value}%")
                        ->orWhere('u2.l_name', 'like', "%{$value}%");
                    });
                }
            })
            // ->leftJoin('users as u1', 'u1.id', '=', 'referral_transactions.parent_id')
            ->leftJoin('users as u2', 'u2.id', '=', 'repurchase_transactions.referral_id')
            ->where('repurchase_transactions.parent_type','=','admin')
            ->select('repurchase_transactions.*','u2.f_name as referral_f_name', 'u2.l_name as referral_l_name')
            ->latest()
            ->paginate(Helpers::pagination_limit())
            ->appends($query_param);

        return view('admin-views.plan.tds_transaction', compact('cou', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'amount' => 'required',
            // 'days' => 'required',
            // 'plan_level' => 'required',
        ]);
        if(isset($request->discount)){
            if($request->discount > $request->amount){
                Toastr::error('The plan amount must be greater than discount amount');
                return redirect()->back();
            }
        }

        $coupon = new plan();
        $coupon->title = $request->title;
        $coupon->amount = $request->amount;
        $coupon->seller_type = $request->seller_type;
        $coupon->discount_amount = isset($request->discount) ? $request->discount : null;
        $coupon->description = $request->description;
        // $coupon->level = $request->plan_level;
        $coupon->days = $request->days;
        $coupon->status = 1;
        // $coupon->frenchise_bonus = $request->frenchise_bonus;
        // $coupon->district_bonus = $request->district_bonus;
        // $coupon->state_bonus = $request->state_bonus;
        // $coupon->shop_bonus = $request->shop_bonus;
        // $coupon->repurchase_state_bonus = $request->repurchase_state_bonus;
        // $coupon->repurchase_district_bonus = $request->repurchase_district_bonus;
        // $coupon->repurchase_frenchise_bonus = $request->repurchase_frenchise_bonus;
        // $coupon->self_purchase_bonus = $request->self_purchase_bonus;
        // $coupon->daily_bonus_till_days = $request->daily_bonus_till_days ?? '';
        // $coupon->daily_bonus_limit = $request->daily_bonus_limit ?? '';
        $coupon->save();

        // if(!empty($request->plan_level)){
        //     $a = 1;
        //     for($i = 0; $i < $request->plan_level; ){
        //         $level = new PlanLevel;
        //         $level->plan_id = $coupon->id;
        //         $level->level = 'level-'.$a;
        //         $level->level_id = $a;
        //         $level->amount = $request->level[$i];
        //         $level->daily_bonus = $request->daily_bonus[$i];
        //         $level->repurchase_income = $request->repurchase_income[$i];
        //         $level->frenchise_income = $request->frenchise_income[$i];
        //         $level->frenchise_withdrawal_income = $request->frenchise_withdrawal_income[$i];
        //         $level->daily_bonus_till_days = $request->daily_bonus_till_days ?? '';
        //         $level->daily_bonus_limit = $request->daily_bonus_limit ?? '';
        //         $level->save();
        //         $a++;
        //         $i++; 
        //     }    
        // }

        Toastr::success('Plan added successfully!');
        return back();
    }
    public function storelevel(Request $request)
    {
       
        if(!empty($request->plan_level)){
            $checkDecending = PlanLevel::where('plan_id', $request->plan_id)->orderBy('id','desc')->first();
            $a = $checkDecending->level_id ?? 1;
            $ab = ++$a; 
            
            for($i = 0; $i < $request->plan_level; ){
                $level = new PlanLevel;
                $level->plan_id = $request->plan_id;
                $level->level = 'level-'.$ab;
                $level->level_id = $ab;
                $level->amount = $request->level[$i];
                $level->daily_bonus = $request->daily_bonus[$i];
                $level->repurchase_income = $request->repurchase_income[$i];
                $level->frenchise_income = $request->frenchise_income[$i];
                $level->frenchise_withdrawal_income = $request->frenchise_withdrawal_income[$i];
                $level->save();
                $ab++;
                $i++; 
            }  
            
            $plan = Plan::find($request->plan_id);
            $plan->level = $request->plan_level;
            $plan->save();

        }
       
        Toastr::success('Plan level added successfully!');
        return back();
    }

    public function edit($id)
    {
        $plan = Plan::find($id);
        // if(!empty($plan->level)){
        //     $level = PlanLevel::where('plan_id', $id)->get();
        // }
        
        return view('admin-views.plan.edit', compact('plan', ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'amount' => 'required',
            // 'days' => 'required',
            // 'plan_level' => 'required',
        ]);
        if(isset($request->discount)){
            if($request->discount > $request->amount){
                Toastr::error('The plan amount must be greater than discount amount');
                return redirect()->back();
            }
        }


        $coupon = Plan::find($id);
        $coupon->title = $request->title;
        $coupon->amount = $request->amount;
        $coupon->seller_type = $request->seller_type;
        $coupon->discount_amount = isset($request->discount) ? $request->discount : null;
        $coupon->description = $request->description;
        $coupon->days = $request->days;
        $coupon->status = 1;
        // $coupon->frenchise_bonus = $request->frenchise_bonus ?? 0;
        // $coupon->district_bonus = $request->district_bonus ?? 0;
        // $coupon->state_bonus = $request->state_bonus ?? 0;
        // $coupon->shop_bonus = $request->shop_bonus ?? 0;
        // $coupon->repurchase_state_bonus = $request->repurchase_state_bonus;
        // $coupon->repurchase_district_bonus = $request->repurchase_district_bonus;
        // $coupon->repurchase_frenchise_bonus = $request->repurchase_frenchise_bonus;
        // $coupon->self_purchase_bonus = $request->self_purchase_bonus ?? 0 ;
        // $coupon->daily_bonus_till_days = $request->daily_bonus_till_days ?? '';
        // $coupon->daily_bonus_limit = $request->daily_bonus_limit ?? '';
        $coupon->save();

        Toastr::success('Plan updated successfully!');
        return redirect()->route('admin.plan.add-new');
    }

    public function status(Request $request)
    {
        $coupon = Plan::find($request->id);
        $coupon->status = $request->status;
        $coupon->save();
        Toastr::success('Plan status updated!');
        return back();
    }
    public function userstatus(Request $request)
    {
        $coupon = DB::table('withdrawal_request_user')->where('id',$request->id)->update(['status'=>1]);
        // $coupon->status = 1;
        // $coupon->save();
        Toastr::success('Withdrawal Paid updated');
        return redirect()->back();
    }
    public function frenchisestatus(Request $request)
    {
        $coupon = DB::table('withdrawal_request_frenchise')->where('id',$request->id)->update(['status'=>1]);
        // $coupon->status = 1;
        // $coupon->save();
        Toastr::success('Withdrawal Paid updated');
        return redirect()->back();
    }

    public function my_withdrawal_list(){

        $user_id = auth('admin')->user()->id;
        $data = DB::table('withdrawal_request_frenchise')
                    ->where('user_id',$user_id)
                    ->orderBy('id','DESC')
                    ->latest()
                    ->paginate(Helpers::pagination_limit());


        return view('admin-views.plan.withdrawal_frenchise', compact('data'));
    }

    public function quick_view_details(Request $request)
    {
        $coupon = plan::find($request->id);
        if(!empty($coupon->level)){
            $level = PlanLevel::where('plan_id', $request->id)->get();
        }

        return response()->json([
            'view' => view('admin-views.plan.details-quick-view', compact('coupon','level'))->render(),
        ]);
    }

    public function delete($id)
    {
        $coupon = Coupon::where(['added_by' => 'admin'])->find($id);
        $coupon->delete();
        Toastr::success('Coupon deleted successfully!');
        return back();
    }
    public function levelupdate(Request $request)
    {
        $check = PlanLevel::find($request->level_id);
        $check->amount = $request->amount;
        $check->daily_bonus = $request->daily_bonus ?? 0;
        $check->repurchase_income = $request->repurchase_income ?? 0;
        $check->frenchise_income = $request->frenchise_income ?? 0;
        $check->frenchise_withdrawal_income = $request->frenchise_withdrawal_income ?? 0;
        $check->save();

        Toastr::success('Level Amount Update successfully!');
        return back();
    }
    public function deletelevel($id)
    {
        $check = PlanLevel::find($id);
        $planID = $check->plan_id;
        
        $checkDecending = PlanLevel::where('plan_id', $planID)->orderBy('id','desc')->first();
        
        if($checkDecending->id == $id){
            $check->delete();
            Toastr::success('Level Delete successfully!');
            return back();
        } 
      
        Toastr::error('You can not delete plans mid level');
        return back();
    
    }

    public function ajax_get_seller(Request $request)
    {
        $sellers = Seller::with('shop')->approved()->get();
        $output='<option value="" disabled selected>Select Seller</option>';
        $output.='<option value="0">All Seller</option>';
        if($request->coupon_bearer == 'inhouse') {
            $output .= '<option value="inhouse">Inhouse</option>';
        }
        foreach($sellers as $seller)
        {
            $output .= '<option value="'.$seller->id.'">'.$seller->shop->name.'</option>';
        }
        echo $output;
    }

    public function kycstatus($id,$status)
    {

        $kyc = DB::table('kyc_details')->where('id',$id)->first();
        if($kyc){
            $kyc = DB::table('kyc_details')->where('id',$id)->update(['status'=>$status]);
            Toastr::success('Status Upadte Succesfully');
            return back();
        } else {
            Toastr::error('Something went Wrong');
            return back();
        }
    }
}
