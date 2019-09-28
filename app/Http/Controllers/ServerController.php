<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    private $key = 'rJYgMdja4KXMqwFbAibOM7jhls';

    public function index(Request $request, Server $server)
    {
        $orm = $server->select('id', 'server_name', 'logo', 'type', 'channel_id', 'beginTime', 'endTime', 'note');

        $list = $orm->paginate(20);

        return response(Response::Success($list));
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

    public function save(Request $request, Server $server)
    {
        $id           = $request->input('id');
        $server_name  = $request->input('server_name');
        $logo         = $request->input('logo');
        $note         = $request->input('note');

        $orm = $server->where(['id' => $id])->first();

        $orm->server_name = $server_name;
        $orm->logo        = $logo;
        $orm->note        = $note;
        $orm->updated_at  = date('Y-m-d H:i:s', time());
        $result = $orm->save();

        if (!$result){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        return response(Response::Success());
    }
}