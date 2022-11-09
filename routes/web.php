<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*Route::get('/', function () {
    return view('home');
})->name('homeDashboard');*/
Route::post('/insert','RegisterController@create')->name('posting');

Auth::routes();

Route::post('custom-login', 'AdminController@customLogin')->name('custom-login'); 


//Route::get('/', 'HomeController@index')->name('home');

Route::get('/home/', 'AdminController@dashboard')->middleware(
    [
        'auth'
    ])->name('home');


Route::get('/bins', 'BinController@index')->middleware(
    [
        'auth'
    ])->name('bins');

Route::get('/order-details/{id}', 'AdminController@order_details')->middleware([
        'auth'
    ])->name('order-details');

Route::get('/dashboard/', 'AdminController@dashboard')->middleware([
        'auth'
    ])->name('dashboard');

Route::post('/filter', 'AdminController@filter')->middleware([
        'auth'
    ])->name('filter.content');

Route::get('/user/', 'AdminController@user')->middleware([
        'auth'
    ])->name('user');

Route::get('/trucks/', 'AdminController@trucks')->middleware([
        'auth'
    ])->name('trucks');

Route::get('/inventories/', 'AdminController@inventries')->middleware([
        'auth'
    ])->name('inventory');

Route::get('/orders/', 'AdminController@orders')->middleware([
        'auth'
    ])->name('orders');

Route::post('/bulk.inventory', 'BulkInventoryUploadController@bulkCsvUpload')->middleware([
        'auth'
    ])->name('bulk.inventory');

Route::get('/bulk.inventory.submit', 'BulkInventoryUploadController@bulkInventoryStore')->middleware([
        'auth'
    ])->name('bulk.inventory.submit');

Route::get('/bulk.inventory.close', 'BulkInventoryUploadController@deleteuploadCsv')->middleware([
        'auth'
    ])->name('bulk.inventory.close');

Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('route:clear');
    // return what you want
});
Route::get('/migrate', function(){
    \Artisan::call('migrate');
    dd('migrated!');
});
//user profile
Route::get('/user-profile/{id}', 'AdminController@user_profile')->middleware([
        'auth'
    ])->name('user-profile');

Route::post('/user-profile-update', 'AdminController@editProfile')->middleware([
        'auth'
    ])->name('user-profile-update');

Route::get('/orders-parts', 'AdminController@orderParts')->middleware([
        'auth'
    ])->name('orders-parts');


Route::get('/search-parts', 'AdminController@searchParts')->middleware([
        'auth'
    ])->name('search-parts');

Route::get('/part-details/{basePN}', 'AdminController@partDetails')->middleware([
        'auth'
    ])->name('part-details');

Route::post('/create-order', 'AdminController@checkoutFromAdmin')->middleware([
        'auth'
    ])->name('create-order');

Route::post('/add-to-cart', 'AdminController@addToCartFromAdmin')->middleware([
        'auth'
    ])->name('add-to-cart');

Route::post('/view-cart', 'AdminController@viewCart')->middleware([
        'auth'
    ])->name('view-cart');
Route::post('/remove-cart', 'AdminController@removeCart')->middleware([
        'auth'
    ])->name('remove-cart');
Route::get('/part-model/{modelId}/{basePN}', 'AdminController@partModel')->middleware([
        'auth'
    ])->name('part-model');

Route::post('/model-variations', 'AdminController@modelVariation')->middleware([
        'auth'
    ])->name('model-variations');


Route::get('/my-account', 'AdminController@myAccount')->middleware([
        'auth'
    ])->name('my-account');