<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PlanLevel extends Model
{
    protected $table = 'plan_levels';

    protected $fillable = [
        'plan_id','level','level_id','amount'
    ];
}
