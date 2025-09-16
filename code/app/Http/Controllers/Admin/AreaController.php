<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\State;
use App\Model\City;
use App\Model\Zipcode;
use App\Model\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Brian2694\Toastr\Facades\Toastr;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $categories = Zipcode::select('zipcode.*','cities.city','zipcode.created_at')
                ->leftJoin('cities','zipcode.city_id','=','cities.id')
                ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('zipcode', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }else{
            $categories = Zipcode::select('zipcode.*','cities.city','zipcode.created_at')
                            ->leftJoin('cities','zipcode.city_id','=','cities.id')
                            ->where('zipcode.id','!=',0);
        }

        $city = City::get();
        $categories = $categories->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.area.view', compact('categories','search','city'));
    }
    public function city(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $categories = City::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('city', 'like', "%{$value}%");
                    $q->orWhere('state', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }else{
            $categories = City::where(['state_id' => 0]);
        }

        $categories = $categories->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.area.cityview', compact('categories','search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'zipcode' => 'required|numeric|digits_between:5,6|unique:zipcode',
            // 'bonus' => 'required|numeric'
        ]);

        $category = new Zipcode;
        $category->city_id = $request->city_id;
        $category->zipcode = $request->zipcode;
        // $category->bonus = $request->bonus;
        $category->save();


        Toastr::success('Zipcode added successfully!');
        return back();
    }
    public function citystore(Request $request)
    {
        $request->validate([
            'city' => 'required|unique:cities,city',
            'state' => 'required|'
        ]);

        $category = new City;
        $category->state_id = 0;
        $category->city = ucfirst($request->city);
        $category->state = $request->state;
        $category->save();


        Toastr::success('City added successfully!');
        return back();
    }

    // public function edit(Request $request, $id)
    // {
    //     $category = Category::with('translations')->withoutGlobalScopes()->find($id);
    //     return view('admin-views.category.category-edit', compact('category'));
    // }

    public function update(Request $request)
    {
        $request->validate([
            'zipcode' => 'required|numeric|digits_between:5,6',
            // 'bonus' => 'required|numeric'
        ]);
        $category = Zipcode::find($request->id);
        $category->zipcode = $request->zipcode;
        $category->city_id = $request->city_id;
        // $category->bonus = $request->bonus;
        $category->save();

        Toastr::success('Zipcode updated successfully!');
        return back();
    }
    public function cityupdate(Request $request)
    {
        $request->validate([
            'city' => 'required',
        ]);
        $category = City::find($request->id);
        $category->city = $request->city;
        $category->save();

        Toastr::success('City updated successfully!');
        return back();
    }

    public function citydelete(Request $request)
    {
       
        City::destroy($request->id);

        return response()->json();
    }
    public function delete(Request $request)
    {
       
        Zipcode::destroy($request->id);

        return response()->json();
    }

    public function fetch(Request $request)
    {
        if ($request->ajax()) {
            $data = Category::where('position', 0)->orderBy('id', 'desc')->get();
            return response()->json($data);
        }
    }

    public function status(Request $request)
    {
        $category = Category::find($request->id);
        $category->home_status = $request->home_status;
        $category->save();
        // Toastr::success('Service status updated!');
        // return back();
        return response()->json([
            'success' => 1,
        ], 200);
    }

    
}
