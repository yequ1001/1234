<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/16
 * Time: 16:57
 */

namespace app\controller\admins\online;

use app\common\controller\BaseController;
use app\common\facade\User;
use app\common\model\UserOnline as UserOnlineModel;

class Today extends BaseController
{
    public function index()
    {
        User::login();
        $UserOnlineData = UserOnlineModel::where('time_start', 'like', date('Y-m-d') .'%')
            ->order('time_start', 'desc')
            ->paginate(20)
            ->each(function($item, $key){
                // 在线状态
                $span = floor((strtotime(date('Y-m-d H:i:s'))-strtotime($item->time_end))/60);
                if ($span > 2) {
                    $item->time_end = '';
                } else {
                    $item->time_end = '<span class="layui-badge layui-bg-green">在线</span>';
                }
                // 获取用户名字
                $item->user = User::nickname($item->user);
            });

        /* 渲染视图 */
        return view()->assign([
            'title'     => '今日访客统计 - 机密',
            'description' => '',
            'UserOnlineData' => $UserOnlineData,
        ]);
    }
}