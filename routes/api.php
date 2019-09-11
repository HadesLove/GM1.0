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

Route::any('new/role/gift', 'GMController@newRolesGift');

Route::group(['middleware' => 'AuthToken', 'prefix' => 'auth'], function (){

    Route::post('manager/add', 'ManagerController@store');
    Route::patch('manager/{id}', 'ManagerController@update');
    Route::any('manager/list', 'ManagerController@managerList');

    Route::get('manager/getList', 'DedicineController@getManagerList');
    Route::get('channel/getList', 'DedicineController@getChannelList');
    Route::get('server/getList', 'DedicineController@getServerList');
    Route::get('goods/getList', 'DedicineController@getGoodsList');
    Route::get('codebox/getList', 'DedicineController@getGiftDeployList');
    Route::get('codebatch/getList', 'DedicineController@getCodeBatchList');
    Route::get('carte/getList', 'DedicineController@getCarteList');
    Route::get('menu/getList', 'DedicineController@getMenuList');

    Route::any('carte/list', 'CarteController@carteList');
    Route::post('carte/add', 'CarteController@store');

    Route::post('account/add', 'AccountController@store');
    Route::any('account/list', 'AccountController@accountList');
    Route::patch('account/{id}', 'AccountController@update');
    Route::post('account/{id}', 'AccountController@save');
    Route::get('info', 'AccountController@accountInfo');


    Route::post('send/mail', 'GMController@sendMail');
    Route::any('send/mail/list', 'GMController@sendMailList');

    Route::post('roles/gift/store', 'GMController@newRolesGiftStore');
    Route::any('roles/gift/list', 'GMController@newRolesGiftList');
    Route::patch('roles/gift/{id}', 'GMController@newRolesGiftUpdate');

    Route::post('ban/chat', 'GMController@banChat');
    Route::post('ban/login', 'GMController@banLogin');

    Route::any('login/notice/list', 'GMController@loginNoticeList');
    Route::post('login/notice', 'GMController@loginNoticeStore');
    Route::any('get/login/notice', 'GMController@getLoginNotice');

    Route::any('gift/deploy/list', 'GMController@giftDeployList');
    Route::post('gift/deploy', 'GMController@giftDeployStore');
    Route::post('gift/deploy/{id}', 'GMController@giftDeployUpdate');


    Route::post('gift/code/batch', 'GMController@giftCodeBatchStore');
    Route::any('code/batch/list', 'GMController@giftCodeBatchList');
    Route::post('code/batch/{id}', 'GMController@giftCodeBatchUpdate');

    Route::post('gift/code', 'GMController@giftCodeStore');
    Route::any('code/list', 'GMController@giftCodeList');

    Route::any('role/list', 'DataController@roleList');

});