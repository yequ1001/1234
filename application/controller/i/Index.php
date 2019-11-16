<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/30
 * Time: 10:41
 */

namespace app\controller\i;

use app\common\controller\BaseController;
use app\common\facade\User;
use app\common\facade\Str;

class Index extends BaseController
{
    public function index()
    {
        /**
         * 视图渲染
         */
        return view()->assign([
            'title'     => '本机',
            'description' => '',
            'ua'        => User::ua(),
            'ip'        => User::ip() .' '. User::ip_info(User::ip()),
        ]);
    }

    public function info($ip)
    {
        if (!empty(input('ip'))) {
            $ip = input('ip');
        }
        return User::ip_info($ip);
    }
}