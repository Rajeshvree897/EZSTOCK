<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Encompasses extends Model
{
    protected $fillable = [
        'user_id',
        'truckName',
        'bins',
        'category',
        'binName',
    ];

    public function users()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
    public function related_inventories()
    {
        return $this->hasMany('App\Inventries', 'truck_id', 'id');
    }

}
