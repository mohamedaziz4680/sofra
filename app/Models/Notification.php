<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model 
{

    protected $table = 'notifications';
    public $timestamps = true;
    protected $fillable = array('title', 'content', 'order_id', 'notifiable_id', 'notifiable_type', 'action');

    public function notifiable()
    {
        return $this->morphTo();
    }

}