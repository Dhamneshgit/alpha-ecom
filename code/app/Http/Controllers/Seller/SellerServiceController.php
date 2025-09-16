<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Service;
use App\Model\Review;
use App\Model\ServiceCategory;
use App\Model\Brand;
use App\Model\BusinessSetting;
use App\Model\Seller;
use App\CPU\Helpers;
use App\Model\TimeSlot;
use Illuminate\Support\Str;
use App\CPU\ImageManager;
use App\CPU\Convert;
use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;


class SellerServiceController extends Controller
{

    function list(Request $request, $type)
    {
        $user_id = auth()->guard('seller')->user()->id;


        // dd(auth()->guard('seller')->user()->id);

        $query_param = [];
        $search = $request['search'];
        if ($type == 'in_house') {
            $pro = Service::with('seller', 'category', 'booking')->withCount('booking')->where(['added_by' => 'seller'])->where(['user_id' => $user_id]);
        } else {
            $pro = Service::with('seller', 'category', 'booking')->withCount('booking')->where(['added_by' => 'seller'])->where('request_status', $request->status)->where(['user_id' => $user_id]);
        }

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $pro = $pro->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('name', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }

        $request_status = $request['status'];
        $pro = $pro->orderBy('id', 'DESC')->paginate(Helpers::pagination_limit())->appends(['status' => $request['status']])->appends($query_param);
        // dd($pro);
        return view('seller-views.system.service-list-view', compact('pro', 'search', 'request_status', 'type'));
    }



    // category_list
    public function add_new()
    {
        // $cat = Category::where(['parent_id' => 0])->get();
        $categories = ServiceCategory::all();
        $br = Brand::orderBY('name', 'ASC')->get();
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;
        $digital_product_setting = BusinessSetting::where('type', 'digital_product')->first()->value;
        return view('seller-views.system.service-add-new', compact('categories', 'br', 'brand_setting', 'digital_product_setting'));
    }
    public function edit_service_view($id)
    {
        $service = Service::find($id);
        $categories = ServiceCategory::all();
        if (!$service) {
            return redirect()->back()->withErrors('Service not found.');
        }

        return view('seller-views.system.edit-service-view', compact('service', 'categories'));
    }




    public function add_new_service(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
            'images' => 'required|array|min:1',
            'thumbnail' => 'required|image',
            'discount_type' => 'required|in:percent,flat',
            'tax' => 'required|min:0',
            'tax_model' => 'required',
            // 'lang' => 'required',
            'unit_price' => 'required|min:1',
            'discount' => 'required|gt:-1',
        ], [
            'name.required' => translate('Product name is required!'),
            'category_id.required' => translate('Category is required!'),
            'images.required' => translate('Product images are required!'),
            'images.array' => translate('Product images must be an array!'),
            'images.min' => translate('At least one image is required!'),
            'thumbnail.required' => translate('Product thumbnail is required!'),
            'thumbnail.image' => translate('Thumbnail must be an image file!'),
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                Toastr::error($error);
            }
            return back()->withInput();
        }

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['unit_price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['unit_price'] <= $dis) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'unit_price',
                    translate('Discount cannot be more or equal to the price!')
                );
            });
            return back()->withErrors($validator)->withInput();
        }

        $product = new Service();
        $product->user_id = auth()->guard('seller')->user()->id;
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
                $check = $this->checkTime($product->id, $request['from_time'][$i], $request['to_time'][$i]);
                if ($check) {
                    $time = new TimeSlot();
                    $time->service_id = $product->id;
                    $time->seller_id = auth()->guard('seller')->user()->id;
                    $time->from_time = $request['from_time'][$i];
                    $time->to_time = $request['to_time'][$i];
                    $time->save();
                }
                $i++;
            }
        }

        // Success message
        Toastr::success('Service added successfully!');
        return redirect()->route('seller.service.list', ['in_house', '']);
    }


    public function checkTime($service_id, $from_time, $to_time)
    {
        $status = true;
        $check = TimeSlot::where('service_id', $service_id)
            ->where('from_time', $from_time)
            ->where('to_time', $to_time)
            ->first();
        if ($check) {
            $status = false;
        }
        return $status;
    }


    // to view a service details on seller service list
    public function single_service_view($id)
    {
        $product = Service::with('seller', 'category', 'booking', 'reviews')->where(['id' => $id])->first();

        $timeslot = TimeSlot::where('service_id', $id)->get();

        $reviews = Review::where(['service_id' => $id])->paginate(Helpers::pagination_limit());

        return view('seller-views.system.single-service-view', compact('product', 'reviews', 'timeslot'));
    }



    // edit/update services 
    public function update_service(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
            'images' => 'nullable|array|min:1',
            'thumbnail' => 'nullable|image',
            'discount_type' => 'required|in:percent,flat',
            'tax' => 'required|min:0',
            'tax_model' => 'required',
            'unit_price' => 'required|min:1',
            'discount' => 'required|gt:-1',
        ], [
            'name.required' => translate('Product name is required!'),
            'category_id.required' => translate('Category is required!'),
            'images.array' => translate('Product images must be an array!'),
            'images.min' => translate('At least one image is required if provided!'),
            'thumbnail.image' => translate('Thumbnail must be an image file!'),
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                Toastr::error($error);
            }
            return back()->withInput();
        }

        $service = Service::find($id);
        if (!$service) {
            Toastr::error(translate('Service not found!'));
            return back();
        }

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['unit_price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['unit_price'] <= $dis) {
            $validator->after(function ($validator) {
                $validator->errors()->add(
                    'unit_price',
                    translate('Discount cannot be more or equal to the price!')
                );
            });
            return back()->withErrors($validator)->withInput();
        }

        $service->name = $request->name;
        $service->slug = Str::slug($request->name, '-') . '-' . Str::random(6);
        $service->category_id = $request->category_id;
        $service->details = $request->description;

        if ($request->hasFile('images')) {
            $uploadedImages = [];
            foreach ($request->file('images') as $image) {
                $path = ImageManager::upload('product/', 'png', $image);
                $uploadedImages[] = $path;
            }
            $service->images = json_encode($uploadedImages);
        }

        if ($request->hasFile('thumbnail')) {
            $service->thumbnail = ImageManager::upload('product/', 'png', $request->file('thumbnail'));
        }

        $service->unit_price = Convert::usd($request->unit_price);
        $service->tax = $request->tax;
        $service->tax_type = $request->tax_type;
        $service->tax_model = $request->tax_model;
        $service->discount = $request->discount_type == 'flat' ? Convert::usd($request->discount) : $request->discount;
        $service->discount_type = $request->discount_type;

        $service->video_provider = 'youtube';
        $service->video_url = $request->video_link;
        $service->shipping_cost = isset($request->shipping_cost) ? Convert::usd($request->shipping_cost) : 0;

        $service->save();

        $timeSlots = isset($request['from_time']) ? count($request['from_time']) : null;
        if (!empty($request['from_time'][0])) {
            TimeSlot::where('service_id', $service->id)->delete(); // Clear existing slots
            for ($i = 0; $i < $timeSlots; $i++) {
                $check = $this->checkTime($service->id, $request['from_time'][$i], $request['to_time'][$i]);
                if ($check) {
                    $time = new TimeSlot();
                    $time->service_id = $service->id;
                    $time->seller_id = auth()->guard('seller')->user()->id;
                    $time->from_time = $request['from_time'][$i];
                    $time->to_time = $request['to_time'][$i];
                    $time->save();
                }
            }
        }

        Toastr::success('Service updated successfully!');
        return redirect()->route('seller.service.list', ['in_house', '']);
    }





    // for service category start from here 
    public function category_list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $categories = ServiceCategory::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $categories = ServiceCategory::where(['position' => 0]);
        }

        $categories = $categories->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('seller-views.system.service-categorylist-view', compact('categories', 'search'));
    }

    // service category end here 


    // timeslots crud starts here 
    public function storeTimeSlotes(Request $request)
    {

        $timeSlotes = isset($request['from_time']) ? count($request['from_time']) : null;
        if ($timeSlotes == 0) {
            Toastr::warning('Invalid time Slotes!');
            return redirect()->route('admin.service.view', [$request->service_id]);
        }

        $check = $this->checkTime($request->service_id, $request['from_time'][0], $request['to_time'][0]);
        if (!$check) {
            Toastr::warning($request['from_time'][0] . ' to ' . $request['to_time'][0] . ' slot is already available');
            return redirect()->route('admin.service.view', [$request->service_id]);
        }
        if (!empty($request['from_time'][0])) {
            for ($i = 0; $i < $timeSlotes;) {

                $check = $this->checkTime($request->service_id, $request['from_time'][$i], $request['to_time'][$i]);
                if ($check) {
                    $time = new TimeSlot();
                    $time->service_id = $request->service_id;
                    $time->seller_id = 1;
                    $time->from_time = $request['from_time'][$i];
                    $time->to_time = $request['to_time'][$i];
                    $time->save();
                }
                $i++;
            }
        }

        Toastr::success('Service TimeSlotes Add successfully.');
        return redirect()->route('seller.service.single-service-view',[$request->service_id]);
    }

    public function updateTimeSlotes(Request $request)
    {

        $timeSlote =  TimeSlot::find($request->slot_id);

        if ($timeSlote) {
            $timeSlote->from_time = $request['from_time'];
            $timeSlote->to_time = $request['to_time'];
            $timeSlote->save();

            Toastr::success('Service time slots update successfully !');
            
            return redirect()->route('seller.service.single-service-view',[$request->service_id]);
        } else {
            Toastr::warning('Invalid time Slotes!');
            return redirect()->route('seller.service.single-service-view',[$request->service_id]);
        }
    }

    public function slotdelete(Request $request, $id)
    {

        $timeSlote =  TimeSlot::find($id);

        if ($timeSlote) {
            $timeSlote->delete();

            Toastr::success('Service time slots delete successfully !');
            return redirect()->back();
        } else {

            Toastr::warning('Invalid time Slotes!');
            return redirect()->back();
        }
    }
}
