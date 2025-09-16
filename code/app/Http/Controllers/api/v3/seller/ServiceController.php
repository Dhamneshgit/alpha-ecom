<?php

namespace App\Http\Controllers\api\v3\seller;

use App\CPU\Convert;
use App\CPU\Helpers;
use App\CPU\ProductManager;
use App\Model\BusinessSetting;
use App\Model\Color;
use App\Model\DeliveryMan;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\Service;
use App\Model\TimeSlot;
use App\Model\ServiceCategory;
use App\Model\Review;
use App\Model\Booking;
use App\Model\Seller;
use App\Model\Tag;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;
use App\CPU\ImageManager;
use App\Model\Translation;
use App\Model\DealOfTheDay;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Model\FlashDealProduct;
use function App\CPU\translate;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\PDF;

class ServiceController extends Controller
{
    public function list(Request $request)
    {
        $seller = $request->seller;
        $products = Service::with('time_slots','rating','reviews.customer')->where(['added_by' => 'seller', 'user_id' => $seller['id']])
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json($products, 200);
    }
    public function myBooking(Request $request)
    {
        $seller = $request->seller;
        $products = Booking::with('user','service')->where('seller_id', $seller['id'])
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['status' => true, 'data' => $products], 200);
    }

    public function details(Request $request, $id)
    {
        $seller = $request->seller;
        $product = Product::where(['added_by' => 'seller', 'user_id' => $seller->id])->find($id);

        if (isset($product)) {
            $product = Helpers::product_data_formatting($product, false);
        }
        return response()->json($product, 200);
    }

    public function stock_out_list(Request $request)
    {
        $seller = $request->seller;
        $stock_limit = Helpers::get_business_settings('stock_limit');

        $products = Product::where(['added_by' => 'seller', 'user_id' => $seller->id, 'product_type' => 'physical', 'request_status' => 1])
            ->where('current_stock', '<', $stock_limit)
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $products->map(function ($data) {
            $data = Helpers::product_data_formatting($data);
            return $data;
        });

        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $products->items()
        ], 200);
    }

    public function upload_images(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
            'type' => 'required|in:product,thumbnail,meta',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $path = $request['type'] == 'product' ? '' : $request['type'] . '/';
        $image = ImageManager::upload('product/' . $path, 'png', $request->file('image'));

        if ($request['colors_active']=="true") {
            $color_image = array(
                "color" => !empty($request['color']) ? str_replace('#','',$request['color']) : null,
                "image_name" => $image,
            );
        }else{
            $color_image = null;
        }

        return response()->json([
            'image_name' => $image,
            'type' => $request['type'],
            'color_image' => $color_image
        ], 200);
    }

    // Digital product file upload
    public function upload_digital_product(Request $request)
    {
        $seller = $request->seller;

        try {
            $validator = Validator::make($request->all(), [
                'digital_file_ready' => 'required|mimes:jpg,jpeg,png,gif,zip,pdf',
            ]);

            if ($validator->errors()->count() > 0) {
                return response()->json(['errors' => Helpers::error_processor($validator)], 403);
            }

            $file = ImageManager::upload('product/digital-product/', $request->digital_file_ready->getClientOriginalExtension(), $request->file('digital_file_ready'));

            return response()->json(['digital_file_ready_name' => $file], 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function add_new(Request $request)
    {
        $seller = $request->seller;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
            // 'unit' => 'required_if:product_type,==,physical',
            'images' => 'required',
            'thumbnail' => 'required',
            'discount_type' => 'required|in:percent,flat',
            'tax' => 'required|min:0',
            'tax_model' => 'required',
            'lang' => 'required',
            'unit_price' => 'required|min:1',
            'discount' => 'required|gt:-1',
            
        ], [
            'name.required' => translate('Product name is required!'),
            'category_id.required' => translate('category is required!'),
            'images.required' => translate('Product images is required!'),
            'image.required' => translate('Product thumbnail is required!'),
           
        ]);

        
        if ($request['discount_type'] == 'percent') {
            $dis = ($request['unit_price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['unit_price'] <= $dis) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'unit_price',
                    translate('Discount can not be more or equal to the price!')
                );
            });
        }

        $product = new Service();
        $product->user_id = $seller->id;
        $product->added_by = "seller";

        $product->name = $request->name;
        $product->slug = Str::slug($request->name, '-') . '-' . Str::random(6);

        $uploadedImages = []; 
        if ($request->hasFile('images')) {
        
            foreach ($request->file('images') as $image) {
                $path = ImageManager::upload('product/', 'png', $image);
                $uploadedImages[] = $path; 
            }
        }    
     

        $product->category_ids = NULL;
        $product->category_id  = $request->category_id;
       
        $product->details = $request->description;

        $product->images = (!empty($uploadedImages)) ? json_encode($uploadedImages) : NULL;
        $product->thumbnail = ImageManager::upload('product/', 'png', $request->file('thumbnail'));
        
        $product->unit_price = Convert::usd($request->unit_price);
        $product->tax = $request->tax;
        $product->tax_type = $request->tax_type;
        $product->tax_model = $request->tax_model;
        $product->discount = $request->discount_type == 'flat' ? Convert::usd($request->discount) : $request->discount;
        $product->discount_type = $request->discount_type;
       

        $product->video_provider = 'youtube';
        $product->video_url = $request->video_link;
        $product->status = 0;
        $product->shipping_cost = isset($request->shipping_cost) ? Convert::usd($request->shipping_cost) : 0;
        $product->save();

        $timeSlotes = isset($request['from_time']) ? count($request['from_time']) : null;
        if (!empty($request['from_time'][0])) {
            for ($i = 0; $i < $timeSlotes;) {
                
                $check = $this->checkTime($product->id,$request['from_time'][$i],$request['to_time'][$i]);
                if($check){
                    $time = new TimeSlot();
                    $time->service_id = $product->id;
                    $time->seller_id = $seller->id;
                    $time->from_time = $request['from_time'][$i];
                    $time->to_time = $request['to_time'][$i];
                    $time->save();
                }
                $i++;
            }
        }


        return response()->json(['status'=> true, 'message' => translate('Service added successfully!')]);
    }

    public function addTimeSlots(Request $request){

        $seller = $request->seller;
        $timeSlotes = isset($request['from_time']) ? count($request['from_time']) : null;
        if($timeSlotes == 0){
            return response()->json(['status'=> false, 'message' => translate('Invalid time Slotes')]);
        }

        $check = $this->checkTime($request->service_id,$request['from_time'][0],$request['to_time'][0]);
        if(!$check){
            return response()->json(['status'=> false, 'message' => $request['from_time'][0].' to '.$request['to_time'][0].' slot is already available' ]);
        }
        if (!empty($request['from_time'][0])) {
            for ($i = 0; $i < $timeSlotes;) {
                
                $check = $this->checkTime($request->service_id,$request['from_time'][$i],$request['to_time'][$i]);
                if($check){
                    $time = new TimeSlot();
                    $time->service_id = $request->service_id;
                    $time->seller_id = $seller->id;
                    $time->from_time = $request['from_time'][$i];
                    $time->to_time = $request['to_time'][$i];
                    $time->save();
                }
                $i++;
            }
        }  
        
        return response()->json(['status'=> true, 'message' => translate('Service time slots added successfully !')]);
    }
    public function updateTimeSlots(Request $request){

        $seller = $request->seller;
        $timeSlote =  TimeSlot::find($request->slot_id);

        if($timeSlote){
            $timeSlote->from_time = $request['from_time'];
            $timeSlote->to_time = $request['to_time'];
            $timeSlote->save();

            return response()->json(['status'=> true, 'message' => translate('Service time slots update successfully !')]);
        } else {
            return response()->json(['status'=> false, 'message' => translate('Something went wrong!')]);
        }
    }
    public function updateBookingStatus(Request $request){

        $seller = $request->seller;
        $bookingId = $request->booking_id;
        $status = $request->status;

        $booking = Booking::find($bookingId);

        if($booking){
            $booking->status = $status;
            $booking->save();

            $user = User::find($booking->user_id);
            if($user->cm_firebase_token){
                $token = $user->cm_firebase_token; 
                $statusm = ($request->status == 1) ? 'Accepted' : 'Rejected' ;
                $sellername = $seller->f_name.' '.$seller->l_name.'('.$seller->unique_code.')';
                $title = 'Service Booking';
                $body = 'Booking Id: '.$bookingId.' is '.$statusm.' by '.$sellername;
                
                $data = []; //$friendData;
                Helpers::sendNotification($token, $title, $body, $data);
                Helpers::createNotification($title,$body,$booking->user_id,null,null);
            }

            return response()->json(['status'=> true, 'message' => translate('Service Status update successfully !')]);
        } else {
            return response()->json(['status'=> false, 'message' => translate('Something went wrong!')]);
        }
    }
    public function deleteTimeSlots(Request $request, $id){

        $seller = $request->seller;
        $timeSlote =  TimeSlot::find($id);

        if($timeSlote){
            $timeSlote->delete();

            return response()->json(['status'=> true, 'message' => translate('Service time slots Deleted successfully !')]);
        } else {
            return response()->json(['status'=> false, 'message' => translate('Something went wrong!')]);
        }
    }
    public function checkTime($service_id,$from_time,$to_time){
        $status = true;
        $check = TimeSlot::where('service_id',$service_id)
                        ->where('from_time',$from_time)
                        ->where('to_time',$to_time)
                        ->first();
        if($check){
            $status = false;
        }       
        return $status;         
    }
    
    public function edit(Request $request, $id)
    {
        $product = Product::withoutGlobalScopes()->with('translations','tags')->find($id);
        $product = Helpers::product_data_formatting($product);

        return response()->json($product, 200);
    }

    public function update(Request $request, $id)
    {
        $seller = $request->seller;
        $product = Service::find($id);

        $validator = Validator::make($request->all(), [
            'service_id' => 'required',
            'name' => 'required',
            'category_id' => 'required',
            // 'unit' => 'required_if:product_type,==,physical',
            'images' => 'required',
            'thumbnail' => 'required',
            'discount_type' => 'required|in:percent,flat',
            'tax' => 'required|min:0',
            'tax_model' => 'required',
            'lang' => 'required',
            'unit_price' => 'required|min:1',
            'discount' => 'required|gt:-1',
            
        ], [
            'name.required' => translate('Service name is required!'),
            'service_id.required' => translate('Service Id is required!'),
            'category_id.required' => translate('category is required!'),
            'images.required' => translate('Service images is required!'),
            'image.required' => translate('Service thumbnail is required!'),
           
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['unit_price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['unit_price'] <= $dis) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'unit_price',
                    translate('Discount can not be more or equal to the price!')
                );
            });
        }

        $product = Service::find($request->service_id);
        $product->user_id = $seller->id;
        $product->added_by = "seller";

        $product->name = $request->name;
        $product->slug = Str::slug($request->name, '-') . '-' . Str::random(6);

        $uploadedImages = []; 
        if ($request->hasFile('images')) {
        
            foreach ($request->file('images') as $image) {
                $path = ImageManager::upload('product/', 'png', $image);
                $uploadedImages[] = $path; 
            }
        }    
     

        $product->category_ids = NULL;
        $product->category_id  = $request->category_id;
       
        $product->details = $request->description;

        $product->images = (!empty($uploadedImages)) ? json_encode($uploadedImages) : $product->images;
        $product->thumbnail = $request->hasFile('thumbnail') ? ImageManager::upload('product/', 'png', $request->file('thumbnail')) : $product->thumbnail;
        
        $product->unit_price = Convert::usd($request->unit_price);
        $product->tax = $request->tax;
        $product->tax_type = $request->tax_type;
        $product->tax_model = $request->tax_model;
        $product->discount = $request->discount_type == 'flat' ? Convert::usd($request->discount) : $request->discount;
        $product->discount_type = $request->discount_type;
       

        $product->video_provider = 'youtube';
        $product->video_url = $request->video_link;
        $product->status = 0;
        $product->shipping_cost = isset($request->shipping_cost) ? Convert::usd($request->shipping_cost) : 0;
        $product->save();


        return response()->json(['status'=> true, 'message' => translate('successfully service updated!')], );
    }

    public function product_quantity_update(Request $request)
    {
        $product = Product::find($request->product_id);
        $product->current_stock = $request->current_stock;
        $product->variation = $request->variation;
        if ($product->save()) {
            return response()->json(['message' => translate('successfully product updated!')], 200);
        }
        return response()->json(['message' => translate('update fail!')], 403);
    }

    public function status_update(Request $request)
    {
        $seller = $request->seller;
        $product = Product::where(['added_by' => 'seller', 'user_id' => $seller->id])->find($request->id);
        if (!$product) {
            return response()->json(['message' => translate('invalid_prodcut')], 403);
        }
        $product->status = $request->status;
        $product->save();

        return response()->json([
            'success' => translate('status_update_successfully'),
        ], 200);
    }
    public function serviceCategory()
    {
        try {
            $categories = ServiceCategory::priority()->get();
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    public function delete(Request $request, $id)
    {

        $product = Service::find($id);
        if($product) {
            foreach (json_decode($product['images'], true) as $image) {
                ImageManager::delete('/product/' . $image);
            }
            ImageManager::delete('/product/thumbnail/' . $product['thumbnail']);
            $product->delete();
            return response()->json(['status'=> true, 'message' => translate('successfully Service deleted!')], 200);
        }
        return response()->json(['status'=> false, 'message' => translate('Service not available!')], 200);
        // FlashDealProduct::where(['product_id' => $id])->delete();
        // DealOfTheDay::where(['product_id' => $id])->delete();
    }

    public function barcode_generate(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'quantity' => 'required',
        ], [
            'id.required' => 'Product ID is required',
            'quantity.required' => 'Barcode quantity is required',
        ]);

        if ($request->limit > 270) {
            return response()->json(['code' => 403, 'message' => 'You can not generate more than 270 barcode']);
        }
        $product = Product::where('id', $request->id)->first();
        $quantity = $request->quantity ?? 30;
        if (isset($product->code)) {
            $pdf = app()->make(PDF::class);
            $pdf->loadView('seller-views.product.barcode-pdf', compact('product', 'quantity'));
            $pdf->save(storage_path('app/public/product/barcode.pdf'));
            return response()->json(asset('storage/app/public/product/barcode.pdf'));
        } else {
            return response()->json(['message' => translate('Please update product code!')], 203);
        }

    }

    public function top_selling_products(Request $request)
    {
        $seller = $request->seller;

        $orders = OrderDetail::with('product.rating')
            ->select('product_id', DB::raw('SUM(qty) as count'))
            ->where(['seller_id' => $seller['id'], 'delivery_status' => 'delivered'])
            ->whereHas('product', function ($query) {
                $query->where(['added_by' => 'seller']);
            })
            ->groupBy('product_id')
            ->orderBy("count", 'desc')
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $orders_final = $orders->map(function ($order) {
            $order['product'] = Helpers::product_data_formatting($order['product'], false);
            return $order;
        });

        $data = array(
            'total_size' => $orders->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'products' => $orders_final,
        );

        return response()->json($data, 200);
    }

    public function most_popular_products(Request $request)
    {
        $seller = $request->seller;
        $products = Product::with(['rating','tags'])
            ->whereHas('reviews', function ($query) {
                return $query;
            })
            ->where(['user_id' => $seller['id'], 'added_by' => 'seller'])
            ->withCount(['reviews'])->orderBy('reviews_count', 'DESC')
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $products_final = Helpers::product_data_formatting($products, true);

        $data = array(
            'total_size' => $products->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'products' => $products_final
        );
        return response()->json($data, 200);
    }

    public function top_delivery_man(Request $request)
    {
        $seller = $request->seller;
        $delivery_men = DeliveryMan::with(['rating', 'orders' => function ($query) {
                $query->select('delivery_man_id', DB::raw('COUNT(delivery_man_id) as count'));
            }])
            ->whereHas('orders', function($query){
                $query->where('order_status','delivered');
            })
            ->where(['seller_id' => $seller['id']])
            ->when(!empty($request['search']), function ($query) use ($request) {
                $key = explode(' ', $request['search']);
                foreach ($key as $value) {
                    $query->where('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%");
                }
            })
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $data = array();
        $data['total_size'] = $delivery_men->total();
        $data['limit'] = $request['limit'];
        $data['offset'] = $request['offset'];
        $data['delivery_man'] = $delivery_men->items();
        return response()->json($data, 200);
    }

    public function review_list(Request $request, $product_id)
    {
        $product = Product::find($product_id);
        $average_rating = count($product->rating) > 0 ? number_format($product->rating[0]->average, 2, '.', ' ') : 0;
        $reviews = Review::with(['customer', 'product'])->where(['product_id' => $product_id])
            ->latest('updated_at')
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $rating_group_count = Review::where(['product_id' => $product_id])
            ->select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->get();

        $data = array();
        $data['total_size'] = $reviews->total();
        $data['limit'] = $request['limit'];
        $data['offset'] = $request['offset'];
        $data['group-wise-rating'] = $rating_group_count;
        $data['average_rating'] = $average_rating;
        $data['reviews'] = $reviews->items();

        return response()->json($data, 200);
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

    public function rescheduleService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_date' => 'required',
            'booking_id' => 'required',
            'slot_id' => 'required'
        ], [
            'booking_date.required' => 'Booking Date is required!',
            'booking_id.required' => 'Booking Id is Required',
            'slot_id.required' => 'Booking Time Slot is Required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'errors' => Helpers::error_processor($validator)], 403);
        }

        $timeSlot = TimeSlot::find($request->slot_id);

        if(!$timeSlot){
            return response()->json(['status' => false ,'message' => translate('TimeSlot not found')], 200);
        }

        $booking = Booking::find($request->booking_id);
        
        if(!$timeSlot){
            return response()->json(['status' => false ,'message' => translate('Booking not found')], 200);
        }

        $checkAvaibility = $this->checkAvaibility($request->booking_date, $booking->service_id, $request->slot_id);

        if(!$checkAvaibility){
            return response()->json(['status' => false ,'message' => 'Service is booked on '.$request->booking_date.' & '.$timeSlot->from_time], 200);
        }

        $booking->booking_datetime = $request->booking_date;
        $booking->slot_id = $request->slot_id;
        $booking->booking_time = $timeSlot->from_time;
        // $booking->status = 3;
        $booking->reschedule_user_status = 1;
        $booking->save();

        $user = User::find($booking->user_id);
        if($user->cm_firebase_token){
            $token = $user->cm_firebase_token; 
            $title = 'Reschedule Booking';
            $body = 'Seller has your reschedule booking id :'.$request->booking_id;
            
            $data = []; //$friendData;
            Helpers::sendNotification($token, $title, $body, $data);
            Helpers::createNotification($title,$body,$booking->user_id,null,null);
        }

        return response()->json(['status' => true ,'message' => translate('Service Re-schedule Successfully, waiting for user approval')], 200);
        
    }
}
