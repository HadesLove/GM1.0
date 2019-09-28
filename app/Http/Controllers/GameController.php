<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    private $key = 'rJYgMdja4KXMqwFbAibOM7jhls';

    /**
     * 充值
     */
    public function recharge(Request $request)
    {
        $uid      = $request->input('uid');
        $serverId = $request->input('server_id');
        $goods_id = $request->input('goods_id');

        $url_args = array(
            "uid"      => $uid,
            "goods_id" => $goods_id,
        );

        $time = time();
        $sign_args = json_encode($url_args);
        $sign = md5("args={$sign_args}&fun=web_op_sys_pay_rmb&mod=pay_api&sid={$serverId}&time={$time}&key={$this->key}");

        //组装内容
        $info = array(
            'args'      => $sign_args,
            'fun'       => 'web_op_sys_pay_rmb',
            'mod'       => 'pay_api',
            'sid'       => $serverId,
            'time'      => $time,
            'sign'      => $sign,
        );

        //发送内容
        $res = $this->send_post(env('WXURL'), $info);

        $res = json_decode($res, true);

        if ($res['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 聊天公告
     */
    public function chatAnnouncement(Request $request)
    {

    }

    /**
     * 开服
     */
    public function openSuit()
    {

    }

    /**
     * 关服
     */
    public function closeSuit()
    {

    }
}