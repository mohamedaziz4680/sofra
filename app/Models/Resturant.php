<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Resturant extends Authenticatable 
{

    protected $table = 'resturants';
    public $timestamps = true;
    protected $fillable = array('name', 'email', 'phone', 'password', 'neighborhood_id', 'category_id', 'minmum_order', 'delivery_fees', 'contact_phone', 'whatsapp', 'pin_code', 'api_token', 'image');

    public function neighborhood()
    {
        return $this->belongsTo('App\Models\Neighborhood');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Item');
    }

    public function offers()
    {
        return $this->hasMany('App\Models\Offer');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    public function notifications()
    {
        return $this->morphMany('App\Models\Notification', 'notifiable');
    }

    public function tokens()
    {
        return $this->morphMany('App\Models\Token', 'tokenable');
    }

}