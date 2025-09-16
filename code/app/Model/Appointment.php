<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    // protected $casts = [
    //     'order_amount' => 'float',
    //     'discount_amount' => 'float',
    //     'customer_id' => 'integer',
    //     'shipping_address' => 'integer',
    //     'shipping_cost' => 'float',
    //     'created_at' => 'datetime',
    //     'updated_at' => 'datetime',
    //     'billing_address'=> 'integer',
    //     'extra_discount'=>'float',
    //     'delivery_man_id'=>'integer',
    //     'shipping_method_id'=>'integer',
    //     'seller_id'=>'integer'
    // ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assigned_to()
    {
        return $this->belongsTo(Admin::class,'assign_to');
    }

}
