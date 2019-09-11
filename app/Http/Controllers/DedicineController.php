<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Account;
use App\Models\Carte;
use App\Models\Channel;
use App\Models\CodeBatch;
use App\Models\CodeBox;
use App\Models\Good;
use App\Models\Manager;
use App\Models\Server;

class DedicineController extends Controller
{
    public function getChannelList(Channel $channel, Account $account)
    {
        $channel_id = $account
            ->where(['id' => UID])
            ->select('channel')
            ->first();

        $list = $channel
            ->whereIn('id', json_decode($channel_id->channel, true))
            ->select('id', 'channel_name')
            ->get();

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

    public function getGiftDeployList(CodeBox $codeBox)
    {
        $list = $codeBox->select('id', 'box_name')->get();

        return response(Response::Success($list));
    }

    public function getCodeBatchList(CodeBatch $codeBatch)
    {
        $list = $codeBatch->select('id', 'batch_name')->get();

        return response(Response::Success($list));
    }

    public function getCarteList(Carte $carte)
    {
        $list = $carte->select('id', 'carte_name', 'pid')->get();

        foreach ($list as $value){
            if ($value['pid'] == 0){
                $value['carte_name'] = '—'.$value['carte_name'];
            }else{
                $res = $carte->where(['id' => $value['pid']])->first();
                if ($res['pid'] == 0){
                    $value['carte_name'] = '         |—'.$value['carte_name'];
                }else{
                    $value['carte_name'] = '                    └─ '.$value['carte_name'];
                }
            }
        }

        return response(Response::Success($list));
    }

    public function getMenuList(Carte $carte)
    {
        $arr = array();

        $list = $carte::GetAllMenuTree(0, $arr);

        return response(Response::Success($list));
    }
}