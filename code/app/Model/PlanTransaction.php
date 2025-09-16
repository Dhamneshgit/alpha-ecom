<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PlanTransaction extends Model
{
    protected $table = 'plan_transactions';

    // protected $fillable = [
    //     'title','description','amount','discount_amount','days','level','status'
    // ];
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
