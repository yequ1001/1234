<?php
/**
 * Created by PhpStorm.
 * User: YEQU1001
 * Date: 2019/4/21
 * Time: 12:39
 */
namespace app\validate;

use think\Validate;

class Links extends Validate
{
    protected $rule = [
        'name|网站全称'         => 'require|length:2,10',
        'url|链接地址'          => 'require|url|length:10,255',
        'mobi|手机号'           => '1\d{10}',
        'type|网站类型'         => 'require',
        'captcha|验证码'        => 'require|captcha',
    ];
}
