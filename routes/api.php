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

Route::group(['prefix' => 'v1'], function() {
    Route::post('/login', 'Api\AuthController@login');
    Route::post('/reset', 'Api\ApiController@reset');
});

Route::group([
    'middleware' => 'jwt.verify', 'prefix' => 'v1'
], function() {
    Route::post('/changePassword', 'Api\ApiController@changePassword');
    Route::post('/customers', 'Api\ApiController@getCustomersByUser');
    Route::post('/items', 'Api\ApiController@getItemsByUser');
    Route::post('/terms', 'Api\ApiController@getTermsByUser');
    Route::post('/salesOrders', 'Api\ApiController@getSalesOrdersByUser');
    Route::post('/taxTypes', 'Api\ApiController@getTaxTypesByUser');
    Route::post('/outstandingARs', 'Api\ApiController@getOutstandingARsByUser');
    Route::post('/temporaryReceipts', 'Api\ApiController@getTemporaryReceiptsByUser');
    Route::post('/createSalesOrder', 'Api\ApiController@createSalesOrder');
    Route::post('/createTemporaryReceipt', 'Api\ApiController@createTemporaryReceipt');

    Route::post('/syncData', 'Api\ApiController@syncAutoCountData');

});
