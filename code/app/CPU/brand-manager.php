<?php

namespace App\CPU;

use App\Model\Brand;
use App\Model\Product;

class BrandManager
{
    public static function get_brands()
    {
        return Brand::withCount('brandProducts')->latest()->get();
    }

    public static function get_products($brand_id)
    {
        return Helpers::product_data_formatting(Product::active()->where(['brand_id' => $brand_id])->get(), true);
    }

    public static function get_active_brands(){
        return Brand::active()->withCount('brandProducts')->latest()->get();
    }

    public static function productsByCity($brand_id,$city)
    {
        $product = Product::active()->with(['seller.shop'])
                            ->where(['brand_id' => $brand_id])
                            ->whereHas('seller', function($query) use ($city) {
                                $query->where('city', $city);
                            })
                            ->get();
        return Helpers::product_data_formatting($product, true);
    }
}
