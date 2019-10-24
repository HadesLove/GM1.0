<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Channel;
use App\Models\Code;
use App\Models\CodeUse;
use App\Models\Content;
use App\Models\Server;
use App\Models\WhiteIp;
use Illuminate\Http\Request;
use DB;

class AjaxController extends Controller
{
    private $key = '51Game@123.com&%#';

    /**
     * 白名单验证
     * @param Request $request
     * @param WhiteIp $white_ip
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function whiteIpCheck(Request $request, WhiteIp $white_ip, Server $server)
    {
        $ip   = $request->input('ip');
        $sid  = $request->input('sid');
        $sign = $request->input('sign');

        if ($server->where(['id' => $sid, 'status' => 0])->first()){
            return response(Response::Error('正常登录', 20000));
        }

        if ($sid < 1000){
            return response(Response::Error('内网测试账号可以正常登录', 20000));
        }

        if($sign !== md5($ip.$sid.$this->key)){
            return response(Response::Error('不在白名单内禁止登录', 1));
        }

        $result = $white_ip->where(['ip' => $ip, 'server_id' => $sid, 'status' => 1])->first();

        if($result){
            return response(Response::Error('登录成功', 20001));
        }

        return response(Response::Error('不在白名单内禁止登录', 1));
    }

    /**
     * 礼包码验证接口
     * @param Request $request
     * @param CodeUse $code_use
     * @param Code $codeModel
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function giftUseCheck(Request $request, CodeUse $code_use, Code $codeModel)
    {
        $rid  = $request->input('roleId');
        $code = $request->input('code');
        //$cid  = $request->input('channelId');
        $sid  = $request->input('serverId');
        $sign = $request->input('sign');

        if (!$rid || !$code  || !$sid || !$sign){
            return response(Response::RequestError(137001));
        }

        if ($sign !== md5($rid.$code.$sid.$this->key)){
            return response(Response::RequestError(137002));
        }

        $res = $codeModel
            ->with([
            'codeBox', 'codeBatch'
            ])
            ->where(['code' => $code, 'status' => 0])
            ->first();

        if (!$res){
            return response(Response::RequestError(137004));
        }

        if ($res->code_batch['server_id'] != 0){
            if ($sid != $res->code_batch['server_id']){
                return response(Response::RequestError(137005));
            }
        }

        $role = $code_use->where(['role_id' => $rid, 'code_box_id' => $res->code_box_id])->first();

        if ($role){
            return response(Response::RequestError(137006));
        }

        if ($res->remain_count <= 0){
            return response(Response::RequestError(137004));
        }

        DB::beginTransaction();
        try{
            $code_use->code_id     = $res->id;
            $code_use->code        = $res->code;
            $code_use->role_id     = $rid;
            $code_use->code_box_id = $res->code_box_id;
            $code_use->save();
            $count = $res->remain_count - 1;
            $status = 0;
            if ($count == 0){
                $status = 1;
            }
            $codeModel->where(['code' => $code])->update(['remain_count' => $count, 'status' => $status]);
            DB::commit();
            return response(Response::RequestSuccess(137007, $res->codeBox['box_item_list']));
        }catch (\Exception $exception){
            DB::rollBack();
            return response(Response::RequestMsgError('使用失败'));
        }

    }

    /**
     * 获取区服登录公告
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getCast(Request $request, Content $content)
    {
        $sdk_name = $request->input('sdk_name');
        $c_id = $request->input('c_id');

        if (!$sdk_name || !$c_id){
            return response(Response::Error('必要参数缺失', 404));
        }

        $result = $content
            ->where('channel_id', '=', $c_id)
            ->select('content')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($result){
            return response(Response::Success($result));
        }else{
            return response(Response::RequestMsgSuccess($result));
        }
    }
}