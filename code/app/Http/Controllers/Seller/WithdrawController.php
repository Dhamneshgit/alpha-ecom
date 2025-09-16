<?php

namespace App\Http\Controllers\Seller;

use App\CPU\BackEndHelper;
use App\CPU\Convert;
use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\SellerWallet;
use App\Model\Seller;
use App\Model\WithdrawalMethod;
use App\Model\RepurchaseTransaction;
use App\Model\WithdrawRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
    public function w_request(Request $request)
    {
        $method = WithdrawalMethod::find($request['withdraw_method']);
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $data['method_name'] = $method->method_name;
        foreach ($fields as $field) {
            if(key_exists($field, $values)) {
                $data[$field] = $values[$field];
            }
        }

        $wallet = SellerWallet::where('seller_id', auth()->guard('seller')->user()->id)->first();
        if (($wallet->total_earning) >= Convert::usd($request['amount']) && $request['amount'] > 1) {
            DB::table('withdraw_requests')->insert([
                'seller_id' => auth()->guard('seller')->user()->id,
                'amount' => Convert::usd($request['amount']),
                'transaction_note' => null,
                'withdrawal_method_id' => $request['withdraw_method'],
                'withdrawal_method_fields' => json_encode($data),
                'approved' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $wallet->total_earning -= Convert::usd($request['amount']);
            $wallet->pending_withdraw += Convert::usd($request['amount']);
            $wallet->save();
            Toastr::success('Withdraw request has been sent.');
            return redirect()->back();
        }

        Toastr::error('invalid request.!');
        return redirect()->back();
    }
    public function bonusrequest(Request $request)
    {
        $method = WithdrawalMethod::find($request['withdraw_method']);
        $fields = array_column($method->method_fields, 'input_name');
        $values = $request->all();

        $data['method_name'] = $method->method_name;
        foreach ($fields as $field) {
            if(key_exists($field, $values)) {
                $data[$field] = $values[$field];
            }
        }

        $swallet = SellerWallet::where('seller_id', auth()->guard('seller')->user()->id)->first();
        $wallet = Seller::where('id', auth()->guard('seller')->user()->id)->first();
        if (($wallet->earning_wallet) >= Convert::usd($request['amount']) && $request['amount'] > 1) {
            DB::table('withdraw_requests')->insert([
                'seller_id' => auth()->guard('seller')->user()->id,
                'amount' => Convert::usd($request['amount']),
                'transaction_note' => null,
                'withdrawal_method_id' => $request['withdraw_method'],
                'withdrawal_method_fields' => json_encode($data),
                'approved' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $wallet->earning_wallet -= Convert::usd($request['amount']);
            $wallet->save();

            $swallet->pending_withdraw += Convert::usd($request['amount']);
            $swallet->save();
            Toastr::success('Withdraw request has been sent.');
            return redirect()->back();
        }

        Toastr::error('invalid request.!');
        return redirect()->back();
    }

    public function close_request($id)
    {
        $withdraw_request = WithdrawRequest::find($id);
        $wallet = SellerWallet::where('seller_id', auth()->guard('seller')->user()->id)->first();

        if (isset($withdraw_request) && isset($wallet) && $withdraw_request->approved == 0) {
            $wallet->total_earning += Convert::usd($withdraw_request['amount']);
            $wallet->pending_withdraw -= Convert::usd($withdraw_request['amount']);
            $wallet->save();
            $withdraw_request->delete();
            Toastr::success('Request closed!');
        } else {
            Toastr::error('Invalid request');
        }

        return back();
    }

    public function status_filter(Request $request)
    {
        session()->put('withdraw_status_filter', $request['withdraw_status_filter']);
        return response()->json(session('withdraw_status_filter'));
    }

    public function list()
    {
        $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        $withdraw_requests = WithdrawRequest::with(['seller'])
            ->where(['seller_id'=>auth('seller')->id()])
            ->when($all, function ($query) {
                return $query;
            })
            ->when($active, function ($query) {
                return $query->where('approved', 1);
            })
            ->when($denied, function ($query) {
                return $query->where('approved', 2);
            })
            ->when($pending, function ($query) {
                return $query->where('approved', 0);
            })
            ->orderBy('id', 'desc')
            ->paginate(Helpers::pagination_limit());

        return view('seller-views.withdraw.list', compact('withdraw_requests'));
    }
    public function bonusList()
    {
        // $all = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'all' ? 1 : 0;
        // $active = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'approved' ? 1 : 0;
        // $denied = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'denied' ? 1 : 0;
        // $pending = session()->has('withdraw_status_filter') && session('withdraw_status_filter') == 'pending' ? 1 : 0;

        // $withdraw_requests = WithdrawRequest::with(['seller'])
        //     ->where(['seller_id'=>auth('seller')->id()])
        //     ->when($all, function ($query) {
        //         return $query;
        //     })
        //     ->when($active, function ($query) {
        //         return $query->where('approved', 1);
        //     })
        //     ->when($denied, function ($query) {
        //         return $query->where('approved', 2);
        //     })
        //     ->when($pending, function ($query) {
        //         return $query->where('approved', 0);
        //     })
        //     ->orderBy('id', 'desc')
        //     ->paginate(Helpers::pagination_limit());
        $query_param = [];

        $cou = RepurchaseTransaction::leftJoin('users as u2', 'u2.id', '=', 'repurchase_transactions.referral_id')
            ->where('repurchase_transactions.parent_type','=','shop')
            ->where('repurchase_transactions.parent_id','=',auth('seller')->id())
            ->select('repurchase_transactions.*','u2.f_name as referral_f_name', 'u2.l_name as referral_l_name')
            ->latest()
            ->paginate(Helpers::pagination_limit());

        return view('seller-views.withdraw.bonus_list', compact('cou'));
    }

    public function method_list(Request $request)
    {
        $method = WithdrawalMethod::ofStatus(1)->where('id', $request->method_id)->first();

        return response()->json(['content'=>$method], 200);
    }
}
