<?php
namespace app\common\controller;

use think\Controller;

class BaseController extends Controller
{
    /**
    * 初始化方法
    */
//    private function initialize()
//    {
//        return '';
//    }

    function to404()
    {
        $this->redirect('/public/static/404.html');
    }
}
