<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\CustomerManager;
use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\CPU\CartManager;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Model\DeliveryCountryCode;
use App\Model\DeliveryZipCode;
use App\Model\Order;
use App\Model\Booking;
use App\Model\OrderDetail;
use App\Model\TimeSlot;
use App\Model\Service;
use App\Model\Seller;
use App\Model\ShippingAddress;
use App\Model\SupportTicket;
use App\Model\SupportTicketConv;
use App\Model\Wishlist;
use App\Traits\CommonTrait;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use function App\CPU\translate;

class CustomerController extends Controller
{
    use CommonTrait;
    public function info(Request $request)
    {
        $user = $request->user();
        $user->certificate = ($user->plan_id) ? url('/certificate/'.$user->id) : '';

        if($user->friend_referral){
            $refer_data = User::where('referral_code',trim($user->friend_referral))->first();
            if(!$refer_data){
                $refer_data = User::where('phone',trim($user->friend_referral))->first();
            }
            if($refer_data){
                $referr['name'] = $refer_data->f_name.' '.$refer_data->l_name; 
                $referr['number'] = $refer_data->phone; 
                $referr['email'] = $refer_data->email; 

                $user->referr_data = $referr;
            }

        }

        return response()->json($user, 200);
    }

    public function create_support_ticket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'type' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $request['customer_id'] = $request->user()->id;
        $request['priority'] = 'low';
        $request['status'] = 'pending';

        try {
            CustomerManager::create_support_ticket($request);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [
                    'code' => 'failed',
                    'message' => 'Something went wrong',
                ],
            ], 422);
        }
        return response()->json(['message' => 'Support ticket created successfully.'], 200);
    }
    public function account_delete(Request $request, $id)
    {
        if($request->user()->id == $id)
        {
            $user = User::find($id);

            ImageManager::delete('/profile/' . $user['image']);

            $user->delete();
           return response()->json(['message' => translate('Your_account_deleted_successfully!!')],200);

        }else{
            return response()->json(['message' =>'access_denied!!'],403);
        }
    }

    public function reply_support_ticket(Request $request, $ticket_id)
    {
        $support = new SupportTicketConv();
        $support->support_ticket_id = $ticket_id;
        $support->admin_id = 1;
        $support->customer_message = $request['message'];
        $support->save();
        return response()->json(['message' => 'Support ticket reply sent.'], 200);
    }

    public function get_support_tickets(Request $request)
    {
        return response()->json(SupportTicket::where('customer_id', $request->user()->id)->get(), 200);
    }

    public function get_support_ticket_conv($ticket_id)
    {
        return response()->json(SupportTicketConv::where('support_ticket_id', $ticket_id)->get(), 200);
    }

    public function add_to_wishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $wishlist = Wishlist::where('customer_id', $request->user()->id)->where('product_id', $request->product_id)->first();

        if (empty($wishlist)) {
            $wishlist = new Wishlist;
            $wishlist->customer_id = $request->user()->id;
            $wishlist->product_id = $request->product_id;
            $wishlist->save();
            return response()->json(['message' => translate('successfully added!')], 200);
        }

        return response()->json(['message' => translate('Already in your wishlist')], 409);
    }

    public function remove_from_wishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $wishlist = Wishlist::where('customer_id', $request->user()->id)->where('product_id', $request->product_id)->first();

        if (!empty($wishlist)) {
            Wishlist::where(['customer_id' => $request->user()->id, 'product_id' => $request->product_id])->delete();
            return response()->json(['message' => translate('successfully removed!')], 200);

        }
        return response()->json(['message' => translate('No such data found!')], 404);
    }

    public function wish_list(Request $request)
    {

        $wishlist = Wishlist::whereHas('wishlistProduct',function($q){
            return $q;
        })->with(['product'])->where('customer_id', $request->user()->id)->get();

        return response()->json($wishlist, 200);
    }

    public function address_list(Request $request)
    {
        return response()->json(ShippingAddress::where('customer_id', $request->user()->id)->latest()->get(), 200);
    }
    public function getServices(Request $request, $id)
    {
        // dd($request);
        $radius = 6;

        $latitude = isset($request['latitude']) ? $request['latitude'] : null;
        $longitude = isset($request['longitude']) ? $request['longitude'] : null;
        $vendor_id = isset($request['vendor_id']) ? $request['vendor_id'] : null;
        
        if($latitude && $longitude){
            $products = Service::with('seller','time_slots','rating','reviews.customer')
                                ->whereHas('seller', function($q) use($latitude, $longitude, $radius,$vendor_id) {
                                    $q->whereRaw("
                                        (6371 * acos(cos(radians(?)) 
                                        * cos(radians(latitude)) 
                                        * cos(radians(longitude) - radians(?)) 
                                        + sin(radians(?)) 
                                        * sin(radians(latitude)))) < ?", [$latitude, $longitude, $latitude, $radius]
                                    )
                                    ->when($vendor_id, function ($q) use ($vendor_id) {
                                        $q->where('unique_code', $vendor_id);
                                    });
                                })
                                ->where('category_id',$id)
                                ->where('status',1)
                                ->orderBy('id', 'DESC')
                                ->get();

        }else{
            $products = Service::with('seller','time_slots','rating','reviews.customer')
                            ->whereHas('seller', function ($q) use ($vendor_id) {
                                $q->when($vendor_id, function ($q) use ($vendor_id) {
                                    $q->where('unique_code', $vendor_id);
                                });
                            })    
                            ->where('category_id',$id)
                            ->where('status',1)
                            ->orderBy('id', 'DESC')
                            ->get();
        }


        // $ground = Ground::selectRaw("*, (6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))) AS distance")
        //     ->where('parent_ground_id', 0)
        //     ->where('is_active', 1)
        //     ->having("distance", "<", $radius) // Filter by distance
        //     ->orderBy("distance") // Order by distance
        //     ->take(9) // Limit to 9 results
        //     ->get();


        // if($id == 0){
        //     // $products = Service::with('seller','rating','reviews.customer')->orderBy('id', 'DESC')->paginate(Helpers::pagination_limit());
        //     $products = Service::with('seller' ,'time_slots','rating','reviews.customer')->where('status',1)->orderBy('id', 'DESC')->get();
        // } else {
        //     // $products = Service::with('seller','rating','reviews.customer')->where('category_id',$id)->orderBy('id', 'DESC')->paginate(Helpers::pagination_limit());
        //     $products = Service::with('seller','time_slots','rating','reviews.customer')->where('category_id',$id)->where('status',1)->orderBy('id', 'DESC')->get();
        // }

        return response()->json(['status' => true , 'data'=>$products,'message'=>'Successfully']);
    
    }
    public function getTimeSlot(Request $request)
    {
        $service = Service::find($request->service_id);
        if($service){
            $timeslotes  = TimeSlot::where('service_id',$request->service_id)->get()->toArray();
            $data = [];
            if(!empty($timeslotes)){

                foreach($timeslotes as $key=>$value){
                    $checkAvaibility = $this->checkAvaibility($request->date, $request->service_id, $value['id']);
                    $slot['slot_id'] = $value['id'];
                    $slot['from_time'] = $value['from_time'];
                    $slot['to_time'] = $value['to_time'];
                    $slot['is_booked'] = ($checkAvaibility) ? 0 : 1;

                    $data[] = $slot;

                }
                
                return response()->json(['status' => true , 'message'=>'Successfully','data'=>$data]);
                
            }else {
                return response()->json(['status' => false , 'message'=>'No Time Slots Available','data'=>$data]);
            }

        }else {
            return response()->json(['status' => false , 'message'=>'Service Not found']);
        }
    
    }
    public function rescheduleBooking(Request $request)
    {
        $booking = Booking::find($request->booking_id);
        if($booking){

            $booking->reschedule_user_status = 0;
            $booking->status = ($request->status) ? 3 : 4 ;
            $booking->save();

            $message = ($request->status) ? 'Re-schedule' : 'Cancelled' ;

            $user = Seller::find($booking->seller_id);
            if($user->cm_firebase_token){
                $token = $user->cm_firebase_token; 
                $title = 'Reschedule Booking';
                $username = $request->user()->f_name.' '.$request->user()->l_name;
                $body = $username.' has '.$message.' your reschedule service request';
                
                $data = []; //$friendData;
                Helpers::sendNotification($token, $title, $body, $data);
                Helpers::createNotification($title,$body,null,$request->seller_id,null);
            }

            return response()->json(['status' => true , 'message'=>'Booking '.$message.' Successfully ']);

        }else {
            return response()->json(['status' => false , 'message'=>'Booking Not found']);
        }
    
    }

    public function add_new_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'address' => 'required',
            'city' => 'required',
            'zip' => 'required',
            'country' => 'required',
            'phone' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'is_billing' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zip_restrict_status = Helpers::get_business_settings('delivery_zip_code_area_restriction');
        $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');

        if ($country_restrict_status && !self::delivery_country_exist_check($request->input('country'))) {
            return response()->json(['message' => translate('Delivery_unavailable_for_this_country')], 403);

        } elseif ($zip_restrict_status && !self::delivery_zipcode_exist_check($request->input('zip'))) {
            return response()->json(['message' => translate('Delivery_unavailable_for_this_zip_code_area')], 403);
        }


        $address = [
            'customer_id' => $request->user()->id,
            'contact_person_name' => $request->contact_person_name,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'city' => $request->city,
            'zip' => $request->zip,
            'country' => $request->country,
            'phone' => $request->phone,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_billing' => $request->is_billing,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        ShippingAddress::insert($address);
        return response()->json(['message' => translate('successfully added!')], 200);
    }
    public function doctorAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_datetime' => 'required',
            'patient_mobile' => 'required',
            'patient_email' => 'required',
            'patient_gender' => 'required'
        ], [
            'booking_datetime.required' => 'Booking Datetime is required!',
            'patient_email.required' => 'Patient Email is Required',
            'patient_mobile.required' => 'Patient Mobile is Required',
            'patient_gender.required' => 'Patient Gender is Required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order_id = 10000 + Booking::all()->count() + 1;

        $booking = [
            'booking_id' => $order_id,
            'user_id' => $request->user()->id,
            'booking_type' => 'doctor',
            'patient_name' => $request->patient_name,
            'patient_age' => $request->patient_age,
            'patient_gender' => $request->patient_gender,
            'patient_email' => $request->patient_email,
            'patient_mobile' => $request->patient_mobile,
            'house_number' => $request->house_number,
            'street_name' => $request->street_name,
            'locality' => $request->locality,
            'pincode' => $request->pincode,
            'area' => $request->area,
            'landmark' => $request->landmark,
            'complaint' => $request->complaint,
            'booking_datetime' => $request->booking_datetime,
            'alternate_datetime' => $request->alternate_datetime,
            'paid_amount' => $request->paid_amount,
            'created_at' => now(),
        ];
        $booking = Booking::insert($booking);
        if($booking){
            return response()->json(['status' => true ,'message' => translate('Appointment Registered Successfully')], 200);
        }else {
            return response()->json(['status' => false ,'message' => translate('Something Went wrong')], 200);
        }
    }
    public function bookingServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_datetime' => 'required',
            'booking_mobile' => 'required',
            'booking_name' => 'required',
            'booking_address' => 'required',
            'slot_id' => 'required'
        ], [
            'booking_datetime.required' => 'Booking Datetime is required!',
            'booking_name.required' => 'Booking Name is Required',
            'booking_mobile.required' => 'Booking Mobile is Required',
            'booking_address.required' => 'Booking Address is Required',
            'slot_id.required' => 'Booking Time Slot is Required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'errors' => Helpers::error_processor($validator)], 403);
        }

        $timeSlot = TimeSlot::find($request->slot_id);

        if(!$timeSlot){
            return response()->json(['status' => false ,'message' => translate('TimeSlot not found')], 200);
        }

        $checkAvaibility = $this->checkAvaibility($request->booking_datetime, $request->service_id, $request->slot_id);

        if(!$checkAvaibility){
            return response()->json(['status' => false ,'message' => 'Service is booked on '.$request->booking_datetime.' & '.$timeSlot->from_time], 200);
        }

        $order_id = 10000 + Booking::all()->count() + 1;


        $uploadedImages = []; 
        if ($request->hasFile('images')) {
        
            foreach ($request->file('images') as $image) {
                $path = ImageManager::upload('product/', 'png', $image);
                $uploadedImages[] = $path; 
            }
        }

        $booking = [
            'booking_id' => $order_id,
            'user_id' => $request->user()->id,
            'service_id' => $request->service_id,
            'seller_id' => $request->seller_id,
            // 'booking_type' => 'home_visit',
            'patient_name' => $request->booking_name,
            'patient_email' => $request->booking_email,
            'patient_mobile' => $request->booking_mobile,
            'alternate_mobile' => $request->alternate_mobile,
            'pincode' => $request->pincode,
            'area' => $request->area,
            'city' => $request->city,
            'state' => $request->state,
            'complaint' => $request->comment,
            'booking_datetime' => $request->booking_datetime,
            'slot_id' => $request->slot_id,
            'booking_time' => $timeSlot->from_time,
            'till_time' => $timeSlot->to_time,
            'alternate_datetime' => $request->alternate_datetime ?? null,
            'google_address' => $request->booking_address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'paid_amount' => $request->paid_amount,
            'is_paid' => $request->is_paid ?? 0,
            'images' => (!empty($uploadedImages)) ? json_encode($uploadedImages) : NULL,
            'created_at' => now(),
        ];
        $booking = Booking::insert($booking);
        if($booking){
            $user = Seller::find($request->seller_id);
            if($user->cm_firebase_token){
                $token = $user->cm_firebase_token; 
                $title = 'Service Booking';
                $username = $request->user()->f_name.' '.$request->user()->l_name;
                $body = 'You have a booking for service by '.$username;
                
                $data = []; //$friendData;
                Helpers::sendNotification($token, $title, $body, $data);
                Helpers::createNotification($title,$body,null,$request->seller_id,null);
            }

            return response()->json(['status' => true ,'message' => translate('Service Booking Successfully')], 200);
        }else {
            return response()->json(['status' => false ,'message' => translate('Something Went wrong')], 200);
        }
    }

    public function checkAvaibility($date, $service_id, $slot_id){
        
        $status = true;
        $check  = Booking::where('booking_datetime', $date)
                        ->where('slot_id', $slot_id)
                        ->where('service_id',$service_id)
                        ->where('status' ,'!=',4)
                        ->first();
        if($check){
            $status = false;
        }                

        return $status;

    }
    public function myBookingServices(Request $request)
    {
       
        $user_id = $request->user()->id;

        // $booking = Booking::with('seller','service')->where('user_id', $user_id)->orderBy('id', 'DESC')->paginate(Helpers::pagination_limit());
        $booking = Booking::with('seller','service')->where('user_id', $user_id)->orderBy('id', 'DESC')->get();

        if($booking){
            return response()->json(['status' => true ,'message' => translate('Successfully'),'data' => $booking], 200);
        }else {
            return response()->json(['status' => false ,'message' => translate('No booking Found'),'data' => []], 200);
        }
    }
    public function homevisitAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_datetime' => 'required',
            'patient_mobile' => 'required',
            'patient_email' => 'required',
            'patient_gender' => 'required'
        ], [
            'booking_datetime.required' => 'Booking Datetime is required!',
            'patient_email.required' => 'Patient Email is Required',
            'patient_mobile.required' => 'Patient Mobile is Required',
            'patient_gender.required' => 'Patient Gender is Required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order_id = 10000 + Booking::all()->count() + 1;

        $booking = [
            'booking_id' => $order_id,
            'user_id' => $request->user()->id,
            'booking_type' => 'home_visit',
            'patient_name' => $request->patient_name,
            'patient_age' => $request->patient_age,
            'patient_gender' => $request->patient_gender,
            'patient_email' => $request->patient_email,
            'patient_mobile' => $request->patient_mobile,
            'house_number' => $request->house_number,
            'street_name' => $request->street_name,
            'locality' => $request->locality,
            'pincode' => $request->pincode,
            'area' => $request->area,
            'landmark' => $request->landmark,
            'complaint' => $request->complaint,
            'booking_datetime' => $request->booking_datetime,
            'alternate_datetime' => $request->alternate_datetime,
            'google_address' => $request->google_address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'paid_amount' => $request->paid_amount,
            'created_at' => now(),
        ];
        $booking = Booking::insert($booking);
        if($booking){
            return response()->json(['status' => true ,'message' => translate('Appointment Registered Successfully')], 200);
        }else {
            return response()->json(['status' => false ,'message' => translate('Something Went wrong')], 200);
        }
    }

    public function update_address(Request $request)
    {

        $shipping_address = ShippingAddress::where(['customer_id' => $request->user()->id, 'id' => $request->id])->first();
        if (!$shipping_address) {
            return response()->json(['message' => translate('not_found')], 200);
        }

        $zip_restrict_status = Helpers::get_business_settings('delivery_zip_code_area_restriction');
        $country_restrict_status = Helpers::get_business_settings('delivery_country_restriction');

        if ($country_restrict_status && !self::delivery_country_exist_check($request->input('country'))) {
            return response()->json(['message' => translate('Delivery_unavailable_for_this_country')], 403);

        } elseif ($zip_restrict_status && !self::delivery_zipcode_exist_check($request->input('zip'))) {
            return response()->json(['message' => translate('Delivery_unavailable_for_this_zip_code_area')], 403);
        }

        $shipping_address->update([
                'customer_id' => $request->user()->id,
                'contact_person_name' => $request->contact_person_name,
                'address_type' => $request->address_type,
                'address' => $request->address,
                'city' => $request->city,
                'zip' => $request->zip,
                'country' => $request->country,
                'phone' => $request->phone,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_billing' => $request->is_billing,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['message' => translate('update_successful')], 200);
    }

    public function delete_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (DB::table('shipping_addresses')->where(['id' => $request['address_id'], 'customer_id' => $request->user()->id])->first()) {
            DB::table('shipping_addresses')->where(['id' => $request['address_id'], 'customer_id' => $request->user()->id])->delete();
            return response()->json(['message' => 'successfully removed!'], 200);
        }
        return response()->json(['message' => translate('No such data found!')], 404);
    }

    public function get_order_list(Request $request)
    {
        $orders = Order::with('delivery_man')->where(['customer_id' => $request->user()->id])->get();
        $orders->map(function ($data) {
            $data['shipping_address_data'] = json_decode($data['shipping_address_data']);
            $data['billing_address_data'] = json_decode($data['billing_address_data']);
            return $data;
        });
        return response()->json($orders, 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $details = OrderDetail::with('seller.shop')
            ->whereHas('order',function($query) use($request){
                $query->where(['customer_id'=>$request->user()->id]);
            })
            ->where(['order_id' => $request['order_id']])
            ->get();
        $details->map(function ($query) {
            $query['variation'] = json_decode($query['variation'], true);
            $query['product_details'] = Helpers::product_data_formatting(json_decode($query['product_details'], true));
            return $query;
        });
        return response()->json($details, 200);
    }


    public function generate_invoice($id)
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
    public function reOrder(Request $request, $orderid)
    {
        $response['status'] = false;
        $response['message'] = 'something went wrong';

        $order = Order::with('seller')->with('shipping')->with('details')->where('id', $orderid)->first();

        $user = $request->user();
        $request['user_id'] = $user->id;

        $cart = null;
        $perviousMsg = null;
        if($order->details){
            foreach($order->details as $key=>$value){
                $request['id'] = $value->product_id;
                $request['quantity'] = $value->qty;
                $cart = CartManager::add_to_cart_reorder($request);
                if(!$cart['status']){
                    $perviousMsg = $cart['message'];
                }
            }
        }

        if($cart){
            $response['status'] = true;
            $response['message'] = ($perviousMsg) ? $perviousMsg : $cart['message'] ;

        }

        return response()->json($response);
        
    }
    public function get_order_by_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order = Order::where(['id' => $request['order_id']])->first();
        $order['shipping_address_data'] = json_decode($order['shipping_address_data']);
        $order['billing_address_data'] = json_decode($order['billing_address_data']);
        return response()->json($order, 200);
    }

    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'phone' => 'required',
        ], [
            'f_name.required' => translate('First name is required!'),
            'l_name.required' => translate('Last name is required!'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if ($request->has('image')) {
            $imageName = ImageManager::update('profile/', $request->user()->image, 'png', $request->file('image'));
        } else {
            $imageName = $request->user()->image;
        }

        if ($request['password'] != null && strlen($request['password']) > 5) {
            $pass = bcrypt($request['password']);
        } else {
            $pass = $request->user()->password;
        }

        $userDetails = [
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'zipcode' => $request->zipcode,
            'area' => $request->area,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'image' => $imageName,
            'password' => $pass,
            'updated_at' => now(),
        ];

        User::where(['id' => $request->user()->id])->update($userDetails);

        return response()->json(['message' => translate('successfully updated!')], 200);
    }

    public function update_cm_firebase_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cm_firebase_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        DB::table('users')->where('id', $request->user()->id)->update([
            'cm_firebase_token' => $request['cm_firebase_token'],
        ]);

        return response()->json(['message' => translate('successfully updated!')], 200);
    }

    public function get_restricted_country_list(Request $request)
    {
        $stored_countries = DeliveryCountryCode::orderBy('country_code', 'ASC')->pluck('country_code')->toArray();
        $country_list = COUNTRIES;

        $countries = array();

            foreach ($country_list as $country) {
                if (in_array($country['code'], $stored_countries))
                {
                    $countries []= $country['name'];
                }
            }

        if($request->search){
            $countries = array_values(preg_grep('~' . $request->search . '~i', $countries));
        }

        return response()->json($countries, 200);
    }

    public function get_restricted_zip_list(Request $request)
    {
        $zipcodes = DeliveryZipCode::orderBy('zipcode', 'ASC')
            ->when($request->search, function ($query) use($request){
                $query->where('zipcode', 'like', "%{$request->search}%");
            })
            ->get();

        return response()->json($zipcodes, 200);
    }
}
