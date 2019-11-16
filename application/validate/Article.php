<?php
/**
 * Created by PhpStorm.
 * User: YEQU1001
 * Date: 2019/4/21
 * Time: 12:39
 */
namespace app\validate;

use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'title|文章标题'     => 'require|length:2,42',
        'content|文章正文'   => 'require|length:10,100000',
        'type|文章类型'      => 'require|length:3,10',
        'type|文章类型'      => ['regex'=>'/^(aquatic|flower|succulent|leaf)$/i'],
        'source|文章来源'    => 'require|length:2,60',
    ];
}