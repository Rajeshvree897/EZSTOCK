<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::view('/first', 'first');
Route::post('encompass/login', 'LoginController@login')->name('encompass/login');

Route::post('encompass/create', 'EnncompassController@encompass_create')->name('encompass/create');

Route::post('encompass/update/{id}', 'EnncompassController@encompass_update')->name('encompass/update/');

Route::get('encompass/delete/{id}', 'EnncompassController@encompass_delete')->name('encompass/delete/');
Route::post('encompass/bin/delete/{id}', 'EnncompassController@delete_bin')->name('encompass/bin/delete/');
Route::get('encompass/get/{id}', 'EnncompassController@encompass_get')->name('encompass/get/');
//inventry api

Route::post('inventry/create/{id}', 'InventryController@inventry_create')->name('inventry/create');
Route::post('inventry/transfer/{id}', 'InventryController@inventry_transfer')->name('inventry/transfer');

//for get single inventry
Route::get('inventry/get/{id}', 'InventryController@inventry_get')->name('inventry/get');

Route::post('inventry/update/{id}', 'InventryController@inventry_update')->name('inventry/update');

Route::post('inventry/manual/update/{id}', 'InventryController@inventry_manual_update')->name('inventry/manual/update');


//for get multiple inventry
Route::post('inventry/gets/{id}', 'InventryController@inventry_gets')->name('inventry/gets');
Route::get('inventry/count/{id}', 'InventryController@inventry_count')->name('inventry/count');

Route::get('inventry/low/{id}', 'InventryController@low_inventry')->name('inventry/low');


Route::get('inventry/delete/{id}', 'InventryController@inventry_delete')->name('inventry/delete');

Route::post('inventry/search/{id}', 'InventryController@search_inventry')->name('inventry/search');



Route::post('order/checkout/{id}', 'OrderController@checkout')->name('order/checkout');

Route::post('order/status/', 'OrderController@status')->name('order/status');

 Route::post('order/nearby/{id}', 'InventryController@order_near_by')->name('order/nearby');


Route::get('order/history/{id}', 'OrderController@orders')->name('order/history');

Route::post('inventry/setting/specific/{id}', 'InventryController@specific_setting')->name('inventry/setting/specific');

Route::post('inventry/setting/global/{id}', 'InventryController@global_setting')->name('inventry/setting/global');

Route::post('profile/update/', 'UserController@profile_update')->name('profile/update');
 //
Route::get('user/info/{id}', 'UserController@user_info')->name('user/info');

Route::post('user/update/location/{id}', 'UserController@update_location')->name('user/update/location');
Route::post('user/save/fcm_key/{id}', 'UserController@save_fcm_key')->name('user/save/fcm_key');

Route::post('user/google/notification/{id}', 'UserController@google_notification')->name('user/google/notification');

Route::get('user/notification/history/{id}', 'UserController@notification_history')->name('user/notification/history');

//

Route::post('notification/nearby/user', 'UserController@notification_nearby_user')->name('notification/nearby/user');

Route::get('truck/summary/{id}', 'EnncompassController@truck_summary')->name('truck/summary');


Route::get('truck/inventory/CSV/get', 'InventryController@get_truck_inventory_CSV')->name('truck/inventory/CSV/get');


Route::get('truck/inventory/transation/get', 'InventryController@get_truck_inventory_transation')->name('truck/inventory/transation/get');

//AutoReplanishment

Route::get('order/autoreplanishment', 'AutoReplanishmentController@autoReplanishment')->name('order/autoreplanishment');

//for all brand name with code 

Route::get('item/brands', 'OrderController@orderBrands')->name('item/brands');

Route::post('weekly/report/{id}', 'OrderController@weeklyReport')->name('weekly/report'); //id = user id

Route::post('weekly/checkout/{id}', 'OrderController@weeklyCheckout')->name('weekly/checkout'); //id = user id








