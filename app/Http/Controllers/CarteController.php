<?php

namespace App\Http\Controllers;

use App\Libray\Response;
use App\Models\Carte;

class CarteController extends Controller
{
    public function carteList(Carte $carte)
    {
        $arr = array();

        $list = $carte::GetAllMenuTree(0, $arr);

        return response(Response::Success($list));
    }
}