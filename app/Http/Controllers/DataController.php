<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Ban;
use App\Models\Channel;
use App\Models\Server;
use DB;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
	 * 新增玩家
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

	    foreach ($list as $key=>$value) {
            $value->cid = $channel[$value->cid]['channel_name'];
            $value->server_name = $server[$value->sid]['server_name'];
            $value->login_time = date('Y-m-d H:i:s', $value->login_time);
            $value->reg_time   = date('Y-m-d H:i:s', $value->reg_time);
            $value->last_time  = date('Y-m-d H:i:s', $value->last_time);

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

	protected function number_segment_between($str_num, $min, $max)
    {
        return version_compare($str_num, $min, '>=') and version_compare($str_num, $max, '<=');
    }
}