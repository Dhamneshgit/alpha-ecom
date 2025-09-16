<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\Admin;
use App\User;
use App\Model\Zipcode;
use App\Model\Plan;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\Subscription;
use App\Model\BusinessSetting;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;
use App\CPU\ImageManager;

class CustomerController extends Controller
{
    public function customer_list(Request $request)
    {
        $admin = Admin::find(auth('admin')->id());
        $query_param = [];
        $search = $request['search'];

        if ($admin->admin_role_id == 6) {

            $cityname = $admin->state;
            if ($request->has('search')) {
                $key = explode(' ', $request['search']);
                $customers = User::with(['orders'])
                    ->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%");
                        }
                    });
                $query_param = ['search' => $request['search']];
            } else {
                $customers = User::with(['orders'])
                    ->where('state', $cityname);
            }
        } elseif ($admin->admin_role_id == 5) {
            $city = DB::table('cities')->where('id', $admin->city_id)->first();
            $cityname = $city->city;
            if ($request->has('search')) {
                $key = explode(' ', $request['search']);
                $customers = User::with(['orders'])
                    ->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%");
                        }
                    });
                $query_param = ['search' => $request['search']];
            } else {
                $customers = User::with(['orders'])
                    ->where('city', $cityname);
            }
        } elseif ($admin->admin_role_id == 2) {
            if ($request->has('search')) {
                $key = explode(' ', $request['search']);
                $customers = User::with(['orders'])
                    ->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%");
                        }
                    });
                $query_param = ['search' => $request['search']];
            } else {
                $customers = User::with(['orders'])
                    ->where('zipcode', $admin->zipcode);
            }
        } else {
            if ($request->has('search')) {
                $key = explode(' ', $request['search']);
                $customers = User::with(['orders'])
                    ->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%")
                                ->orWhere('phone', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%");
                        }
                    });
                $query_param = ['search' => $request['search']];
            } else {
                $customers = User::with(['orders']);
            }
        }
        $customers = $customers->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.customer.list', compact('customers', 'search'));
    }

    public function add_new()
    {
        // $zipcode = Zipcode::get();
        return view('admin-views.customer.add-new');
    }

    public function store(Request $request)
    {


        $request->validate([
            // 'name' => 'required',
            // 'zipcode' => 'required',
            // 'image' => 'required',
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'phone' => 'required|unique:users'

        ], [
            // 'name.required' => 'Role name is required!',
            'f_name.required' => 'First name is required!',
            'l_name.required' => 'Last name is required!',
            'email.required' => 'Email id is Required',
            'image.required' => 'Image is Required',

        ]);
        if ($request->confirm_password != $request->password) {
            Toastr::error('Confirm password and password does not match');
            return redirect()->back();
        }

        DB::table('users')->insert([
            // 'name' => $request->name,
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'state' => $request->state,
            'city' => $request->city,
            'street_address' => $request->address,
            'address' => $request->address,
            'zipcode' => $request->zipcode,
            'latitude' => $request->latitude,
            'longitude ' => $request->longitude,
            'age' => $request->age,
            'gender' => $request->gender,
            // 'admin_role_id' => $request->role_id,
            'password' => bcrypt($request->password),
            // 'status'=>1,
            'image' => ImageManager::upload('users/', 'png', $request->file('image')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Toastr::success('User added successfully!');
        return redirect()->route('admin.customer.list');
    }

    public function edit($user_id)
    {
        $customer = DB::table('users')->where('id', $user_id)->first();

        if (!$customer) {
            Toastr::error('Customer not found!');
            return redirect()->route('admin.customer.list');
        }

        return view('admin-views.customer.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = User::findOrFail($id);

        $request->validate([
            // 'name' => 'required',
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => 'required|unique:users,phone,' . $customer->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp,tiff|max:2048',
            'password' => 'nullable|min:8|confirmed',
        ], [
            // 'name.required' => 'Name is required!',
            'f_name.required' => 'First Name is required!',
            'l_name.required' => 'Last Name is required!',
            'email.required' => 'Email is required!',
            'password.confirmed' => 'Password and confirm password do not match!',
            'password.min' => 'Password must be at least 8 characters long!',
        ]);

        if ($request->hasFile('image')) {
            $customer->image = ImageManager::update('profile/', $customer->image, 'png', $request->file('image'));
        }

        if ($request->filled('password')) {
            $customer->password = bcrypt($request->password);
        }

        // $customer->name = $request->name;
        $customer->f_name = $request->f_name;
        $customer->l_name = $request->l_name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->age = $request->age;
        $customer->state = $request->state;
        $customer->city = $request->city;
        $customer->street_address = $request->address;
        $customer->address = $request->address;
        $customer->zipcode = $request->zipcode;
        $customer->latitude = $request->latitude;
        $customer->longitude = $request->longitude;
        $customer->gender = $request->gender;

        $customer->save();

        Toastr::success('Customer updated successfully!');
        return redirect()->route('admin.customer.list');
    }

    public function status_update(Request $request)
    {
        User::where(['id' => $request['id']])->update([
            'is_active' => $request['status']
        ]);

        DB::table('oauth_access_tokens')
            ->where('user_id', $request['id'])
            ->delete();

        return response()->json([], 200);
    }

    public function view(Request $request, $id)
    {

        $customer = User::find($id);
        if (isset($customer)) {
            $query_param = [];
            $search = $request['search'];
            $orders = Order::where(['customer_id' => $id]);
            if ($request->has('search')) {

                $orders = $orders->where('id', 'like', "%{$search}%");
                $query_param = ['search' => $request['search']];
            }
            $orders = $orders->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
            $kyc = DB::table('kyc_details')->where('user_id', $id)->where('type', 'user')->first();
            return view('admin-views.customer.customer-view', compact('customer', 'orders', 'search', 'kyc'));
        }
        Toastr::error('Customer not found!');
        return back();
    }
    public function certificate(Request $request, $id)
    {

        $customer = User::find($id);
        if (isset($customer) && !empty($customer->plan_id)) {
            $query_param = [];
            $search = $request['search'];
            $orders = Order::where(['customer_id' => $id]);
            if ($request->has('search')) {

                $orders = $orders->where('id', 'like', "%{$search}%");
                $query_param = ['search' => $request['search']];
            }
            $orders = $orders->latest()->paginate(Helpers::pagination_limit())->appends($query_param);

            $plan = Plan::find($customer->plan_id);
            return view('admin-views.customer.certificate', compact('customer', 'plan', 'orders', 'search'));
        }
        Toastr::error('Plan not found!');
        return back();
    }
    public function kycstatus($id, $status, $type = "user")
    {

        $kyc = DB::table('kyc_details')->where('user_id', $id)->where('type', 'user')->first();
        if ($kyc) {
            $kyc = DB::table('kyc_details')->where('user_id', $id)->where('type', 'user')->update(['status' => $status]);
            Toastr::success('Status Upadte Succesfully');
            return back();
        } else {
            Toastr::error('Something went Wrong');
            return back();
        }
    }

    public function delete($id)
    {
        $customer = User::find($id);
        $customer->delete();
        Toastr::success('Customer deleted successfully!');
        return back();
    }

    public function subscriber_list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $subscription_list = Subscription::where('email', 'like', "%{$search}%");

            $query_param = ['search' => $request['search']];
        } else {
            $subscription_list = new Subscription;
        }
        $subscription_list = $subscription_list->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.customer.subscriber-list', compact('subscription_list', 'search'));
    }

    public function customer_settings()
    {
        $data = BusinessSetting::where('type', 'like', 'wallet_%')->orWhere('type', 'like', 'loyalty_point_%')->get();
        $data = array_column($data->toArray(), 'value', 'type');

        return view('admin-views.customer.customer-settings', compact('data'));
    }

    public function customer_update_settings(Request $request)
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(\App\CPU\translate('update_option_is_disable_for_demo'));
            return back();
        }

        $request->validate([
            'add_fund_bonus' => 'nullable|numeric|max:100|min:0',
            'loyalty_point_exchange_rate' => 'nullable|numeric',
        ]);
        BusinessSetting::updateOrInsert(['type' => 'wallet_status'], [
            'value' => $request['customer_wallet'] ?? 0
        ]);
        BusinessSetting::updateOrInsert(['type' => 'loyalty_point_status'], [
            'value' => $request['customer_loyalty_point'] ?? 0
        ]);
        BusinessSetting::updateOrInsert(['type' => 'wallet_add_refund'], [
            'value' => $request['refund_to_wallet'] ?? 0
        ]);
        BusinessSetting::updateOrInsert(['type' => 'loyalty_point_exchange_rate'], [
            'value' => $request['loyalty_point_exchange_rate'] ?? 0
        ]);
        BusinessSetting::updateOrInsert(['type' => 'loyalty_point_item_purchase_point'], [
            'value' => $request['item_purchase_point'] ?? 0
        ]);
        BusinessSetting::updateOrInsert(['type' => 'loyalty_point_minimum_point'], [
            'value' => $request['minimun_transfer_point'] ?? 0
        ]);

        Toastr::success(\App\CPU\translate('customer_settings_updated_successfully'));
        return back();
    }

    public function get_customers(Request $request)
    {
        $key = explode(' ', $request['q']);
        $data = User::where('id', '!=', 0)->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('f_name', 'like', "%{$value}%")
                    ->orWhere('l_name', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%");
            }
        })
            ->limit(8)
            ->get([DB::raw('id, CONCAT(f_name, " ", l_name, " (", phone ,")") as text')]);
        if ($request->all) $data[] = (object)['id' => false, 'text' => trans('messages.all')];


        return response()->json($data);
    }


    /**
     * Export product list by excel
     * @param Request $request
     * @param $type
     */
    public function export(Request $request)
    {

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customers = User::with(['orders'])
                ->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    }
                });
        } else {
            $customers = User::with(['orders']);
        }
        $items = $customers->latest()->get();

        return (new FastExcel($items))->download('customer_list.xlsx');
    }
    public function withdrawal_list()
    {

        $data = DB::table('withdrawal_request_user')
            ->select('users.f_name', 'users.l_name', 'withdrawal_request_user.*')
            ->leftJoin('users', 'withdrawal_request_user.user_id', '=', 'users.id')
            ->orderBy('id', 'DESC')
            ->latest()
            ->paginate(Helpers::pagination_limit());


        return view('admin-views.plan.withdrawal_transaction', compact('data'));
    }
    public function fund_list()
    {

        $data = DB::table('fund_payments')
            ->select('users.f_name', 'users.l_name', 'fund_payments.*')
            ->leftJoin('users', 'fund_payments.user_id', '=', 'users.id')
            ->orderBy('id', 'DESC')
            ->latest()
            ->paginate(Helpers::pagination_limit());


        return view('admin-views.plan.fund_list', compact('data'));
    }
    public function updatefund(Request $request)
    {

        $update = [
            'status' => $request['status'],
            'remark' => $request['remark'] ?? null,
        ];

        DB::table('fund_payments')->where('id', $request['id'])->update($update);
        $data = DB::table('fund_payments')->where('id', $request['id'])->first();

        if ($request['status'] == 1) {
            $user = User::find($data->user_id);
            $user->fund_wallet = $user->fund_wallet + $data->amount;
            $user->save();

            $transaction1 = [
                'user_id' => $data->user_id,
                'transaction_id' => $data->transaction_id,
                'credit' => $data->amount,
                'transaction_type' => 'fund_add',
                'type' => 'credit'
            ];
            DB::table('wallet_transactions')->insert($transaction1);
        }

        return redirect()->back();
    }
    public function addMoney(Request $request)
    {

        $amount = $request['amount'];
        $user_id = $request['user_id'];

        $user = User::find($user_id);
        $user->fund_wallet = $user->fund_wallet + $amount;
        $user->save();

        $transaction1 = [
            'user_id' => $user_id,
            'transaction_id' => 'admin_add_money',
            'credit' => $amount,
            'transaction_type' => 'fund_add',
            'type' => 'credit'
        ];
        DB::table('wallet_transactions')->insert($transaction1);

        return redirect()->back();
    }
}
