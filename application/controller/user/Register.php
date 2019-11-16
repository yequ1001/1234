<?php
namespace app\controller\user;

use app\common\controller\BaseController;
use app\common\model\User as UserModel;
use think\facade\Validate;
use app\common\facade\User;
use think\facade\Session;

class Register extends BaseController
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
         * 渲染视图
         */
        return view()->assign([
            'title' => '用户注册',
            'description' => '',
        ]);
    }

    /**
     * 用户提交注册表单的执行脚本，步骤如下.
     */
    public function create()
    {
        // 将表单信息写入当前类变量$this->user_info_arr
        $this->user_info_arr = [
            'username'          => input('username'),
            'nickname'          => input('nickname'),
            'password'          => input('password'),
            'password_confirm'  => input('password_confirm'),
            'mobi'              => input('mobi'),
            'captcha'           => input('captcha'),
        ];

        // 建立表单验证规则
        Validate::rule([
            'username|用户账号'         => 'require|[a-zA-Z]{1}[a-zA-Z0-9]{3,10}',
            'nickname|称呼'             => 'require|min:1',
//            'password|密码'             => 'require|alphaDash|confirm|min:4|max:11',
            'mobi|手机号码'             => 'require|mobile',
            'captcha|验证码'            => 'require|length:4|number',
        ]);

        // 表单验证，将验证结果（即bool值）写入变量$bool
        if (!Validate::check($this->user_info_arr)) {
            return Validate::getError();
        }

        // 验证用户表单手机号和session保存的手机号一致性
        if (session('mobi') != $this->user_info_arr['mobi']) {
            return '验证号码不一致';
        }
        session('mobi', null);

        // 查询用户账号是否存在，账号必须唯一
        if (UserModel::getByUsername($this->user_info_arr['username'])) {
            return '该账号已存在';
        }

        // 查询用户名称是否存在，名称必须唯一
        if (UserModel::getByNickname($this->user_info_arr['nickname'])) {
            return '该昵称已被占用';
        }

        // 查询手机号是否存在，手机号也必须唯一
        if (UserModel::getByMobi($this->user_info_arr['mobi'])) {
            return '该手机号已经注册';
        }

        // 手机验证码验证
        if (session('sms') != $this->user_info_arr['captcha']) {
            return '短信验证码输入不正确，请重新输入';
        }
        session('sms', null);

        // 往数据库写入新用户信息
        $UserData = UserModel::create([
            'username'      => $this->user_info_arr['username'],
            'nickname'      => $this->user_info_arr['nickname'],
            'password'      => sha1($this->user_info_arr['mobi']),
            'reg_time'      => date('Y-m-d H:i:s'),
            'final_time'    => date('Y-m-d H:i:s'),
            'face'          => User::face(),
            'mobi'          => $this->user_info_arr['mobi'],
        ]);

        // 返回写数据库提示
        User::announcement($UserData->id, '亲爱的'. $UserData->nickname .'，这是您人生首次登陆勤话网，<a onclick="window.parent.location.href=\'/diary/month\'">点我打开微日记</a>写下第一条记事吧！');
        return true;
    }

    /**
     * 手机验证码Api
     */
    public function sms()
    {
        /**
         * 三方api验证
         */
        if (Session::get('captcha') != 'OK') {
            Session::set('captcha', null);
            return '您还没有通过滑动拼图验证';
        }
        Session::set('captcha', null);

        // 手机号
        $mobi = input('mobi');
        session('mobi', $mobi); // 手机号存入session，注册时使用，而不是使用用户表单填写的手机号

        // 验证手机号格式
        $valid = preg_match('#^1\d{10}$#', $mobi);
        if (!$valid) {
            return '手机号格式不正确';
        }

        // 查询手机号是否存在，手机号必须唯一
        if (UserModel::getByMobi($mobi)) {
            return '该手机号已存在';
        }

        // 查询用户账号是否存在，账号必须唯一
        if (!preg_match('#[a-zA-Z]{1}[a-zA-Z0-9]{3,10}#', input('username'))) {
            return '用户名只能使用字母数字组合，且字母开头';
        }
        if (UserModel::getByUsername(input('username'))) {
            return '该账号已存在';
        }

        // 查询用户名称是否存在，名称必须唯一
        if (UserModel::getByNickname(input('nickname'))) {
            return '该昵称已被占用';
        }

        // 验证码
        $number = rand(1000,9999);

        // 验证码存入服务器，匹配用
        session('sms', $number);

        // 以下是第三方短信验证码api
        $host = "https://smsapi.api51.cn";
        $path = "/code/";
        $method = "GET";
        $appcode = "c459a773c26f4817ab4ba47bce9b1b6d";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "code={$number}&mobile={$mobi}";
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        return curl_exec($curl);
    }

    private function api51_curl($url,$data=false,$ispost=0,$appcode)
    {
        $headers = array();
        //根据阿里云要求，定义Appcode
        array_push($headers, "Authorization:APPCODE " . $appcode);
        array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");

        $httpInfo = array();

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'api51.cn' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 300 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 300);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        if (1 == strpos("$".$url, "https://"))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if($ispost)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $data );
            curl_setopt( $ch , CURLOPT_URL , $url );
        }
        else
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            if($data){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$data );

            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }

        }
        $response = curl_exec( $ch );

        if ($response === FALSE) {
            // echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }
}
