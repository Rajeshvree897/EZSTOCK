<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification_histories extends Model
{
    protected $fillable = [
        'user_id',
        'notification_details'
    ];
}
