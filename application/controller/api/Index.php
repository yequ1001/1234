<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/7/11
 * Time: 20:18
 */

namespace app\controller\api;

use app\common\controller\BaseController;
use app\common\facade\User;
use think\facade\Session;

class Index extends BaseController
{
    /**
     * 腾讯api
     * 网址：https://007.qq.com/captcha/#/gettingStart
     */
    public function tencentCaptcha()
    {
        // 确定参数
        $ticket     = input('ticket');
        $ip         = User::ip();
        $randstr    = input('randstr');

        // 链接api地址并获取返回数据
        $url = "https://ssl.captcha.qq.com/ticket/verify?aid=2003609756&AppSecretKey=0tk7WDl5hcb8Xe_u4fJEBYA**&Ticket={$ticket}&UserIP={$ip}&Randstr={$randstr}";
        $html = file_get_contents($url);
        $html = json_decode($html);

        // 验证判断
        if ($html->response == 1) {
            Session::set('captcha', 'OK');
            return true;
        } else {
            return $html->err_msg;
        }
    }
}