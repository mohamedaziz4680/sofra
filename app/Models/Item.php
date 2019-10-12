<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model 
{

    protected $table = 'items';
    public $timestamps = true;
    protected $fillable = array('image', 'name', 'description', 'price', 'price_in_offer', 'time_to_ready', 'resturant_id');

    public function resturants()
    {
        return $this->belongsTo('App\Models\Resturant');
    }

    public function orders()
    {
        return $this->belongsToMany('App\Models\Order');
    }

}