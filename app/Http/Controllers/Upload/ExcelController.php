<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use Excel;

class ExcelController extends Controller
{
    public function giftInfoExcel()
    {
        $cellData = [
            ['礼包码'],
        ];
        Excel::create('礼包码',function($excel) use ($cellData){
            $excel->sheet('gift', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');

    }
}