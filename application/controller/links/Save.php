<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/30
 * Time: 10:41
 */

namespace app\controller\links;

use app\common\controller\BaseController;
use app\common\model\Links as LinksModel;
use app\common\facade\User;

class Save extends BaseController
{
    public function index()
    {
        /**
         * 视图渲染
         */
        return view()->assign([
            'title'         => '申请加入',
            'description' => '',
        ]);
    }

    public function save()
    {
        $name           = trim(input('name'));
        $type           = trim(input('type'));
        $url            = trim(input('url'));
        $mobi           = trim(input('mobi'));
        $captcha        = trim(input('captcha'));

        // 验证器
        $result = $this->validate(
            [
                'name'          => $name,
                'type'          => $type,
                'url'           => $url,
                'mobi'          => $mobi,
                'captcha'       => $captcha,
            ],
            'app\validate\Links');

        // 验证结果
        if ($result !== true) {
            return $result;
        }

        // 变量加工：剔除网址开头的https: 或 http:
        $url = preg_replace('#^https?://#ims', '', $url);
        $url = preg_replace('#^//#ims', '', $url);
        $url = preg_replace('#/$#ims', '', $url);

        // 查询这个网址是否已被加入黑名单
        preg_match('#^[^/]+#ims', $url, $domain);
        $LinksModel = LinksModel::where('url', 'like', $domain[0] .'%')->where('state', -1)->find();
        if ($LinksModel) {
            return '该域名已列入黑名单，请联系网站管理员';
        }

        $linksData = LinksModel::create([
            'name'          => $name,
            'type'          => $type,
            'url'           => $url,
            'mobi'          => $mobi,
            'state'         => 1,
            'user'          => User::id(),
            'time'          => date('Y-m-d H:i:s'),
        ]);

        cache('LinksData', null);

        return $linksData->id;
    }
}