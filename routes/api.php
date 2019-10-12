<?php

use Illuminate\Http\Request;

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

Route::group(['prefix' => 'v1', 'namespace' => 'Api'], function () {

    
    Route::get('cities', 'GeneralsController@cities');
    Route::get('neighborhoods', 'GeneralsController@neighborhoods');
    Route::get('resturants', 'GeneralsController@resturants');
    Route::get('resturant-details', 'GeneralsController@resturantDetails');
    Route::get('items', 'GeneralsController@items');
    Route::get('comments', 'GeneralsController@comments');
    Route::get('notifications', 'GeneralsController@listNotifications');
    Route::post('contact-us', 'GeneralsController@contactUs');
    Route::get('offers', 'GeneralsController@offers');
    Route::get('settings', 'GeneralsController@settings');
    Route::get('payment-methods', 'GeneralsController@paymentMethods');
    Route::get('notifications', 'GeneralsController@notifications');

    Route::group(['prefix' => 'client', 'namespace' => 'client'], function () {
        Route::post('client-register','AuthController@register');
        Route::post('client-login','AuthController@login');
        Route::post('client-reset','Authcontroller@resetPassword');
        Route::post('client-new-password', 'AuthController@newPassword');
    
        Route::group(['middleware' => 'auth:api-client'], function () {
            
            Route::post('update-profile','AuthController@updateProfile');
            Route::post('client-register-token', 'AuthController@registerToken');
            Route::post('client-remove-token', 'AuthController@removeToken');
            Route::post('add-comment', 'MainController@addComment');
            Route::post('new-order', 'MainController@newOrder');
            Route::get('orders', 'MainController@myOrders');
            Route::get('orders', 'MainController@showOrder');
            Route::get('latest-orders', 'MainController@latestOrder');
            Route::get('confirm-orders', 'MainController@confirmOrder');
            Route::get('decline-orders', 'MainController@declineOrder');
            Route::get('notifications', 'MainController@notifications');
        });
    });
    
    

    Route::group(['prefix' => 'resturant', 'namespace' => 'resturant'], function () {
        Route::post('resturant-register','AuthController@register');
        Route::post('resturant-login','AuthController@login');
        Route::post('resturant-reset','Authcontroller@resetPassword');
        Route::post('resturant-new-password', 'AuthController@newPassword');
        Route::get('categories', 'MainController@categories');

        Route::group(['middleware' => 'auth:api-resturant'], function () {
            Route::post('resturant-register-token', 'AuthController@registerToken');
            Route::post('resturant-remove-token', 'AuthController@removeToken');
            Route::post('resturant-update-profile','AuthController@updateProfile');
            Route::post('add-item','MainController@addItem');
            Route::post('edit-item','MainController@editItem');
            Route::get('delete-item','MainController@deleteItem');
            Route::get('list-item','MainController@listItem');
            Route::post('add-offer','MainController@addOffer');
            Route::post('edit-offer','MainController@editOffer');
            Route::get('delete-offer','MainController@deleteOffer');
            Route::get('list-offer','MainController@listOffer');
            Route::get('orders','MainController@myOrders');
            Route::get('order','MainController@showOrder');
            Route::get('accept-order','MainController@acceptOrder');
            Route::get('reject-order','MainController@rejectOrder');
            Route::get('confirm-order','MainController@confirmOrder');
            Route::get('notifications','MainController@notifications');
        });
    });

    
});
