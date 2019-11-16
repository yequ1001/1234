<?php

namespace app\common\behavior;

use think\facade\Request;
use app\common\model\User as UserModel;
use app\common\facade\User;

/**
 * 当前行为绑定到系统预设钩子：操作开始执行
 * 当前行为更新当前会员的最新在线时间
 */
class Online
{
    public function run()
    {
        if(User::type() == 'member'){
            UserModel::where('id', User::id())->update([
                'final_time'    => date('Y-m-d H:i:s'),
                'final_system'  => User::system(),
                'final_page'    => Request::Url(),
            ]);
        }
    }
}