<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\Admin;
use App\Model\Plan;
use App\Model\PlanLevel;
use App\Model\Seller;
use App\Model\Zipcode;
use App\Model\City;
use App\User;
use App\Model\AdminRole;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Model\Shop;

class EmployeeController extends Controller
{

    public function add_new()
    {
        $rls = AdminRole::whereNotIn('id', [1])->get();
        $zipcode = Zipcode::get();
        $users = User::where('is_active', 1)->get();
        return view('admin-views.employee.add-new', compact('rls', 'zipcode', 'users'));
    }
    public function getReferral(Request $request)
    {
        $user_id = $request->input('user_id');
        $user = User::find($user_id);
        if ($user->friend_referral) {
            $parentUser = User::where('referral_code', $user->friend_referral)->first();
            if ($parentUser) {
                return response()->json([
                    'status' => true,
                    'name' => $parentUser->f_name . ' ' . $parentUser->l_name . ' (' . $parentUser->phone . ')',
                    // 'referral_code' => $parentUser->referral_code,
                    'id' => $parentUser->id,
                    'phone' => $parentUser->phone
                ]);
            }
        }

        return response()->json(['status' => false, 'message' => 'Refer User not found']);
    }
    public function add_new_district()
    {
        $rls = AdminRole::whereNotIn('id', [1])->get();
        $zipcode = Zipcode::get();
        $city = City::get();
        return view('admin-views.employee.add-new-district', compact('rls', 'city','zipcode'));
    }
    public function add_new_doctor()
    {
        $rls = AdminRole::whereNotIn('id', [1])->get();
        // $zipcode = Zipcode::get();
        $city = City::get();
        return view('admin-views.employee.add-new-doctor', compact('rls', 'city'));
    }


    public function doctor_store(Request $request)
    {
        // dd($request);
        $request->validate([
            'name' => 'required',
            'role_id' => 'required',
            'license_number' => 'required',
            'clinic_name' => 'required',
            'clinic_address' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required',
            'phone' => 'required',
            'pan_card' => 'required|unique:admins',
            'address' => 'required',
            'gender' => 'required',

        ], [
            'name.required' => 'Role name is required!',
            'role_name.required' => 'Role id is Required',
            'email.required' => 'Email id is Required',
            'email.unique' => 'Email id is Already taken',
            'pan_card.required' => 'Pan Card No. is Required',
            'address.required' => 'address is Required',
            'gender.required' => 'Gender is Required',

        ]);

        if ($request->role_id == 1) {
            Toastr::warning('Access Denied!');
            return back();
        }

        // $check = DB::table('admins')->where('city_id', $request->city_id)->first();
        // if (!empty($check)) {
        //     Toastr::error('District is already registered on ' . $request->city_id . 'this City!');
        //     return back();
        // }

        DB::table('admins')->insert([
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
            'admin_role_id' => $request->role_id,
            'password' => bcrypt($request->password),
            'status' => 1,
            'certificate_image' => ImageManager::upload('admin/', 'png', $request->file('certificate')),
            'standard_aggrement' => ImageManager::upload('admin/', 'pdf', $request->file('standard_aggrement')),
            // 'clinic_logo' => ImageManager::upload('admin/', 'png', $request->file('logo')),
            // 'clinic_banner' => ImageManager::upload('admin/', 'png', $request->file('banner')),
            'image' => ImageManager::upload('admin/', 'png', $request->file('image')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Toastr::success('Doctor added successfully!');
        return redirect()->route('admin.employee.doctor_list');
    }

    function doctor_list(Request $request)
    {
        $search = $request['search'];
        $key = explode(' ', $request['search']);
        $em = Admin::select('admins.*', 'cities.city')
            ->where('admin_role_id', 5)
            // $em = Admin::with(['role'])->whereNotIn('id', [1,2,3,4])
            ->when($search != null, function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            })
            ->leftJoin('cities', 'admins.city_id', '=', 'cities.id')
            ->paginate(Helpers::pagination_limit());
        return view('admin-views.employee.doctor_list', compact('em', 'search'));
    }
    public function add_new_state()
    {
        $rls = AdminRole::whereNotIn('id', [1])->get();
        $zipcode = Zipcode::get();
        return view('admin-views.employee.add-new-state', compact('rls','zipcode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'role_id' => 'required',
            // 'image' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required',
            'phone' => 'required',
            // 'dob' => 'required',
            'pan_card' => 'required|unique:admins',
            'address' => 'required',
            'gst' => 'required|unique:admins',
            // 'gender' => 'required',

            // 'zipcode'=>'required'

        ], [
            'name.required' => 'Role name is required!',
            'role_name.required' => 'Role id is Required',
            'email.required' => 'Email id is Required',
            // 'image.required' => 'Image is Required',
            // 'dob.required' => 'Date Of Birth is Required',
            'pan_card.required' => 'Pan Card No. is Required',
            'address.required' => 'address is Required',
            'gst.required' => 'Gst is Required',
            // 'gender.required' => 'Gender is Required',
            // 'zipcode.required' => 'Zipcode is Required',

        ]);

        if ($request->role_id == 1) {
            Toastr::warning('Access Denied!');
            return back();
        }

        // $check = DB::table('admins')->where('zipcode', $request->zipcode)->first();
        // if (!empty($check)) {
        //     Toastr::error('Franchise is already registered on ' . $request->zipcode . 'this zipcode!');
        //     return back();
        // }


        DB::table('admins')->insert([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            // 'dob' => $request->dob,
            'pan_card' => $request->pan_card,
            'address' => $request->address,
            'gst' => $request->gst,
            // 'gender' => $request->gender,
            'specialization' => $request->specialization,
            // 'zipcode' => $request->zipcode ?? null,
            'admin_role_id' => $request->role_id,
            'password' => bcrypt($request->password),
            'user_id' => !empty($request->user_id) ? $request->user_id : null,
            // 'referral_id' => !empty($request->refer_id) ? $request->refer_id : null,
            'status' => 1,
            'image' => ImageManager::upload('admin/', 'png', $request->file('image')),
            'created_at' => now(),
            'updated_at' => now(),


        ]);

        Toastr::success('Aggregator added successfully!');
        return redirect()->route('admin.employee.list');
    }
    public function districtstore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'role_id' => 'required',
            'image' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required',
            'phone' => 'required',
            // 'dob' => 'required',
            'pan_card' => 'required|unique:admins',
            'address' => 'required',
            'gst' => 'required|unique:admins',
            'aadhar_number' => 'required|unique:admins',
            'zipcode'=>'required|unique:admins'
            // 'gender' => 'required',
            // 'city_id' => 'required'

        ], [
            'name.required' => 'Role name is required!',
            'role_name.required' => 'Role id is Required',
            'email.required' => 'Email id is Required',
            'email.unique' => 'This Email is already in use',
            // 'image.required' => 'Image is Required',
            // 'dob.required' => 'Date Of Birth is Required',
            'pan_card.required' => 'Pan Card No. is Required',
            'address.required' => 'address is Required',
            'gst.required' => 'Gst is Required',
            // 'gender.required' => 'Gender is Required',
            'aadhar_number.required' => 'Aadhar is Required',
            'aadhar_number.unique' => 'This Aadhar Number is already in use',
            // 'city_id.required' => 'City is Required',
            'zipcode.required' => 'Zipcode is Required',
            'zipcode.unique' => 'This zipcode is already in use',

        ]);

        if ($request->role_id == 1) {
            Toastr::warning('Access Denied!');
            return back();
        }

        // $check = DB::table('admins')->where('city_id', $request->city_id)->first();
        // if (!empty($check)) {
        //     Toastr::error('District is already registered on ' . $request->city_id . 'this City!');
        //     return back();
        // }

        DB::table('admins')->insert([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            // 'dob' => $request->dob,
            'pan_card' => $request->pan_card,
            'address' => $request->address,
            'gst' => $request->gst,
            // 'gender' => $request->gender,
            'zipcode' => $request->zipcode,
            'aadhar_number' => $request->aadhar_number,
            // 'city_id' => $request->city_id ?? null,
            'admin_role_id' => $request->role_id,
            'password' => bcrypt($request->password),
            'status' => 1,
            'image' => ImageManager::upload('admin/', 'png', $request->file('image')),
            'bank_statement' => ImageManager::upload('admin/', 'pdf', $request->file('bank_statement')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Toastr::success('Aggregator added successfully!');
        return redirect()->route('admin.employee.district_list');
    }
    public function statestore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'role_id' => 'required',
            'image' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required',
            'phone' => 'required',
            // 'dob' => 'required',
            // 'pan_card' => 'required|unique:admins',
            // 'address' => 'required',
            // 'gst' => 'required|unique:admins',
            'gender' => 'required',
            // 'state' => 'required'

        ], [
            'name.required' => 'Role name is required!',
            'role_name.required' => 'Role id is Required',
            'email.required' => 'Email id is Required',
            'image.required' => 'Image is Required',
            'email.unique' => 'This Email is already in use',
            // 'dob.required' => 'Date Of Birth is Required',
            // 'pan_card.required' => 'Pan Card No. is Required',
            // 'address.required' => 'address is Required',
            // 'gst.required' => 'Gst is Required',
            'gender.required' => 'Gender is Required',
            // 'state.required' => 'State is Required',

        ]);

        if ($request->role_id == 1) {
            Toastr::warning('Access Denied!');
            return back();
        }

        // $check = DB::table('admins')->where('state', $request->state)->first();
        // if (!empty($check)) {
        //     Toastr::error('State is already registered on ' . $request->State . 'this State!');
        //     return back();
        // }

        DB::table('admins')->insert([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            // 'dob' => $request->dob,
            // 'pan_card' => $request->pan_card,
            // 'address' => $request->address,
            // 'gst' => $request->gst,
            'gender' => $request->gender,
            // 'zipcode' => $request->zipcode,
            // 'city_id' => $request->city_id,
            // 'state' => $request->state,
            'admin_role_id' => $request->role_id,
            'password' => bcrypt($request->password),
            'status' => 1,
            'image' => ImageManager::upload('admin/', 'png', $request->file('image')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Toastr::success('Admin added successfully!');
        return redirect()->route('admin.employee.state_list');
    }

    function list(Request $request)
    {
        $search = $request['search'];
        $key = explode(' ', $request['search']);
        // $em = Admin::with(['role'])->whereNotIn('id', [1])
        $em = Admin::select('admins.*', 'users.f_name', 'users.l_name', 'u1.f_name as username', 'u1.l_name as userlastname')
            ->where('admin_role_id', 2)
            ->when($search != null, function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('admins.name', 'like', "%{$value}%")
                        ->orWhere('admins.phone', 'like', "%{$value}%")
                        ->orWhere('admins.email', 'like', "%{$value}%");
                }
            })
            ->leftJoin('users', 'admins.referral_id', '=', 'users.id')
            ->leftJoin('users as u1', 'admins.user_id', '=', 'u1.id')
            ->paginate(Helpers::pagination_limit());
        return view('admin-views.employee.list', compact('em', 'search'));
    }
    function district_list(Request $request)
    {
        $search = $request['search'];
        $key = explode(' ', $request['search']);
        $em = Admin::select('admins.*', 'cities.city')
            ->where('admin_role_id', 3)
            // $em = Admin::with(['role'])->whereNotIn('id', [1,2,3,4])
            ->when($search != null, function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            })
            ->leftJoin('cities', 'admins.city_id', '=', 'cities.id')
            ->paginate(Helpers::pagination_limit());
        return view('admin-views.employee.district_list', compact('em', 'search'));
    }
    function state_list(Request $request)
    {
        $search = $request['search'];
        $key = explode(' ', $request['search']);
        // $em = Admin::with(['role'])->whereNotIn('id', [1,2,3,4,5])
        $em = Admin::where('admin_role_id', 4)
            ->when($search != null, function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            })
            ->paginate(Helpers::pagination_limit());
        return view('admin-views.employee.state_list', compact('em', 'search'));
    }

    public function edit($id)
    {
        $e = Admin::where(['id' => $id])->first();
        $rls = AdminRole::whereNotIn('id', [1])->get();
        $zipcode = Zipcode::get();
        $city = City::get();
        return view('admin-views.employee.edit', compact('rls', 'e', 'zipcode', 'city'));
    }

    public function edit_doctor($id)
    {
        $e = Admin::where(['id' => $id])->first();
        $rls = AdminRole::whereNotIn('id', [1])->get();
        $zipcode = Zipcode::get();
        $city = City::get();
        return view('admin-views.employee.edit_doctor', compact('rls', 'e', 'zipcode', 'city'));
    }

    public function update_doctor(Request $request, $id)
    {
        // dd($request);
        $request->validate([
            'name' => 'required',
            'role_id' => 'required',
            'email' => 'required|email|unique:admins,email,' . $id,
            'clinic_name' => 'required',
            'clinic_address' => 'required',
            // 'qualification' => 'required',
            // 'qualification' => 'required',
            // 'license_number' => 'required',
            // 'dob' => 'required',
            'pan_card' => 'required',
            'address' => 'required',
            // 'gst' => 'required',
            'gender' => 'required',
        ], [
            'name.required' => 'Role name is required!',
            'dob.required' => 'Date Of Birth is Required',
            // 'pan_card.required' => 'Pan Card No. is Required',
            'address.required' => 'address is Required',
            // 'gst.required' => 'Gst is Required',
            'gender.required' => 'Gender is Required',
        ]);

        if ($request->role_id == 1) {
            Toastr::warning('Access Denied!');
            return back();
        }

        $e = Admin::find($id);
        if ($request['password'] == null) {
            $pass = $e['password'];
        } else {
            if (strlen($request['password']) < 7) {
                Toastr::warning('Password length must be 8 character.');
                return back();
            }
            $pass = bcrypt($request['password']);
        }

        if ($request->has('certificate_image')) {
            $e['certificate_image'] = ImageManager::update('admin/', $e['certificate_image'], 'png', $request->file('certificate_image'));
        }
        if ($request->has('standard_aggrement')) {
            $e['standard_aggrement'] = ImageManager::update('admin/', $e['standard_aggrement'], 'png', $request->file('standard_aggrement'));
        }
        if ($request->has('clinic_logo')) {
            $e['clinic_logo'] = ImageManager::update('admin/', $e['logo'], 'png', $request->file('clinic_logo'));
        }
        if ($request->has('clinic_banner')) {
            $e['clinic_banner'] = ImageManager::update('admin/', $e['banner'], 'png', $request->file('clinic_banner'));
        }
        if ($request->has('image')) {
            $e['image'] = ImageManager::update('admin/', $e['image'], 'png', $request->file('image'));
        }

        DB::table('admins')->where(['id' => $id])->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'qualification' => $request->qualification,
            'clinic_name' => $request->clinic_name,
            'clinic_address' => $request->clinic_address,
            'license_number' => $request->license_number,
            'qualification' => $request->qualification,
            'passing_year' => $request->passing_year,
            'medical_council' => $request->medical_council,
            // 'dob' => $request->dob,
            'pan_card' => $request->pan_card,
            'address' => $request->address,
            'gst' => $request->gst,
            'gender' => $request->gender,
            'admin_role_id' => $request->role_id,
            'password' => $pass,
            'image' => $e['image'],
            'certificate_image' => $e['certificate_image'],
            'standard_aggrement' => $e['standard_aggrement'],
            'updated_at' => now(),
        ]);

        Toastr::success('Updated successfully!');
        return back();
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'role_id' => 'required',
            'email' => 'required|email|unique:admins,email,' . $id,

            // 'dob' => 'required',
            // 'pan_card' => 'required',
            // 'address' => 'required',
            // 'gst' => 'required',
            // 'gender' => 'required',
        ], [
            'name.required' => 'Role name is required!',
            'email.required' => 'Email is required!',
            'email.unique' => 'Email is Already Taken!',
            // 'dob.required' => 'Date Of Birth is Required',
            // 'pan_card.required' => 'Pan Card No. is Required',
            // 'address.required' => 'address is Required',
            // 'gst.required' => 'Gst is Required',
            // 'gender.required' => 'Gender is Required',
        ]);

        if ($request->role_id == 1) {
            Toastr::warning('Access Denied!');
            return back();
        }

        $e = Admin::find($id);
        if ($request['password'] == null) {
            $pass = $e['password'];
        } else {
            if (strlen($request['password']) < 7) {
                Toastr::warning('Password length must be 8 character.');
                return back();
            }
            $pass = bcrypt($request['password']);
        }

        if ($request->has('image')) {
            $e['image'] = ImageManager::update('admin/', $e['image'], 'png', $request->file('image'));
        }
        if ($request->has('bank_statement')) {
            $e['bank_statement'] = ImageManager::update('admin/', $e['bank_statement'], 'pdf', $request->file('bank_statement'));
        }

        DB::table('admins')->where(['id' => $id])->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            // 'dob' => $request->dob,
            'pan_card' => $request->pan_card,
            'address' => $request->address,
            'gst' => $request->gst,
            // 'gender' => $request->gender,
            'admin_role_id' => $request->role_id,
            'specialization' => $request->specialization ?? null,
            'zipcode' => $request->zipcode ?? null,
            'password' => $pass,
            'image' => $e['image'],
            'bank_statement' => $e['bank_statement'],
            'updated_at' => now(),
        ]);

        Toastr::success('Updated successfully!');
        return back();
    }
    public function status(Request $request)
    {
        $employee = Admin::find($request->id);
        $employee->status = $request->status;
        $employee->save();

        Toastr::success('Employee status updated!');
        return back();
    }

    public function withdrawal_list()
    {

        $data = DB::table('withdrawal_request_frenchise')
            ->select('admins.name', 'withdrawal_request_frenchise.*')
            ->leftJoin('admins', 'withdrawal_request_frenchise.user_id', '=', 'admins.id')
            ->orderBy('id', 'DESC')
            ->latest()
            ->paginate(Helpers::pagination_limit());


        return view('admin-views.plan.withdrawal_transaction_frenchise', compact('data'));
    }
    public function kyc_list()
    {

        $data = DB::table('kyc_details')
            ->select('admins.name', 'kyc_details.*')
            ->where('kyc_details.type', 'admin')
            ->leftJoin('admins', 'kyc_details.user_id', '=', 'admins.id')
            ->orderBy('id', 'DESC')
            ->latest()
            ->paginate(Helpers::pagination_limit());


        return view('admin-views.plan.kyc_list', compact('data'));
    }


    public function get_bonus_referral($is_frenchise = 1, $user_id, $level = 1)
    {

        $code = $this->get_friends_code($user_id);
        if ($code['status']) {
            $check = $this->checkPlan($code['code'], $level, $user_id, $is_frenchise);
            $user = User::where('referral_code', $code['code'])->first();

            $this->get_bonus_referral($is_frenchise = 1, $user->id, $level + 1);
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

    private function checkPlan($referral_code, $level, $user_id, $is_frenchise)
    {
        $today = Carbon::now()->format('Y-m-d');
        $user = User::where('referral_code', $referral_code)->first();
        if ($user) {
            if (($user->plan_status == 1) && ($user->plan_expire_date > $today) && !empty($user->plan_id)) {
                $plan = $this->planData($user->plan_id, $level, $user_id, $user->id, $is_frenchise);
                return true;
            }
        }
        return false;
    }

    public function planData($planId, $level, $user_id, $parent_id, $is_frenchise)
    {
        $plan = Plan::find($planId);
        if ($plan) {
            $planlevel = PlanLevel::where('plan_id', $planId)->where('level_id', $level)->first();
            if ($planlevel) {
                $userData = User::find($parent_id);

                $userData->referral_bonus = $userData->referral_bonus + $planlevel->frenchise_income;
                $userData->save();

                $refferal_add1 = $this->referralTransaction($parent_id, $user_id, $planlevel->frenchise_income, $level, 'frenchise_refer_bonus');
                return true;
            }
        }
        return false;
    }

    public function referralTransaction($parent_id, $referrel_id, $amount, $level, $type = '')
    {
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
