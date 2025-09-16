<?php

namespace App\Http\Controllers\api\v3\seller\auth;

use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\Seller;
use App\Model\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\CPU\Helpers;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class RegisterController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|unique:sellers',
            'shop_address'  => 'required',
            'latitude'  => 'required',
            'longitude'  => 'required',
            'f_name'        => 'required',
            'type'        => 'required',
            'zipcode'        => 'required',
            'l_name'        => 'required',
            'shop_name'     => 'required',
            'phone'         => 'required',
            'password'      => 'required|min:8',
            'image'         => 'required|mimes: jpg,jpeg,png,gif',
            'logo'          => 'required|mimes: jpg,jpeg,png,gif',
            'banner'        => 'required|mimes: jpg,jpeg,png,gif',
            // 'bottom_banner' => 'mimes: jpg,jpeg,png,gif',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=> true,'message' => Helpers::error_processor($validator)], 403);
        }

        $request->vendorID = $this->createVendorId($request->zipcode, $request->type);

        DB::beginTransaction();
        try {
            $seller = new Seller();
            $seller->f_name = $request->f_name;
            $seller->l_name = $request->l_name;
            $seller->unique_code = $request->vendorID;
            $seller->type = $request->type; //goods, service,both
            $seller->phone = $request->phone;
            $seller->email = $request->email;
            $seller->cm_firebase_token = $request->device_token;
            $seller->pan_card = $request->pan_card;
            $seller->gst = $request->gst;
            $seller->aadhar = $request->aadhar;
            $seller->address = $request->shop_address;
            $seller->latitude = $request->latitude;
            $seller->longitude = $request->longitude;
            $seller->zipcode = $request->zipcode;
            $seller->city =  $request->city;
            $seller->state =  $request->state;
            $seller->area =  $request->area;
            $seller->image = ImageManager::upload('seller/', 'png', $request->file('image'));
            $seller->password = bcrypt($request->password);
            $seller->status =  $request->status == 'approved'?'approved': "pending";
            $seller->save();

            $shop = new Shop();
            $shop->seller_id = $seller->id;
            $shop->name = $request->shop_name;
            $shop->seller_type = $request->type; //goods, service,both
            $shop->address = $request->shop_address;
            $shop->contact = $request->phone;
            $shop->image = ImageManager::upload('shop/', 'png', $request->file('logo'));
            $shop->banner = ImageManager::upload('shop/banner/', 'png', $request->file('banner'));
            $shop->bottom_banner = ImageManager::upload('shop/banner/', 'png', $request->file('bottom_banner'));
            $shop->save();

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
            DB::commit();
            return response()->json(['status'=>true,'message' => translate('Shop apply successfully!')], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status'=>false,'message' => translate('Shop apply fail!'),'data'=>$e], 403);
        }

    }

    public function createVendorId($pincode, $vendorType)
    {

        $vendorTypeMap = [
            'both' => 'B',
            'goods' => 'G',
            'service' => 'S'
        ];

        // Extract Required Values
        $townway = 'TW'; // Static
        $vendorCode = $vendorTypeMap[$vendorType];
        $pincodePrefix = substr($pincode, 0, 2);

        // Generate Serial Number
        $lastVendor = Seller::where('zipcode', 'LIKE', $pincodePrefix . '%')
                ->where('type', $vendorType)
                ->orderBy('id', 'desc')
                ->first();

                
        // Determine Next Serial Number
        if (($lastVendor) && ($lastVendor->unique_code)) {
            $lastSerial = (int)substr($lastVendor->unique_code, 5);
            $nextSerial = $lastSerial + 1;
        } else {
            $nextSerial = 1001; // Start Serial at 1001
        }
        // dd($nextSerial);

        $vendorId = $townway . $pincodePrefix . $vendorCode . $nextSerial;

        return $vendorId;
    }
}
