<?php

namespace App\Model;

use App\CPU\Helpers;
use App\Model\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class TimeSlot extends Model
{

    protected $table = 'time_slots';

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

}
