<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\CategoryManager;
use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\ServiceCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function get_categories()
    {
        try {
            $categories = Category::with(['childes.childes'])->where(['position' => 0])->priority()->get();
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
    public function get_servicecategories()
    {
        try {
            $categories = ServiceCategory::priority()->get();
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    // public function get_products(Request $request, $id)
    // {
    //     // dd($request->user());
    //     $city = $_GET['city'];
    //     $user = $request->user();
    //     // $zipcode = $user->zipcode;
    //     if(!empty($_GET['city'])){
    //         return response()->json(Helpers::product_data_formatting(CategoryManager::productsByCity($id,$city), true), 200);

    //     } else {
    //         return response()->json(Helpers::product_data_formatting(CategoryManager::products($id), true), 200);
    //     }
    // }

    // public function get_products(Request $request, $id)
    // {
    //     $city = $request->get('city');
    //     $user = $request->user();
        
    //     $minPriceInput = $request->get('min_price');
    //     $maxPriceInput = $request->get('max_price');
    
    //     if (!empty($city)) {
    //         $products = CategoryManager::productsByCity($id, $city);
    //     } else {
    //         $products = CategoryManager::products($id);
    //     }
    
    //     if (!is_null($minPriceInput)) {
    //         $products = $products->where('unit_price', '>=', $minPriceInput);
    //     }
    //     if (!is_null($maxPriceInput)) {
    //         $products = $products->where('unit_price', '<=', $maxPriceInput);
    //     }
    
    //     // $maxPrice = $products->max('unit_price'); 
    //     // $minPrice = $products->min('unit_price');
    
    //     return response()->json([
    //         'products' => Helpers::product_data_formatting($products, true),
    //         // 'maxPrice' => $maxPrice,
    //         // 'minPrice' => $minPrice,
    //     ], 200);
    // }

    public function get_products(Request $request, $id)
{
    $city = $request->get('city');
    $user = $request->user();
    
    $minPriceInput = $request->get('min_price');
    $maxPriceInput = $request->get('max_price');

    // Fetch products based on city or default to all products
    if (!empty($city)) {
        $products = CategoryManager::productsByCity($id, $city);
    } else {
        $products = CategoryManager::products($id);
    }

    // Apply price filters if provided
    if (!is_null($minPriceInput)) {
        $products = $products->where('unit_price', '>=', $minPriceInput);
    }
    if (!is_null($maxPriceInput)) {
        $products = $products->where('unit_price', '<=', $maxPriceInput);
    }

    return response()->json(Helpers::product_data_formatting($products, true), 200);
}

}
