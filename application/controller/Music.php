<?php
/**
 * Created by PhpStorm.
 * User: YEQU1001
 * Date: 2019/4/18
 * Time: 23:04
 */

namespace app\controller;

use app\common\controller\BaseController;

class Music extends BaseController
{
    public function index()
    {
        /* 渲染视图 */
        return view()->assign([
            'title' => '四季播放器 - Op112.Com',
        ]);
    }
}