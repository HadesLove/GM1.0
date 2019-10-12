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
        $orm = $server->select('id', 'server_name', 'logo', 'type', 'channel_id', 'note', 'status');

        $list = $orm->paginate(20);

        return response(Response::Success($list));
    }

    public function serverUpdate(Request $request, Server $server)
    {
        $id = $request->input('id');
        $status = $request->input('status');

        $result = $server->where(['id' => $id])->update(['status' => $status]);

        if ($result) {
            return response(Response::Success());
        } else {
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }
    }

}