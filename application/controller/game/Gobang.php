<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/30
 * Time: 10:41
 */

namespace app\controller\game;

use app\common\controller\BaseController;
use app\common\facade\User;

class Gobang extends BaseController
{
    public function index()
    {
        /**
         * 视图渲染
         */
        return view()->assign([
            'title'     => '五子棋',
            'description' => '',
        ]);
    }
}