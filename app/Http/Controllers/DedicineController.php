<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Channel;
use App\Models\Good;
use App\Models\Manager;
use App\Models\Server;

class DedicineController extends Controller
{
    public function getChannelList(Channel $channel)
    {
        $list = $channel->select('id', 'channel_name')->get();

        return response(Response::Success($list));
    }

    public function getServerList(Server $server)
    {
        $list = $server->select('id', 'server_name')->get();

        return response(Response::Success($list));
    }

    public function getManagerList(Manager $manager)
    {
        $list = $manager->select('id', 'manager_name')->get();

        return response(Response::Success($list));
    }

    public function getGoodsList(Good $good)
    {
        $list = $good->select('id', 'good_name')->get();

        return response(Response::Success($list));
    }
}