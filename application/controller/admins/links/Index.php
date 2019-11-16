<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/16
 * Time: 16:57
 */

namespace app\controller\admins\links;

use app\common\controller\BaseController;
use app\common\model\Links as LinksModel;
use think\facade\Env;
use app\common\facade\File;
use app\common\facade\Str;
use app\common\facade\User;

class Index extends BaseController
{
    public function index()
    {
        User::login();

        /* 渲染视图 */
        return view()->assign([
            'title'     => '外链管理',
            'description' => '',
            'all'       => $this->all(),
            'page'      => input('page'),
        ]);
    }


    /**
     * 返回所有的外链
     */
    public function all()
    {
        $LinksData = LinksModel::where('id', '>=', 0)->order('id desc')->paginate(8)->each(function($item, $key){
            if (empty($item->url_main)) {
                $item->url_main = 'http://'. $item->url;
            }
        });
        return $LinksData;
    }

    /**
     * 更新外链
     */
    public function update()
    {
        $url_main = input('url_main');

        $filename = Env::get('root_path'). '/runtime/cache/getWeb.txt';
        $getWeb = Str::getWeb($url_main);
        if (!empty($getWeb)) {
            $file = File::fopen($filename, $getWeb);
        } else {
            $file = File::fopen($filename, file_get_contents($url_main));
        }

        // 获取网站LOGO
        if (empty($getWeb)) {
            $str = $file;
        } else {
            $str = $getWeb;
        }
        preg_match('#<link[^>]+\.ico[^>]+>#ims', $str, $ico);
        if (empty($ico[0])) {
            preg_match('#<link[^>]+icon("|\')[^>]+>#ims', $str, $ico);
        }
        if (empty($ico[0])) {
            preg_match('#<link[^>]+("|\')icon[^>]+>#ims', $str, $ico);
        }
        if (empty($ico[0])) {
            $ico = '/public/static/img/default_ico.ico';
        } else {
            $ico = $ico[0];
            preg_match('#href ?= ?(\'|")[^"\']+(\'|")#ims', $ico, $href);
            preg_match('#(?<="|\')[^"\']+(?="|\')#ims', $href[0], $href);
            $ico = $href[0];
            if (substr($ico, 0, 4) != 'http') {
                if (substr($ico, 0, 2) != '//') {
                    preg_match('#^[^/]+//[^/]+#ims', $url_main, $host);
                    $ico = $host[0] .'/'. $ico;
                } else {
                    $ico = 'http:'. $ico;
                }
            }
        }
        $ico = preg_replace('#(?<!:)//#i', '/', $ico);

        if ($file) {
            $metaArr = get_meta_tags($filename);
        } else {
            return $file;
        }

        if (isset($metaArr['keywords'])) {
            $keywords = $metaArr['keywords'];
        } else {
            $keywords = '';
        }

        if (isset($metaArr['description'])) {
            $description = $metaArr['description'];
        } else {
            $description = '';
        }

        LinksModel::where('id', input('id'))
            ->update([
                'url_main' => $url_main,
                'keywords' => $keywords,
                'description' => $description,
                'logo' => $ico,
            ]);
        return true;
    }

    /**
     * 删除外链
     */
    public function del()
    {
        LinksModel::destroy(input('id'));

        /**
         * 删除缓存，否则删除的外链还在显示
         */
        cache('LinksData', null);

        return true;
    }

    /**
     * 设置推荐标徽
     */
    public function recommend()
    {
        $recommend = input('recommend');
        $recommend = explode('|', $recommend);
        $id = $recommend[1];
        $recommend = $recommend[0];
        if ($recommend == 'null') {
            $recommend = null;
        }
        LinksModel::where('id', $id)
            ->update(['recommend' => $recommend]);
        return true;
    }
}