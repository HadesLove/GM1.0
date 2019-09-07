<?php

namespace App\Http\Controllers;

use App\Libray\Encryption;
use App\Libray\Response;
use App\Models\Account;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    public function index(Request $request, Account $account)
    {
        $username = $request->input('username', null);
        $password = $request->input('password', null);

        $user = $account->where(['account_name' => $username])->first();

        if (!$user){
            return response(Response::Error(trans('ResponseMsg.USER_NOT_EXIST'), 20004));
        }

        if (!$user->status){
            return response(Response::Error(trans('ResponseMsg.USER_ACCOUNT_FORBIDDEN'), 20003));
        }

        if (!password_verify($password, $user->password)){
            return response(Response::Error(trans('ResponseMsg.USER_LOGIN_ERROR'), 20002));
        }

        $Token = $this->setLoginToken($user);

        return response(Response::Success($Token));
    }


    protected function setLoginToken($user)
    {
        $Token = [
            'user_id'   => $user->id,
            'time'      => time(),
        ];

        $Encryption = new Encryption();

        $Token = $Encryption->encode(json_encode($Token));

        Redis::set($Token, time());
        Redis::expire($Token, 604800);

        return ['Token' => $Token];
    }
}