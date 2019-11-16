<?php
namespace app\controller\user;

use app\common\controller\BaseController;
use think\facade\View;
use app\common\facade\User;

class Info extends BaseController
{
    public function index()
    {
		/**
         * 如果用户没有登陆则跳出当前页面
         */
        User::login();
		
        $userData       = User::data();

        //渲染视图
        View::assign([
            'title'         => '我的资料',
            'description' => '',
            'id'            => User::id(),
            'username'      => $userData->username,
            'nickname'      => $userData->nickname,
            'face'          => $userData->face,
            'regTime'       => $userData->reg_time,
            'lv'            => User::lv($userData->days),
            'days'          => $userData->days,
            'loginTime'     => $userData->log_time,
            'autograph'     => $userData->autograph,
            'mobi'          => $userData->mobi,
        ]);
        return View::fetch();
    }
}