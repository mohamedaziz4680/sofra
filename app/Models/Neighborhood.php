<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model 
{

    protected $table = 'neighborhoods';
    public $timestamps = true;
    protected $fillable = array('name');

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function clients()
    {
        return $this->hasMany('App\Models\Client');
    }

    public function resturants()
    {
        return $this->hasMany('App\Models\Resturant');
    }

}