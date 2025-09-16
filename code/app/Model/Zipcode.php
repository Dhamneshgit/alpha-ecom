<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Zipcode extends Model
{
    protected $table = 'zipcode';

    protected $fillable = [
        'city_id','zipcode'
    ];
}
