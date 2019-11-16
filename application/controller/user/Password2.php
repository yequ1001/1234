<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/22
 * Time: 21:10
 */

namespace app\controller\user;

use app\common\controller\BaseController;
use app\common\model\User as UserModel;
use think\facade\Validate;
use app\common\facade\User;

class Password2 extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        /**
         * 如果用户没有登陆则跳出当前页面
         */
        User::login();

        /* 渲染视图 */
        return view()->assign([
            'title' => '安全密码',
            'description' => '',
        ]);
    }

    /**
     * 更新密码
     */
    public function save()
    {
        // 将表单信息写入当前类变量$this->user_info_arr
        $this->user_info_arr = [
            'password'          => input('password'),
            'password2'          => input('password2'),
        ];

        // 建立表单验证规则
        Validate::rule([
            'password|登录密码'    => 'alphaDash|min:4|max:11',
            'password2|安全码'     => 'alphaDash|min:0|max:6',
        ]);

        // 表单验证
        if(!Validate::check($this->user_info_arr)){
            return Validate::getError();
        }

        // 安全码加工
        if (!empty($this->user_info_arr['password2'])) {
            $this->user_info_arr['password2'] = sha1($this->user_info_arr['password2']);
        } else {
            $this->user_info_arr['password2'] = null;
        }

        // 查询用户登录密码是否匹配
        $password = UserModel::where('id', User::id())->field('password')->find();
        if ($password->password != sha1($this->user_info_arr['password']) && $password->password != null){
            return '用户登录密码错误';
        } else {
            // 更新数据库
            UserModel::where('id', User::id())
                ->update([
                    'password2'         => $this->user_info_arr['password2'],
                    'password2_time'    => date('Y-m-d H:i:s')
                ]);
        }

        return true;
    }
}