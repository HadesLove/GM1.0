<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\CodeBatch;
use App\Models\CodeBox;
use App\Models\Content;
use App\Models\Gmmail;
use App\Models\Good;
use Illuminate\Http\Request;

class GMController extends Controller
{
    public function sendMail(Request $request, Gmmail $gmmail)
    {
        $server   = $request->input('server');
        $title    = $request->input('title');
        $role     = $request->input('role');
        $item_id  = $request->input('item_id');
        $content  = $request->input('content');
        $channel  = $request->input('channel');

        $item = array();
        foreach ($item_id as $item_key => $item_value){
            $item_val = json_decode($item_value, true);
            if (!empty($item_val)){
                $item[$item_val['selectVal']] = intval($item_val['num']);
            }
        }

        if (empty($role)) {
            $role = array();
        } else {
            $role = explode("|", $role);
        }

        $role_int = array();
        foreach ($role as $role_val){
            $role_int[] = intval($role_val);
        }

        if (!empty($role_int)){
            $url_args = array(
                "objects"     => $role_int,
                "title"       => $this->checkout_chinese($title) ? strtolower(urlencode($title)) : $title,
                "content"     => $content ? ($this->checkout_chinese($content) ? strtolower(urlencode($content)) : $content) : "",
                "items"       => json_encode($item),
            );

            $sign_args = json_encode($url_args);
            $sign = md5("args={$sign_args}&fun=web_op_sys_mail&mod=mail_api&sid={$server}&time=".time()."&key=".env('WUXIAKEY'));

            //组装内容
            $info = array(
                'args'      => $sign_args,
                'fun'       => 'web_op_sys_mail',
                'mod'       => 'mail_api',
                'sid'       => $server,
                'time'      => time(),
                'sign'      => $sign,
            );

            //发送内容
            $res = $this->send_post(env('WXURL'), $info);

            dump($res);

            /*if ($res['res'] == "1") {
                return response(Response::Success());
            } else {
                return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
            }*/
        }

        /*if (!empty($role_int)){}

        foreach ($server as $server_val)
        {
            dump($server_val);
        }

        $url_args = array(
            "objects"     => $channel ? intval($channel) : $role_int,
            "title"       => $this->checkout_chinese($title) ? strtolower(urlencode($title)) : $title,
            "content"     => $content ? ($this->checkout_chinese($content) ? strtolower(urlencode($content)) : $content) : "",
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
        $res = $this->send_post($this->WXUrl, $info);

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
        }*/
    }

    protected function checkout_chinese($str)
    {
        if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $str, $match)) {
            return true;
        } else {
            return false;
        }
    }

    protected function send_post($url, $params) {

        $post_data = http_build_query($params);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $post_data,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    public function loginNoticeStore(Request $request, Content $content)
    {
        $details = $request->input('details');
        $title   = $request->input('title');

        if (!$details){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        $content->content = $details;
        $content->title   = $title;
        $result = $content->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    public function loginNoticeList(Request $request, Content $content)
    {
        $title = $request->input('title');

        $orm = $content->select('id', 'title', 'content', 'channel_id', 'status', 'note');

        if ($title){
            $orm->where(['title' => $title]);
        }

        $list = $orm->paginate(3);

        return response(Response::Success($list));
    }

    public function getLoginNotice(Content $content)
    {
        $data = $content->where(['status' => 1])
            ->select('content')
            ->OrderBy('id DESC')
            ->first();

        return response(Response::Success($data));
    }

    public function giftDeployList(Request $request, CodeBox $codeBox, Good $good)
    {
        $box_name = $request->input('box_name');
        $account_id = $request->input('account_id');

        $orm = $codeBox->with(['account' => function($query){
            $query->select('id', 'real_name');
        }])->select('id', 'box_name', 'box_item_list', 'account_id', 'created_at');

        if ($box_name){
            $orm->where(['box_name' => $box_name]);
        }

        if ($account_id){
            $orm->where(['account_id' => $account_id]);
        }

        $goodsList = $good->get();

        $goods = $this->convert_arr_key($goodsList, 'id', 'good_name');

        $list = $orm->paginate(3);

        foreach ($list as $key=>$value){
            $itemList = json_decode($value['box_item_list'], true);
            $items = '';
            foreach ($itemList as $k=>$val){
                $items .= $goods[$k] . ":" . $val . ";";
            }
            $value['box_item_content'] = $items;
        }

        return response(Response::Success($list));
    }

    public function giftDeployStore(Request $request, CodeBox $code_box)
    {
        $box_name = $request->input('box_name');
        $box_item = $request->input('box_item');

        if ($code_box->where(['box_name' => $box_name])->first()){
            return response(Response::Error(trans('ResponseMsg.GIFT_HAS_EXISTED'), 90002));
        }

        $item = array();
        foreach ($box_item as $item_key => $item_value){
            $item_val = json_decode($item_value, true);
            if (!empty($item_val)){
                $item[$item_val['selectVal']] = intval($item_val['num']);
            }
        }

        $code_box->box_name      = $box_name;
        $code_box->box_item_list = json_encode($item);
        $code_box->account_id    = UID;
        $result = $code_box->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    public function giftDeployUpdate(Request $request, CodeBox $code_box)
    {
        $id       = $request->input('id');
        $box_name = $request->input('box_name');
        $box_item = $request->input('box_item');

        $code = $code_box->where(['box_name' => $box_name])->first();

        if ($code){
            if ($code->id != $id){
                return response(Response::Error(trans('ResponseMsg.GIFT_HAS_EXISTED'), 90002));
            }

        }

        $item = array();
        foreach ($box_item as $item_key => $item_value){
            $item_val = json_decode($item_value, true);
            if (!empty($item_val)){
                $item[$item_val['selectVal']] = intval($item_val['num']);
            }
        }

        $orm = $code_box->where(['id' => $id])->first();

        $orm->box_name      = $box_name;
        $orm->box_item_list = json_encode($item);
        $result = $orm->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    public function giftCodeBatchStore(Request $request, CodeBatch $code_batch)
    {
        $batch_name = $request->input('batch_name');
        $batch_detail = $request->input('batch_detail');
        $code_box_id = $request->input('code_box_id');
        $code_prefix = $request->input('code_prefix');
        $code_length = $request->input('code_length');
        $platform = $request->input('platform');
        $channel_id = $request->input('channel_id');
        $use_count = $request->input('use_count');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');

        if ($code_batch->where(['batch_name' => $batch_name])->first()){
            return response(Response::Error(trans('ResponseMsg.GIFT_CODE_BATCH_HAS_EXISTED'), 90003));
        }

        $code_batch->batch_name    = $batch_name;
        $code_batch->batch_detail  = $batch_detail;
        $code_batch->code_box_id   = $code_box_id;
        $code_batch->code_prefix   = $code_prefix;
        $code_batch->code_length   = $code_length;
        $code_batch->platform      = $platform;
        $code_batch->channel_id    = $channel_id;
        $code_batch->use_count     = $use_count;
        $code_batch->start_time    = $start_time;
        $code_batch->end_time      = $end_time;
        $code_batch->account_id    = UID;
        $result = $code_batch->save();

        if ($result){
            return response(Response::Success());
        }
        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
    }

    protected function convert_arr_key($arr, $key_name, $val_name)
    {
        $arr2 = array();
        foreach ($arr as $key => $val) {
            $arr2[$val[$key_name]] = $val[$val_name];
        }
        return $arr2;
    }
}