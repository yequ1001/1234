<?php
namespace app\controller\user;

use app\common\controller\BaseController;
use app\common\model\User as UserModel;
use think\facade\Validate;
use think\facade\Session;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\facade\CookieExtend;

class Login extends BaseController
{
    /**
     * 储存来自表单发送的用户信息，以数组形式保存
     */
    private $user_info_arr;

    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        /**
         * 如果用户已经登录则跳转到用户中心
         */
        if (User::type() == 'member') {
            return $this->redirect('/user');
        }

        /**
         * 验证码过期
         */
        Session::set('captcha', null);

        /**
         * 强制到访提示
         */
        if (session('restore') != null) {
            $help = '亲爱的当前操作需要登录支持';
        } else {
            $help = '　';
        }

        /**
         * 渲染视图
         */
        return view()->assign([
            'title' => '用户登录',
            'description' => '',
            'help'  => $help,
        ]);
    }

    /**
     * 用户提交注册表单的执行脚本，步骤如下.
     */
    public function read()
    {
        if (!CookieExtend::has()) {
            return '您的浏览器Cookie未开启';
        }

        // 将表单信息写入当前类变量$this->user_info_arr
        $this->user_info_arr = [
            'username'          => input('username'),
            'password'          => input('password'),
        ];

        // 建立表单验证规则
        Validate::rule([
            'username|账号'       => 'require|alphaDash|min:4|max:11',
            'password|密码'       => 'require|alphaDash|min:4|max:11',
        ]);

        // 表单验证，将验证结果（即bool值）写入变量$bool
        if(!Validate::check($this->user_info_arr)){
            return Validate::getError();
        }

        /**
         * 三方api验证
         */
        if (Session::get('captcha') != 'OK') {
            Session::set('captcha', null);
            return '你还没有通过验证';
        }

        /**
         * 验证码过期
         */
        Session::set('captcha', null);

        // 识别登录名是账号还是手机号，
        // 且查询该账号是否存在
        $str = substr($this->user_info_arr['username'], 0, 1);
        if (is_numeric($str)) {
            // 是手机号
            if (!preg_match('#^1\d{10}$#', $this->user_info_arr['username'])) {
                return '手机号不正确';
            }
            $userData = UserModel::getByMobi($this->user_info_arr['username']);
        } else {
            // 是用户名
            $userData = UserModel::getByUsername($this->user_info_arr['username']);
        }

        // 如果账号不存在
        if(!$userData) {
            return '不存在的账号';
        }

        // 查询用户密码是否匹配
        if( $userData->password != sha1( $this->user_info_arr['password'] ) ){
            return '密码错误';
        }

        // 验证成功，将用户信息写入Cookie
        $exDay = 7; // 登录有效期，单位 天
        cookie(['prefix' => 'user_', 'expire' => 60*60*24*$exDay]);
        cookie('id', $userData->id);
        cookie('key', Str::cookieKey([$userData->id]));

        // 更新用户的登录时间
        $userData->log_time = Date('Y-m-d H:i:s');
        $userData->system   = User::system();
        $userData->save();

        // 将登录时间写入session，用于：
        // 用户立即使用微日记时无需再次输入登录密码
        session('password_time', date('Y-m-d H:i:s'));

        if (session('restore') != null) {
            $restore = session('restore');
            // 清除登录前页面URL纪录
            session('restore', null);

            return "redirect:{$restore}";
        }
        return true;
    }
}
