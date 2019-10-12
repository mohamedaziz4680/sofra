<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model 
{

    protected $table = 'offers';
    public $timestamps = true;
    protected $fillable = array('resturant_id', 'name', 'content', 'price', 'starting_at', 'ending_at');

    public function resturant()
    {
        return $this->belongsTo('App\Models\Resturant');
    }

}