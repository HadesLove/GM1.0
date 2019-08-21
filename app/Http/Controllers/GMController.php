<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Gmmail;
use Illuminate\Http\Request;

class GMController extends Controller
{
    public function newMail(Request $request, Gmmail $gmmail)
    {
        $server   = $request->input('server');
        $title    = $request->input('title');
        $role     = $request->input('role');
        $item_id   = $request->input('item_id');
        $content  = $request->input('content');
        $channel  = $request->input('channel');

        if (empty($role)) {
            $role = array();
        } else {
            $role = explode("|", $role);
        }

        $item = array();
        foreach ($item as $key => $val) {
            if (strpos($key, "itemId") !== false) {
                if ($val && ($item_id["num" . substr($key, 6)] > 0)) {
                    $item[$val] = intval($item_id["num" . substr($key, 6)]);
                }
            }
        }

        $role_int = array();
        foreach ($role as $value){
            $role_int[] = intval($value);
        }

        $url_args = array(
            "objects"     => $channel ? intval($channel) : $role_int,
            "title"       => checkout_chinese($title) ? strtolower(urlencode($title)) : $title,
            "content"     => $content ? (checkout_chinese($content) ? strtolower(urlencode($content)) : $content) : "",
            "items"       => json_encode($item),
        );

        $sign_args = json_encode($url_args);
        $sign = md5("args={$sign_args}&fun=web_op_sys_mail&mod=mail_api&sid={$server}&time=".time()."&key={$this->key}");

        //组装内容
        $info = array(
            'args'      => $sign_args,
            'fun'       => 'web_op_sys_mail',
            'mod'       => 'mail_api',
            'sid'       => $serverId,
            'time'      => time(),
            'sign'      => $sign,
        );

        //发送内容
        $res = send_post($this->WXUrl, $info);

        $gmmail->role_list = $role;
        $gmmail->server_id = $server;
        $gmmail->role_list = $role;
        $gmmail->role_list = $role;
        $gmmail->title     = $title;
        $gmmail->content   = $content;
        $gmmail->attach_s  = json_encode($item);
        $result = $gmmail->save();

        $res = json_decode($res, true);

        if ($res['res'] == "1") {
            if ($result){
                return response(Response::Success());
            }
            return response(Response::Error(trans('ResponseMsg.SPECIFIED_QUESTIONED_USER_NOT_EXIST'), 30001));

        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        if (IS_POST) {
            $data = I('post.');
            //搜索条件
            if (empty($data["serverId"])) {
                exit(json_encode(array("status" => false, "msg" => "请选择游戏区服！")));
            }

            if (empty($data["title"])) {
                exit(json_encode(array("status" => false, "msg" => "请输入邮件标题！")));
            }

            //分割角色
            if (empty($data["role"])) {
                $role = array();
            } else {
                $role = explode("|", $data["role"]);
            }
            $roleInt = array();
            foreach ($role as $value){
                $roleInt[] = intval($value);
            }

            $serverId = intval($data["serverId"]);

            //获取附件
            $item = array();
            foreach ($data as $key => $val) {
                if (strpos($key, "itemId") !== false) {
                    if ($val && ($data["num" . substr($key, 6)] > 0)) {
                        $item[$val] = intval($data["num" . substr($key, 6)]);
                    }
                }
            }


        } else {
            $response = $this->fetch();
            $this->ajaxReturn(array("status" => 1, "_html" => $response));
        }
    }
}