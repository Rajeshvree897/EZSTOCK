<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Default Encompass URL
    |--------------------------------------------------------------------------
    |
    | 
    |
    */

    'encompass_login_url' => env('ENCOMPASS_LOGIN_API', 'https://encompass.com/restfulservice/jsonWebItemInformation'),


    'encompass_order_create_api' => env('ENCOMPASS_ORDER_CREATE_API', 'https://encompass.com/restfulservice/createOrder'),


    'encompass_order_status_api' => env('ENCOMPASS_ORDER_STATUS_API', 'https://encompass.com/restfulservice/orderStatus'),

  

];
