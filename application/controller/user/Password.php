<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/22
 * Time: 19:37
 */

namespace app\controller\user;

use app\common\controller\BaseController;
use app\common\model\User as UserModel;
use think\facade\Validate;
use app\common\facade\User;

class Password extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        /**
         * 如果用户没有登陆则强制退出当前页面
         */
        User::login();

        /**
         * 渲染视图
         */
        return view()->assign([
            'title' => '登录密码',
            'description' => '',
        ]);
    }

    /**
     * 更新密码
     */
    public function save()
    {
        //步骤1：将表单信息写入当前类变量$this->user_info_arr
        $this->user_info_arr = [
            'password_0'          => input('password_0'),
            'password_1'          => input('password_1'),
            'password_2'          => input('password_2'),
        ];

        //步骤2：建立表单验证规则
        Validate::rule([
            'password_0|旧密码'    => 'require|alphaDash|min:4|max:11',
            'password_1|密码1'     => 'require|alphaDash|min:4|max:11',
            'password_2|密码2'     => 'require|alphaDash|min:4|max:11|confirm:password_1',
        ]);

        //步骤3：表单验证
        if(!Validate::check($this->user_info_arr)){
            return Validate::getError();
        }

        //步骤4：查询用户密码是否匹配
        $password = UserModel::where('id', User::id())->field('password')->find();
        if ($password->password != sha1($this->user_info_arr['password_0'])){
            return '旧密码错误';
        } else {
            // 最后一次验证
            if ($this->user_info_arr['password_0'] == $this->user_info_arr['password_1']) {
                return '新密码和旧的密码是相同的';
            }
            // 更新数据库
            UserModel::where('id', User::id())
                ->update([
                    'password'          => sha1($this->user_info_arr['password_1']),
                    'password_time'     => date('Y-m-d H:i:s')
                ]);
        }

        return true;
    }
}