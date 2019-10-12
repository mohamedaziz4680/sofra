<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model 
{

    protected $table = 'orders';
    public $timestamps = true;
    protected $fillable = array('payment_method_id', 'total', 'delivery_cost', 'price', 'note', 'delivery_address', 'commission', 'client_id', 'net');

    public function resturant()
    {
        return $this->belongsTo('App\Models\Resturant');
    }

    public function items()
    {
        return $this->belongsToMany('App\Models\Item')->withPivot('price', 'quantity', 'sp_note');
    }

    public function paymentmethod()
    {
        return $this->belongsTo('App\Models\PaymentMethod');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

}