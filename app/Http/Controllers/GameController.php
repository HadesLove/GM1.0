<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use Illuminate\Http\Request;
use App\Libray\RequestTool;

class GameController extends Controller
{
    private $key = 'rJYgMdja4KXMqwFbAibOM7jhls';

    /**
     * 封停ip
     */
    public function closureIp(Request $request)
    {
        $ip       = $request->input('ip');
        $times    = $request->input('times');
        $serverId = intval($request->input('server_id'));

        $url_args = array(
            "ip"    => $ip,
            "oper"  => 1,
            "time"  => intval($times)
        );

        $time      = time();
        $fun       = 'web_op_sys_ip_suspend';
        $mod       = 'login_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 解封ip
     */
    public function unlockIp(Request $request)
    {
        $ip       = $request->input('ip');
        $serverId = intval($request->input('server_id'));

        $url_args = array(
            "ip"    => $ip,
            "oper"  => 0,
        );

        $time      = time();
        $fun       = 'web_op_sys_ip_suspend';
        $mod       = 'login_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

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

        $time      = time();
        $fun       = 'web_op_sys_pay_rmb';
        $mod       = 'pay_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 开服
     */
    public function openSuit(Request $request)
    {
        $serverId = $request->input('server_id');

        $url_args = array(
            "is_open" => 1,
        );

        $time      = time();
        $fun       = 'web_op_node';
        $mod       = 'global';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 关服
     */
    public function closeSuit(Request $request)
    {
        $serverId = $request->input('server_id');

        $url_args = array(
            "is_open" => 0,
        );

        $time      = time();
        $fun       = 'web_op_node';
        $mod       = 'global';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 发送道具
     */
    public function sendProp(Request $request)
    {
        $serverId = $request->input('server_id');
        $uid = $request->input('uid');
        $item_id = $request->input('item_id');
        $count = $request->input('count');

        $url_args = array(
            "uid"     => $uid,
            "item_id" => $item_id,
            "count"   => $count,
        );

        $time      = time();
        $fun       = 'web_op_sys_send_item';
        $mod       = 'pay_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
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
        $serverId = $request->input('server_id');
        $comment = $request->input('comment');

        $url_args = array(
            "comment"      => $comment,
        );

        $time      = time();
        $fun       = 'web_op_sys_chat';
        $mod       = 'chat_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 发送跑马灯
     */
    public function sendMarquee(Request $request, Broadcast $broadcast)
    {
        $id = $request->input('id');

        $res = $broadcast->where(['id' => $id])->first();

        $str_long_content = strlen($res->content);
        $contents = '';
        for ($i=0; $i < $str_long_content ; $i++) {
            if(preg_match('/^[\x7f-\xff]+$/', $res->content[$i])){
                $contents .= urlencode($res->content[$i]);
            }else{
                $contents .= $res->content[$i];
            }
        }

        $serverId = intval($res->server_id);

        $url_args = array(
            "id"       => intval($id),
            "interval" => intval($res->interval),
            "times"    => intval($res->times),
            "content"  => strtolower($contents),
        );

        $time      = time();
        $fun       = 'web_op_sys_broadcast';
        $mod       = 'chat_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    /**
     * 取消跑马灯
     */
    public function cancelMarquee(Request $request, Broadcast $broadcast)
    {
        $id = $request->input('id');

        $res = $broadcast->where(['id' => $id])->first();

        $serverId = intval($res->server_id);

        $url_args = array(
            "id"       => intval($id),
        );

        $time      = time();
        $fun       = 'web_op_sys_broadcast_undo';
        $mod       = 'chat_api';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

    public function timeTack(Request $request)
    {
        $work_time = $request->input('work_time');

        $serverId = intval($request->input('server_id'));

        $url_args = array(
            "work_time" => $work_time,
        );

        $time      = time();
        $fun       = 'web_op_work_day';
        $mod       = 'global';

        $result = $this->requestWX($url_args, $fun, $mod, $time, $serverId, $this->key);

        if ($result['res'] == "1") {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }
    protected function requestWX($url_args, $fun, $mod, $time, $serverId, $key)
    {
        $sign_args = json_encode($url_args);

        $sign = md5("args={$sign_args}&fun={$fun}&mod={$mod}&sid={$serverId}&time={$time}&key={$key}");

        //组装内容
        $info = array(
            'args'      => $sign_args,
            'fun'       => $fun,
            'mod'       => $mod,
            'sid'       => $serverId,
            'time'      => $time,
            'sign'      => $sign,
        );

        $res = RequestTool::send_post(env('WXURL'), $info);

        $result = json_decode($res, true);

        return $result;
    }
}
