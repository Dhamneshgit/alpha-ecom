<?php

namespace App\Http\Controllers\Web;

use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\ServiceCategory;
use App\Model\Service;
use App\Model\Brand;
use App\Model\TimeSlot;
use App\Model\Booking;
use App\Model\Seller;
// use App\Model\User;
use App\User;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\Review;
use App\Model\Translation;
use App\Model\Wishlist;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;
use Carbon\Carbon;

class ServiceListController extends Controller
{
    public function services_get(Request $request)
    {
        $theme_name = theme_root_path();

        return match ($theme_name) {
            'default' => self::default_theme($request),
            'theme_aster' => self::theme_aster($request),
            'theme_fashion' => self::theme_fashion($request),
            'theme_all_purpose' => self::theme_all_purpose($request),
        };
    }



    public function show($id)
    {
        $servicecategories = ServiceCategory::where('id', $id)->get();

        // $services = Service::where('category_id', $id)->get();

        // $reviews = Review::whereIn('service_id', $services->pluck('id'))->get();

        $services = Service::where('category_id', $id)
            ->where('services.status', 1)
            ->leftJoin('reviews', 'services.id', '=', 'reviews.service_id')
            ->select('services.*', DB::raw('COALESCE(AVG(reviews.rating), 0) as average_rating'))
            ->groupBy('services.id')
            ->get();






        // $overallRating = $this->calculateOverallRating($reviews);



        return view('web-views.services.serviceview', compact('servicecategories', 'services'));
    }

    public function showsingleservice($id)
    {
        $services = Service::where('id', $id)->get();
        $cat_id = Service::where('id', $id)->first()->category_id;

        $servicecategories = ServiceCategory::where('id', $cat_id)->get();



        $reviews = Review::where('service_id', $id)
            ->leftJoin('users', 'reviews.customer_id', '=', 'users.id')
            ->select('reviews.*', 'users.f_name', 'users.image as user_image', DB::raw('COUNT(reviews.id) as review_count'))
            ->groupBy('reviews.id', 'users.f_name', 'users.image')
            ->get();





        $overallRating = $this->calculateOverallRating($reviews);
        return view('web-views.services.singleserviceview', compact('servicecategories', 'services', 'overallRating', 'reviews'));
    }

    private function calculateOverallRating($reviews)
    {
        if ($reviews->count() > 0) {
            $totalRating = 0;
            foreach ($reviews as $review) {
                $totalRating += $review->rating;
            }
            return $totalRating / $reviews->count();
        }
        return 0;
    }
    
    
    
    public function bookingServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_datetime' => 'required',
            'booking_mobile' => 'required|min:10',
            'booking_name' => 'required',
            'booking_address' => 'required',
            'slot_id' => 'required',
            // 'paid_amount' => 'required',
            // 'latitude' => 'required|numeric',
            // 'longitude' => 'required|numeric',
            // 'booking_email' => 'required|email',
        ]);
        
        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 200);
        }

        $timeSlot = TimeSlot::find($request->slot_id);

        if (!$timeSlot) {
            return response()->json(['status' => false, 'message' => translate('TimeSlot not found')], 200);
        }

        $checkAvaibility = $this->checkAvaibility($request->booking_datetime, $request->service_id, $request->slot_id);

        if (!$checkAvaibility) {
            return response()->json(['status' => false, 'message' => 'Service is already booked on ' . $request->booking_datetime . ' & ' . $timeSlot->from_time], 200);
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
            'user_id' => Auth::guard('customer')->user()->id, //$request->user()->id Auth::user()->id
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
            'order_note' => $request->note,
            'booking_datetime' => $request->booking_datetime,
            'slot_id' => $request->slot_id,
            'booking_time' => $timeSlot->from_time,
            'till_time' => $timeSlot->to_time,
            'alternate_datetime' => $request->alternate_datetime ?? null,
            'google_address' => $request->booking_address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'paid_amount' => $request->unit_price ?? 0,
            'is_paid' => $request->is_paid ?? 0,
            'images' => (!empty($uploadedImages)) ? json_encode($uploadedImages) : NULL,
            'created_at' => now(),
        ];
        $booking = Booking::insert($booking);


        if ($booking) {
            $user = Seller::find($request->seller_id);
            // dd($user);
            if ($user && $user->cm_firebase_token) {
                $token = $user->cm_firebase_token;
                $title = 'Service Booking';
                $username = Auth::guard('customer')->user()->f_name . ' ' . Auth::guard('customer')->user()->l_name;
                $body = 'You have a booking for service by ' . $username;

                $data = []; //$friendData;
                Helpers::sendNotification($token, $title, $body, $data);
                Helpers::createNotification($title, $body, null, $request->seller_id, null);
            }
            Toastr::success('Service Booking Successfully');
            return response()->json(['status' => true, 'message' => translate('Service Booking Successfully')], 200);
        } else {
            Toastr::warning('Something Went wrong');
            return response()->json(['status' => false, 'message' => translate('Something Went wrong')], 200);
        }
    }


    public function checkAvaibility($date, $service_id, $slot_id)
    {

        $status = true;
        $check  = Booking::where('booking_datetime', $date)
            ->where('slot_id', $slot_id)
            ->where('service_id', $service_id)
            ->where('status', '!=', 4)
            ->first();
        if ($check) {
            $status = false;
        }

        return $status;
    }

    public function getTimeSlot(Request $request)
    {

        $service = Service::find($request->service_id);
        if ($service) {
            // @dd($service, $request->service_id);
            $timeslotes  = TimeSlot::where('service_id', $request->service_id)->get()->toArray();
            // dd($timeslotes);

            $data = [];
            if (!empty($timeslotes)) {

                foreach ($timeslotes as $key => $value) {
                    $checkAvaibility = $this->checkAvaibility($request->date, $request->service_id, $value['id']);

                    $from_time = Carbon::createFromFormat('H:i:s', $value['from_time'])->format('h:i A');
                    $to_time = Carbon::createFromFormat('H:i:s', $value['to_time'])->format('h:i A');

                    $current_time = Carbon::now()->format('Y-m-d H:i:s');
                    // $slot_start_time = Carbon::createFromFormat('H:i:s', $value['from_time'])->format('Y-m-d H:i');

                    if ($current_time < $request->date . ' ' . $value['from_time']) {
                        // Initialize the slot array
                        $slot = [];

                        $slot['slot_id'] = $value['id'];
                        $slot['from_time'] = $value['from_time'];
                        $slot['to_time'] = $value['to_time'];
                        $slot['time'] = $from_time . ' - ' . $to_time;
                        $slot['is_booked'] = ($checkAvaibility) ? 0 : 1;

                        // if ($slot['is_booked'] == 0) {
                        //     $data[] = $slot;
                        // }

                        // Add slot to data array outside the if block
                        $data[] = $slot;
                    }
                }
                return response()->json(['status' => true, 'message' => 'Successfully', 'data' => $data]);
            } else {
                return response()->json(['status' => false, 'message' => 'No Time Slots Available', 'data' => $data]);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Service Not found']);
        }
    }

    //     public function getTimeSlot(Request $request)
    // {
    //     $service = Service::find($request->service_id);

    //     if ($service) {
    //         $timeslotes = TimeSlot::where('service_id', $request->service_id)->get()->toArray();
    //         $data = [];

    //         $slot = [];
    //         if (!empty($timeslotes)) {

    //             foreach ($timeslotes as $key => $value) {
    //                 $checkAvaibility = $this->checkAvaibility($request->date, $request->service_id, $value['id']);

    //                 $slot['slot_id'] = $value['id'];

    //                 $slot['from_time'] = Carbon::createFromFormat('H:i:s', $value['from_time'])->format('h:i A');
    //                 $slot['to_time'] = Carbon::createFromFormat('H:i:s', $value['to_time'])->format('h:i A');

    //                 $class = 'defaultBg';
    //                 $disable = '';

    //                 if ($checkAvaibility) {
    //                     $class = 'booked';
    //                     $disable = 'disabled';
    //                 } else {
    //                     $class = 'available';
    //                     $disable = '';
    //                 }

    //                 $current_time = Carbon::now()->format('Y-m-d H:i');
    //                 $slot_start_time = Carbon::createFromFormat('H:i:s', $value['from_time'])->format('Y-m-d H:i');


    //                 if ($current_time > $slot_start_time) {
    //                     $class = 'past';
    //                     $disable = 'disabled';
    //                 }

    //                 $slot['is_booked'] = ($checkAvaibility) ? 1 : 0;
    //                 $slot['class'] = $class;
    //                 $slot['is_disabled'] = $disable;

    //                 $data[] = $slot;
    //             }

    //             return response()->json([
    //                 'status' => true, 
    //                 'message' => 'Successfully retrieved time slots', 
    //                 'data' => $data
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'status' => false, 
    //                 'message' => 'No Time Slots Available', 
    //                 'data' => []
    //             ]);
    //         }
    //     } else {
    //         return response()->json([
    //             'status' => false, 
    //             'message' => 'Service Not found'
    //         ]);
    //     }
    // }


}
