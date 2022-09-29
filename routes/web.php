<?php

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

use App\Http\Controllers\Auth\ForgotPasswordController;

Auth::routes();
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('forgot.password.get');
Route::post('/forgot-password', [ForgotPasswordController::class, 'submitForgotPasswordForm'])->name('forgot.password.post');

Route::get('/', 'DashboardController@index')->name('index');

Route::group(['namespace' => 'Main', 'middleware' => 'auth'], function(){
    Route::resource('user', 'UserController');
    Route::resource('sync', 'SyncController');

    Route::get('changePassword', 'UserController@changePassword')->name('changePassword');
    Route::get('resetPasswordByAdmin/{user_id}', 'UserController@resetPasswordByAdmin')->name('resetPasswordByAdmin');


    Route::get('reports/thruput', 'ReportsController@thruput')->name('reports.thruput');
    Route::post('reports/downloadThrupt', 'ReportsController@downloadThrupt')->name('reports.downloadThrupt');
    Route::get('reports/retail', 'ReportsController@retail')->name('reports.retail');
    Route::post('reports/downloadRetail', 'ReportsController@downloadRetail')->name('reports.downloadRetail');
    Route::get('reports/salesman', 'ReportsController@salesman')->name('reports.salesman');

    Route::get('getCompanyByUser', 'UserController@getCompanyByUser')->name('getCompanyByUser');
    Route::get('getSaleAgentsByCompany', 'UserController@getSaleAgentsByCompany')->name('getSaleAgentsByCompany');
    Route::post('addSaleAgentMapping', 'UserController@addSaleAgentMapping')->name('addSaleAgentMapping');
    Route::post('removeSaleAgentMapping', 'UserController@removeSaleAgentMapping')->name('removeSaleAgentMapping');

    Route::post('syncData', 'SyncController@syncAutoCountData')->name('syncData');

});
