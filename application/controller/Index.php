<?php
namespace app\controller;

use app\common\controller\BaseController;
use think\facade\Cache;
use app\common\model\Article as ArticleModel;
use app\common\facade\User;
use app\common\facade\Str;
use think\facade\Env;

class Index extends BaseController
{
    public function index()
    {
        Cache::remember('articleList'.input('page'), function(){
            return $this->read();
        }, 3600);
        $this->assign('list', Cache::get('articleList'.input('page')));

        // 删除安全码
        session('password2', null);
        // 清除强制登录的上级页面纪录
        session('restore', null);

        /* 渲染视图 */
        return view()->assign([
            'title' => '勤话微日记 - op112.com',
            'description' => '国内首创的微日记、考勤功能型网站，10个字也可以写日记的便携平台。',
            'userType' => User::type()
        ]);
    }

    private function read()
    {
        $list = ArticleModel::where('id', '>', 0)->order('id', 'desc')->paginate(15)->each(function($item, $key){
            // 删除文章内容中的html标签
            $item['content'] = preg_replace('#<[^>]+>#ims', '', $item['content']);
            // 文章内容概括
            if (mb_strlen($item['content']) > 35) {
                $item['content'] = mb_substr($item['content'], 0, 35) .'..';
            }
            // 文章附图
            $imgArr = explode('|', $item['files']);
            $imgArr = array_filter($imgArr); // 删除数组中的空项目
            $item['files'] = '';
            $i = 1;
            foreach ($imgArr as $img_) {
                $item['files'] .= "<img src='/public/data/article/file/{$img_}' />";
                break;
            }
            return $item;
        });
        return $list;
    }
}
