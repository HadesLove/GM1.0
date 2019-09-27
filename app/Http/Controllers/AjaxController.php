<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\WhiteIp;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    private $key = '51Game@123.com&%#';

    public function whiteIpCheck(Request $request, WhiteIp $white_ip)
    {
        $ip   = $request->input('ip');
        $sid  = $request->input('sid');
        $sign = $request->input('sign');

        if($sign !== md5($ip.$sid.$this->key)){
            return response(Response::Success());
        }

        $reslut = $white_ip->where(['ip' => $ip, 'server_id' => $sid])->first();

        if($reslut){
            return response(Response::Error(trans('ResponseMsg.WHITE_IP_NOT_FOUND'), 90005));
        }

        return response(Response::Success());
    }

    public function giftUseCheck(Request $request)
    {
        $roleId    = $request->input('roleId');
        $code      = $request->input('code');
        $channelId = $request->input('channelId');
        $sign      = $request->input('sign');

        if (!$roleId || !$code || !$channelId || !$sign){

            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 137001));
        }

        if ($sign !== md5($roleId.$code.$channelId.$this->key)){

            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 137002));
        }

        $map = array(

        );

        $res = M()->table('lg_code a')->where($map)
            ->join('lg_code_box b ON b.id = a.box_id')->join('lg_code_batch c ON c.id = a.batch_id')
            ->field('a.id as code_id,a.batch_id,a.box_id,a.code,g.status,a.start_time,a.end_time,b.box_item_list, c.channel')
            ->find();

        if (!$res){
            $msg = array(
                'code' => 0,
                'err_code' => 137004
            );

            exit(json_encode($msg? $msg: array()));
        }

        if ($res['status']){
            $msg = array(
                'code' => 0,
                'err_code' => 137005
            );

            exit(json_encode($msg? $msg: array()));
        }

        $roleInfo = M('code_use', C('DB_PREFIX_API'))->where(['role_id' => $data['roleId'], 'code_box_id' => $res['box_id']])->find();

        if ($roleInfo){
            $msg = array(
                'code' => 0,
                'err_code' => 137006
            );

            exit(json_encode($msg? $msg: array()));
        }


        $info = array(
            'code_id'       => $res['code_id'],
            'code'          => $data['code'],
            'role_id'       => $data['roleId'],
            'code_box_id'   => $res['box_id']
        );

        $result = M('code_use', C('DB_PREFIX_API'))->add($info);

        $codeInfo = M('code', C('DB_PREFIX_API'))->where($map)->save(['status' => 1]);

        if ($result && $codeInfo){
            $msg = array(
                'code' => 1,
                'err_code' => 137007,
                'item' => $res['box_item_list']
            );

            exit(json_encode($msg? $msg: array()));
        }

        $msg = array(
            'code' => 0,
            'msg'  => '使用失败'
        );

        exit(json_encode($msg? $msg: array()));

    }
}