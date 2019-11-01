<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use App\Models\Code;
use Excel;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    public function giftInfoExcel(Request $request, Code $code)
    {
        $batch_id = $request->input('batch_name');
        $box_id = $request->input('box_name');
        $code_number = $request->input('code');
        $status = $request->input('status');

        $orm = $code->with([
            'codeBatch' => function($query){
                $query->select('id', 'batch_name');
            },
            'codeBox' => function($query){
                $query->select('id', 'box_name');
            }
        ]);

        if ($code_number){
            $orm->where(['code' => $code_number]);
        }

        if ($status){
            $orm->where(['status' => $status]);
        }

        if ($batch_id){
            $orm->whereIn('code_batch_id', $batch_id);
        }

        if ($box_id){
            $orm->whereIn('code_box_id', $box_id);
        }

        $list = $orm->get();

        $cellData = [
            ['礼包码'],
        ];

        foreach ($list as $key=>$value) {
            $cellData[] = array($value['code']);
        }

        Excel::create('礼包码',function($excel) use ($cellData){
            $excel->sheet('gift', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');

    }
}