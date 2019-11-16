<?php

namespace app\common\behavior;

use think\facade\Session;
use think\facade\Cookie;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\facade\CookieExtend;

/**
 * 当前行为绑定到系统预设钩子：应用初始化
 * 当前行为给每一位访客生成一个身份
 */
class Identity
{
    public function run()
    {
        /**
         * 生成访客ID
         * 无论是会员还是游客都会生成访客标识
         */
        list($usec, $sec) = explode(' ', microtime());
        $str1 = str_shuffle('QWERTYUPASDFGHJKLZXCVNM');
        $str2 = str_shuffle('0123456789');
        $str3 = ((float)$usec + (float)$sec) * 10000;
        $id = substr($str1, 0, 1) . $str3 . substr($str1, 0, 2) . substr($str2, 0, 3);

        /**
         * 配置Cookie，初始化
         * 前缀 和 过期时间（30天）
         */
        Cookie::init(['prefix' => 'user_', 'expire' => 60 * 60 * 24 * 30]);

        /**
         * 将访客信息输出到客户端Cookie
         */
        if (!Cookie::has('id')){
            Cookie::set('id', $id);
            Cookie::set('key', Str::cookieKey([$id]));
        }

        /**
         * Cookie防篡改验证
         */
        else {
            $str1 = Cookie::get('key','user_');
            $str2 = Str::cookieKey([Cookie::get('id','user_')]);
            if ($str1 != $str2){
                Cookie::clear('user_');
                exit('Cookie被篡改，网站拒绝当前访问');
            }
        }

        /**
         * 在TP框架中，如果同名Session存在，set是不会重新创建Session的
         */
//        Session::set('user', $id);

        /**
         * 访问统计
         */
        if (CookieExtend::has()) {
            User::setOnline();
        }
    }
}