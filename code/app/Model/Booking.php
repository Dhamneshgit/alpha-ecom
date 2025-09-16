<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Booking extends Model
{
    protected $table = 'bookings';
    
    public function employee()
    {
        return $this->belongsTo(Admin::class, 'employee_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
