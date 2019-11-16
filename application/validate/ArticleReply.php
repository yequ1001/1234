<?php
/**
 * Created by PhpStorm.
 * User: YEQU1001
 * Date: 2019/4/21
 * Time: 12:39
 */
namespace app\validate;

use think\Validate;

class ArticleReply extends Validate
{
    protected $rule = [
        'id|文章id'           => 'require|number',
        'content|评论'        => 'require|length:1,120',
        'captcha|验证码'      => 'require|captcha',
    ];
}
