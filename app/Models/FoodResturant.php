<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodResturant extends Model 
{

    protected $table = 'food_resturant';
    public $timestamps = true;
    protected $fillable = array('item_id', 'resturant_id');

}