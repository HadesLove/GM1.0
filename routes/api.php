<?php


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
Route::any('login', 'LoginController@index');

Route::group(['middleware' => 'AuthToken', 'prefix' => 'auth'], function (){

    Route::post('manager/add', 'ManagerController@store');
    Route::patch('manager/{id}', 'ManagerController@update');
    Route::any('manager/list', 'ManagerController@managerList');

    Route::get('manager/getList', 'DedicineController@getManagerList');
    Route::get('channel/getList', 'DedicineController@getChannelList');
    Route::get('server/getList', 'DedicineController@getServerList');
    Route::get('goods/getList', 'DedicineController@getGoodsList');

    Route::any('carte/list', 'CarteController@carteList');

    Route::post('account/add', 'AccountController@store');
    Route::any('account/list', 'AccountController@accountList');
    Route::patch('account/{id}', 'AccountController@update');
    Route::post('account/{id}', 'AccountController@save');
    Route::get('info', 'AccountController@accountInfo');

});