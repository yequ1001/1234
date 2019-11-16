<?php
namespace app\controller\admins\announcement;

use app\common\controller\BaseController;
use app\common\facade\User;
use app\common\model\User as UserModel;

class Index extends BaseController
{
    public function index()
    {
        /* 渲染视图 */
        return view()->assign([
            'title' => '系统通知管理 - 机密',
            'description' => '',
        ]);
    }

    public function save()
    {
        $user = input('user');
        $content = input('content');

        $send = User::announcement($user, $content);

        if ($send) {
            return true;
        } else {
            return false;
        }
    }
}
