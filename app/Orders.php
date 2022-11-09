<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'address',
        'total',
        'item_details',
        'shipping_method',
        'order_status',
        'request_parameter',
        'response',
        'order_basePN',
        'type'
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function order_user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
    
}
