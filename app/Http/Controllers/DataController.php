<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Libray\RSA;
use App\Models\Ban;
use App\Models\Channel;
use App\Models\Good;
use App\Models\Server;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DataController extends Controller
{
    /**
     * 角色列表
     * @param Request $request
     * @param Ban $ban
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function roleList(Request $request, Ban $ban)
	{
	    $role_id    = $request->input('role_id');
	    $role_name  = $request->input('role_name');
	    $channel_id = $request->input('channel_id');
	    $server_id  = $request->input('server_id');
	    $order      = $request->input('order');
	    $by         = $request->input('by');
	    $time       = $request->input('time');

        $orm = DB::connection('wxfyl')
            ->table('user')
            ->select('uid', 'uuid', 'sid', 'cid', 'uname', 'sex', 'renown_lv', 'pay_gold', 'renown', 'gold', 'silver', 'reg_time', 'reg_ip', 'login_times','login_time', 'last_time', 'last_ip');

        if ($role_id){
           $orm->where(['uid' => $role_id]);
        }

        if ($role_name){
           $orm->where('uname', 'like', '%'.$role_name.'%');
        }

        if ($channel_id){
           $orm->where(['cid' => $channel_id]);
        }

        if ($server_id){
           $orm->where(['sid' => $server_id]);
        }

        if ($time[0] && $time[1]){
           $orm->whereBetween('reg_time', array(strtotime($time[0]), strtotime($time[1])));
        }

        if ($order && $by){
            $orm->orderBy($order, $by);
        }else{
            $orm->orderBy('reg_time', 'ASC');
        }

        $list = $orm->paginate(20);

        $server  = Server::all()->keyBy('id')->toArray();
	    $channel = Channel::all()->keyBy('id')->toArray();

	    $username = DB::connection('wxfyl_account')
            ->table('account')
            ->select('account', 'uuid')
            ->get()->toArray();

        $userCount = array_column($username, null, 'uuid');

	    foreach ($list as $key=>$value) {
            $value->cid = $channel[$value->cid]['channel_name'];
            $value->server_name = $server[$value->sid]['server_name'];
            $value->login_time = date('Y-m-d H:i:s', $value->login_time);
            $value->reg_time   = date('Y-m-d H:i:s', $value->reg_time);
            $value->last_time  = date('Y-m-d H:i:s', $value->last_time);

            $value->username = substr($userCount[$value->uuid]->account, 21);

            $status = $ban->where(['role_id' => $value->uid, 'serverId' => $value->sid, 'status' => 1])->select('type')->get();
            $value->status = '';

            if ($status) {
                foreach ($status as $k => $v) {
                    if ($v->type == 1) {
                        $value->status .= '禁言';
                        $value->chat = 1;
                    }
                    if ($v->type == 2) {
                        $value->status .= '禁登';
                        $value->login = 1;
                    }
                }
            }

            if (!$value->status){
                $value->status = '正常';
            }

            switch (true)
            {
                case $this->number_segment_between($value->pay_gold, 0 , 5):
                    $value->vip = 0;
                    break;
                case ($value->pay_gold >= 6 && $value->pay_gold <= 41):
                    $value->vip = 1;
                    break;
                case ($value->pay_gold >= 42 && $value->pay_gold < 98):
                    $value->vip = 2;
                    break;
                case ($value->pay_gold >= 98 && $value->pay_gold < 198):
                    $value->vip = 3;
                    break;
                case ($value->pay_gold >= 198 && $value->pay_gold < 498):
                    $value->vip = 4;
                    break;
                case ($value->pay_gold >= 498 && $value->pay_gold < 998):
                    $value->vip = 5;
                    break;
                case ($value->pay_gold >= 998 && $value->pay_gold < 2000):
                    $value->vip = 6;
                    break;
                case ($value->pay_gold >= 2000 && $value->pay_gold < 3600):
                    $value->vip = 7;
                    break;
                case ($value->pay_gold >= 3600 && $value->pay_gold < 5800):
                    $value->vip = 8;
                    break;
                case ($value->pay_gold >= 5800 && $value->pay_gold < 9800):
                    $value->vip = 9;
                    break;
                case ($value->pay_gold >= 9800 && $value->pay_gold < 15000):
                    $value->vip = 10;
                    break;
                case ($value->pay_gold >= 15000 && $value->pay_gold < 21000):
                    $value->vip = 11;
                    break;
                default:
                    $value->vip = 12;
            }
        }

        return response(Response::Success($list));
	}

    /**
     * 宠妻列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function wifeList(Request $request)
	{
	    $uid      = $request->input('uid');
	    $channel  = $request->input('channel');
	    $wife_id  = $request->input('wife_id');
	    $intimacy = $request->input('intimacy');
	    $wife_event_desc = $request->input('wife_event_desc');
	    $time     = $request->input('time');

        $orm = DB::connection('wxfyl_l2002')
            ->table('lg_wife')
            ->select('id', 'uid', 'channel', 'wife_id', 'intimacy', 'child_count', 'wife_event_type', 'wife_event_desc', 'old_value', 'add_value', 'new_value', 'time');

        $list = $orm->paginate(20);

        $role = DB::connection('wxfyl_s2002')
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        foreach ($list as $key=>$value) {
            $value->time        = date('Y-m-d H:i:s', $value->time);

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
            }else{
                $value->role_name = '-';
            }
        }

        return response(Response::Success($list));
	}

    /**
     * 子女列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function childList(Request $request)
	{
        $orm = DB::connection('wxfyl_l2002')
            ->table('lg_child')
            ->select('id', 'uid', 'cid', 'child_idx', 'child_sex', 'child_name', 'child_lv', 'child_event_type', 'child_event_desc', 'time');

        $list = $orm->paginate(20);

        $role = DB::connection('wxfyl_s2002')
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        foreach ($list as $key=>$value) {
            $value->time        = date('Y-m-d H:i:s', $value->time);

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
            }else{
                $value->role_name = '-';
            }
        }

        return response(Response::Success($list));
	}

    /**
     * 道具变化列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function resourceList(Request $request)
	{
        $orm = DB::connection('wxfyl_l2002')
            ->table('lg_resource')
            ->select('id', 'server_id', 'role_id', 'action_id', 'action_desc', 'item_id', 'init_value', 'add_value', 'result_value', 'channel', 'role_name', 'user_code', 'time');

        $list = $orm->paginate(20);

        $server = Server::all()->keyBy('id')->toArray();
        $good   = Good::all()->keyBy('id')->toArray();

        foreach ($list as $key=>$value) {
            $value->server_name = $server[$value->server_id]['server_name'];
            $value->item_name   = $good[$value->item_id]['good_name'];
            $value->time        = date('Y-m-d H:i:s', $value->time);
        }

        return response(Response::Success($list));
	}

	/**
     * 角色登录列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function roleStreamList(Request $request)
	{
	    $role_id = $request->input('role_id', null);

	    $time = $request->input('time', null);

        $orm = DB::connection('wxfyl_l2002')
            ->table('lg_role_stream')
            ->select('id', 'channel', 'userCode', 'serverId', 'roleId', 'loginTime', 'loginOutTime', 'onlineTime', 'createTime');

        if ($role_id){
            $orm->where(['roleId' => $role_id]);
        }

        if ($time){
            $orm->whereBetween('loginTime', [strtotime($time[0]), strtotime($time[1])]);
            $orm->whereBetween('loginOutTime', [strtotime($time[0]), strtotime($time[1])]);
        }

        $server  = Server::all()->keyBy('id')->toArray();

        $role = DB::connection('wxfyl')
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $list = $orm->paginate(20);

        $nameCount = array_column($role, null, 'uid');

	    foreach ($list as $key=>$value) {
            $value->server_name  = $server[$value->serverId]['server_name'];
            $value->loginTime    = date('Y-m-d H:i:s', $value->loginTime);
            $value->loginOutTime = date('Y-m-d H:i:s', $value->loginOutTime);
            $value->createTime   = date('Y-m-d H:i:s', $value->createTime);

            if (isset($nameCount[$value->roleId])){
                $value->role_name = $nameCount[$value->roleId]->uname;
            }else{
                $value->role_name = '-';
            }
        }

        return response(Response::Success($list));
	}

    /**
     * 聊天列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
	public function chatList(Request $request, Ban $ban)
    {
        $orm = DB::connection('wxfyl_l2002')
            ->table('lg_chat')
            ->select('id', 'uid', 'chatTime', 'chatChannel', 'chatText')
            ->orderBy('id', 'asc');

        $list = $orm->paginate(20);

        $role = DB::connection('wxfyl_s2002')
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        foreach ($list as $key=>$value) {
            $value->chatTime   = date('Y-m-d H:i:s', $value->chatTime);

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
            }else{
                $value->role_name = '-';
            }

            $status = $ban->where(['role_id' => $value->uid, 'serverId' => 2002, 'status' => 1, 'type' => 1])->select('type')->first();
            $value->status = '';

            if ($status) {
                if ($status->type == 1) {
                    $value->status .= '禁言';
                }
            }
            if (!$value->status){
                $value->status = '正常';
            }
        }

        return response(Response::Success($list));
    }

    /**
     * 实时聊天监控
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function RealTimeChat(Request $request, Ban $ban)
    {
        $id = $request->input('id');

        if (!Redis::get('real_time')){
            $time = time();
            Redis::set('real_time', $time);
            Redis::expire('real_time', 600);
        }

        $orm = DB::connection('wxfyl_l2002')
            ->table('lg_chat')
            ->select('id', 'uid', 'chatTime', 'chatChannel', 'chatText');

        if ($id){
            Redis::set('real_time', null);
            $orm->where('id', '>', $id);
        }else{
            $orm->where('chatTime', '>', Redis::get('real_time'));
        }

        $list = $orm->get();

	    $role = DB::connection('wxfyl_s2002')
            ->table('user')
            ->select('uid', 'uname')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        foreach ($list as $key=>$value) {
            $value->chatTime   = date('Y-m-d H:i:s', $value->chatTime);

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
            }else{
                $value->role_name = '-';
            }

            $status = $ban->where(['role_id' => $value->uid, 'serverId' => 2002, 'status' => 1, 'type' => 1])->select('type')->first();
            $value->status = '';

            if ($status) {
                if ($status->type == 1) {
                    $value->status .= '禁言';
                }
            }
            if (!$value->status){
                $value->status = '正常';
            }
        }

        return response(Response::Success($list));
    }

    /**
     * 订单列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function OrderList(Request $request)
    {
        $orm = DB::connection('wxfyl_order')
            ->table('lg_pay')
            ->where(['sid' => 20002])
            ->select('orderId', 'tranId', 'goodsId', 'uid', 'time', 'sid', 'cid', 'amount');

        $list = $orm->paginate(20);

	    $role = DB::connection('wxfyl')
            ->table('user')
            ->select('uid', 'uname', 'uuid')
            ->get()->toArray();

	    $username = DB::connection('wxfyl_account')
            ->table('account')
            ->select('account', 'uuid')
            ->get()->toArray();

        $nameCount = array_column($role, null, 'uid');

        $userCount = array_column($username, null, 'uuid');

        $server = Server::all()->keyBy('id')->toArray();
        foreach ($list as $key=>$value) {
            $value->time   = date('Y-m-d H:i:s', $value->time);
            $value->server_name = $server[$value->sid]['server_name'];

            if (isset($nameCount[$value->uid])){
                $value->role_name = $nameCount[$value->uid]->uname;
                if (isset($userCount[$nameCount[$value->uid]->uuid])) {
                    $value->username = substr($userCount[$nameCount[$value->uid]->uuid]->account, 21);
                } else {
                    $value->username = '-';
                }
            } else {
                $value->role_name = '-';
                $value->username = '-';
            }
        }

        return response(Response::Success($list));
    }

    /**
     * 数据区间
     * @param $str_num
     * @param $min
     * @param $max
     * @return bool
     */
	protected function number_segment_between($str_num, $min, $max)
    {
        return version_compare($str_num, $min, '>=') and version_compare($str_num, $max, '<=');
    }

    public function test()
    {
        $data = array(
            'dataKey' => '4tfe53w3dnl2j19c',
            'AFDI' => '42059079-1CD8-495F-AA79-9FEE18221C81',
            'economyMsg' => 'iPhone',
            'didupemtem' => 'e52521983fc847ed8c09c40cfd2cc83b',
            'isLoading' => false,
            'VFDI' => 'A9F2CF6C-94F4-4E09-B573-35A4E2DF9DB5',
            'type' => '2',
            'irrigation' => '100007',
            'CFBundleVersion' => 1
        );

        dump(base64_decode('iwkA5ip3NTGeot7c56dTI9lIIZpD1IrDUUA2HQWaobDU2/65OS9FA0gv/2B9t3fjBrKIz3X82C+hTO+aTs2SQBRqRDIjft3oxsPcMsIy/KmUAmuZx3L0t6Zr2lheHleEVamTD1iFW4EvcZ0sWr6AmQN1Uy9SaET+C8Kq6osHTMUH+s0PLZN2L782iqi4cEOqXrPniZWj8nYXz7TCCRc9hUZFrku48UydGoAXtRXN8OX2HpJHvKKB+JHBOiRs+KCv4rbMwv0M48wu9u7g51+xnlS1MInKOUCJQMTcoi3c4mwS/3WQmgSh06uyfqMp9S7qqmjQhYblZfhPr9YRf0nGRDXRioMVSIDIP+cqbV5lLJ+14HQZY0AoN5bxaftFgf2rUh+OkvcxcOQG8/kGjs0YHzqUcOXLBF474Fus+GdgHUv3DlejkTxGJcN3Ju+3P5b9VIBug+Ey0m5ZwlzDbBZ70Sx8m4A8Jdf7N9CbElDPnK9K6trHuMxBtJE/f12Ddms5nNSE0dEbRgZUC+MmcMKU2hxbPBab8BBhd7yZwhDnwAjFCa4AjhGaAmzUk7pA24ETFdmIUrSJXK+74QhCPtRMSL39YYuYG2aTWNVYpD7m4x6QsGeeVCQA4yC4nTjuy09r//HHT+auLt0KDO9A0R6nlo9sJ02H1I6WaOBcYkbxHUo='));die;

        $pubKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQChNeAYA71WgAE0BCYRa5WGNNj+
K8P3+13+CyD5EMi0MPoN6TVpH3aE/0nMKWu4qwFnYTDNYH/ZakC/3LtxnWyigoYS
4uXoZf720QxMYOG9C1DorsxeJrl1WXtYuOwHZrDjVIC34yMZ86zH9u8G9WqPEykt
syzbI7g+RQTl/EWTPQIDAQAB
-----END PUBLIC KEY-----';
        $priKey = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQChNeAYA71WgAE0BCYRa5WGNNj+K8P3+13+CyD5EMi0MPoN6TVp
H3aE/0nMKWu4qwFnYTDNYH/ZakC/3LtxnWyigoYS4uXoZf720QxMYOG9C1Dorsxe
Jrl1WXtYuOwHZrDjVIC34yMZ86zH9u8G9WqPEyktsyzbI7g+RQTl/EWTPQIDAQAB
AoGAbfyc5KWAg0iYCY4fDtmQzVy3A0qGzGTCbvXWzDcIR+/2WpFWsF8X9ItcJR/J
b9e0AH1N14FUGNimToBhnpViLLQumaWLllxi5Di76kNCg7ivo2Ml1neK282ZCZ9v
JOaiAnPEoOWed9Gjx5qWrfF7B5RXyBBtvkHXe1zcIHddwIECQQDW3hN+KzpoRbml
Bs0WBeFMMvahZY7821vOD/A8T8Dk5XMYttmt8jrOa+pdr987Zmr5RTcwVQ8iftrj
4Cd0BmXhAkEAwBJAK3w7s2OkA4lOuQoux+urmjvajg9vAU559rY1pxcpgNBFWvEv
e9jA46trZmWU0h1f/TCycXdr+iEW73Og3QJAbxqmObdgnEpxlEPQCHNB7ITtwsch
CN7kucjEEGus8q8ytLTYGnoGrnZe2dL3O1/aMMr5nqRdDxlJVkuyGuy0AQJBAL6q
32zLnPBNv6mLCs0B4MKxnt4zAJj5lTZ00vook0ZV5etr1Q2cU4jb+U+JAcramEuk
wX80ck/VPylE4+G8pTkCQHu55wyptTjEIxSmiFS31osR0/+CNpf62F5uzgjAMWOi
udTG2A2mPIiELeLAE7AcTAp4vCnMct7QhHUO3Mxvulg=
-----END RSA PRIVATE KEY-----
';


        $pi_key = openssl_pkey_get_private($priKey);


        $pi_key =  openssl_pkey_get_private($priKey);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $pu_key = openssl_pkey_get_public($pubKey);//这个函数可用来判断公钥是否是可用的
        print_r($pi_key);echo "\n";
        print_r($pu_key);echo "\n";

        $data1 = base64_encode(json_encode($data));//原始数据
        $encrypted = "";
        $decrypted = "";

        openssl_private_encrypt($data1,$encrypted, $pi_key);
        $encrypted = base64_encode($encrypted);

        dump($encrypted);


        //openssl_public_encrypt(json_encode($data),$encrypted,$pu_key);

        //dump($encrypted);


        openssl_public_decrypt(base64_decode($encrypted),$decrypted, $pu_key);//私钥加密的内容通过公钥可用解密出来
        dump($decrypted);die;


        /*$ras = new RSA();

        //dump('http://192.168.1.6:8081/rsa_private_key.pem');

        $e = $ras->encrypt(json_encode($data));*/



        //$res = $ras->encrypt(json_encode($data));

        //dump($res);
    }

    /*HRestiQ0FO1ssyFdn2t2vwLlSinLUaOQ1puLpnz4SKtRICjYK0JQbXbLhcfJCkA+5+tevm6sKV3wCEKaR2phZ6WZjlU616bbgQYOeO72yH/pvC4/NO7nKvsRrStkSfid+7vD9bEwwGjLt9fe8LtTJpUPJKT4WcmwKcDtxZ3hJLQ=*/


    public function gologn()
    {
        $data = 'HRestiQ0FO1ssyFdn2t2vwLlSinLUaOQ1puLpnz4SKtRICjYK0JQbXbLhcfJCkA+5+tevm6sKV3wCEKaR2phZ6WZjlU616bbgQYOeO72yH/pvC4/NO7nKvsRrStkSfid+7vD9bEwwGjLt9fe8LtTJpUPJKT4WcmwKcDtxZ3hJLQ=';

        $ras = new RSA(public_path('./rsa_public_key.pem'), public_path('./rsa_private_key.pem'));

        $res = $ras->decrypt($data);

        dump(json_decode($res, true));
    }
}
