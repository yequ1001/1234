<?php

namespace app\controller\user;

use app\common\controller\BaseController;
use think\facade\Validate;
use app\common\model\User as UserModel;
use app\common\facade\User;

class Autograph extends BaseController
{
    public function index()
    {
        /**
         * 如果用户没有登陆则跳出当前页面
         */
        User::login();

        /**
         * 获取会员信息
         */
        $userData   = User::data();
        $autograph  = $userData->autograph;
        $face       = $userData->face;

        /**
         * 渲染视图
         */
        return view()->assign([
            'autograph' => $autograph,
            'description' => '',
            'title'     => '我的签名',
            'face'      => $face,
        ]);
    }

    /**
     * 用户改签名
     */
    public function save()
    {
        $newAutograph = input("newAutograph");
        $newAutograph = trim($newAutograph);

        /**
         * 验证签名是否符合规则
         */
        Validate::rule([
            'newAutograph|签名' => 'max:50',
        ]);

        $form = [
            'newAutograph' => $newAutograph,
        ];

        if (!Validate::check($form)) {
            return Validate::getError();
        }

        /**
         * 变量再加工
         */
        $newAutograph = htmlspecialchars($newAutograph);

        /**
         * 数据库用户名字更新并返回信息
         */
        $update = UserModel::where('id', User::id()) ->update([
            'autograph' => $newAutograph
        ]);
        if ($update) {
            return true;
        } else {
            return '出错了';
        }
    }
}