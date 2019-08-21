<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Manager;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function managerList(Request $request, Manager $manager)
    {
        $manager_name = $request->input('name');

        $orm = $manager->select('id', 'manager_name', 'remark', 'status');

        if ($manager_name){
            $orm->where(['manager_name' => $manager_name]);
        }

        $list = $orm->paginate(3);

        return response(Response::Success($list));
    }

    public function store(Request $request, Manager $manager)
    {
        $manager_name = $request->input('name');
        $status       = $request->input('status');
        $remark       = $request->input('remark');

        $menu         = $request->input('menuList');

        return response(Response::Success($menu));

        if ($manager->where(['manager_name' => $manager_name])->first()){
            return response(Response::Error(trans('ResponseMsg.ROLE_HAS_EXISTED'), 90001));
        }

        $manager->manager_name = $manager_name;
        $manager->status       = $status;
        $manager->remark       = $remark;

        $result = $manager->save();

        if ($result){
            return response(Response::Success());
        }

        return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));

    }

    public function update(Request $request, Manager $manager)
    {
        $data = $request->all();

        if (!$manager->where(['id' => $data['id']])->update(['status' => $data['status']])){
            return response(Response::Error(trans('ResponseMsg.SYSTEM_INNER_ERROR'), 40001));
        }

        return response(Response::Success());
    }
}