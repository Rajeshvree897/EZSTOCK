<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventries extends Model
{
    protected $fillable = [
        'truck_id',
        'user_id',
         'quantity',
        'item_code',
        'item_name',
        'bin_id',
        'basePN',
        'brand_name',
        'description',
        'item_price',
        'setting',
        'customer_details',
        'fix_quantity',
        'item_image'
    ];

    public function trucks()
    {
        return $this->hasOne('App\Encompasses', 'id', 'truck_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
    public function order_inventory()
    {
        return $this->hasOne('App\Orders', 'order_basePN', 'basePN');
    }
}
