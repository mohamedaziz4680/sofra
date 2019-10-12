<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemOrder extends Model 
{

    protected $table = 'item-order';
    public $timestamps = true;
    protected $fillable = array('order_id', 'item_id', 'quantity', 'price', 'sp_note');

}