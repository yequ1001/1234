<?php
/**
 * Created by PhpStorm.
 * User: YeQu1001
 * Date: 2019/7/10
 * Time: 18:10
 */

namespace app\controller\admins;

use app\common\controller\BaseController;
use app\common\facade\User;
use app\common\facade\Str;
use app\common\model\UserOnline as UserOnlineModel;


class Index extends BaseController
{
    public function index()
    {
        User::login();

        // 实时在线人数统计
        $online_current = UserOnlineModel::where('time_end', '>=', date('Y-m-d H:i:s', strtotime( '-2 Minute', strtotime(date('Y-m-d H:i:s')))))->count();
        // 1小时在线统计
        $online_1h = UserOnlineModel::where('time_end', '>=', date('Y-m-d H:i:s', strtotime( '-60 Minute', strtotime(date('Y-m-d H:i:s')))))->count();
        // 今日访问量UV
        $online_today = UserOnlineModel::where('time_start', 'like', date('Y-m-d') .'%')->count();

        /* 渲染视图 */
        return view()->assign([
            'title' => '勤话管理中心 - 机密',
            'description' => '',
            'online_current' => $online_current,
            'online_1h' => $online_1h,
            'online_today' => $online_today,
        ]);
    }
}