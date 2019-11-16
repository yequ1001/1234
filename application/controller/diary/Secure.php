<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/7/26
 * Time: 21:00
 */

namespace app\controller\diary;

use app\common\controller\BaseController;
use app\common\facade\Str;
use app\common\facade\User;

class Secure extends BaseController
{
    public function index()
    {
        /* 渲染视图 */
        return view()->assign([
            'title'         => '隐私安全验证',
            'description' => '',
        ]);
    }

    /**
     * 读取并验证安全码
     */
    public function read()
    {
        // 获取用户输入的安全码
        $password2 = input('password2');
        $password2 = Str::cookieKey([$password2]);
        
        // 获取数据库储存的用户安全码
        $UserData = User::data();

        // 登录密码验证
        if ($UserData->password2 == $password2) {
            session('password2_time', date('Y-m-d H:i:s'));
            return true;
        } else {
            return '安全码不正确';
        }
    }
}