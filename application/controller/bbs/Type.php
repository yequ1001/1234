<?php

namespace app\controller\bbs;

use app\common\controller\BaseController;
use app\common\model\Bbs as BbsModel;
use app\common\model\BbsArticle as BbsArticleModel;
use app\common\facade\Str;
use app\common\facade\User;

class Type extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        $type = input('type');

        // 查询论坛
        $BbsData = BbsModel::where('id', $type)->find();
        if (!$BbsData) {
            $this->to404();
        }

        // 显示拓展链接
        $links_name = $BbsData->links_name;
        $links_url = $BbsData->links_url;

        $links_name_arr = explode('|', $links_name);
        $links_url_arr = explode('|', $links_url);

        $links_arr = null;

        for ($i = 0; $i < count($links_name_arr); $i ++) {
            if (!empty($links_name_arr[$i])) {
                $links_arr[] = [
                    'name' => $links_name_arr[$i],
                    'url' => $links_url_arr[$i],
                ];
            }
        }

        // 是否只加载精华帖子
        $is_essence = input('essence', 0);

        // 点击量、回复等排行
        $order = input('order', 'reply_time');

        // 加载帖子
        $BbsArticleData = BbsArticleModel::where('type', $type)
            ->where('bind', null)
            ->where('is_essence', '>=', $is_essence)
            ->order("is_top desc, is_top_time desc, {$order} desc")
            ->paginate()
            ->each(function($data, $key){
                $data->time = Str::simpleTime($data->time);
                if (!empty($data->imgArr)) {
                    $imgArr = explode('|', $data->imgArr);
                    if (count($imgArr) < 3) {
                        if (mb_strlen($data->title) > 25) {
                            $data->title = mb_substr($data->title, 0, 20) .'...';
                        }
                        $data->imgArr = '<img src="/_data/bbs/'. $data->type .'/'. $data->id .'/'. $imgArr[0] .'" />';
                    } else {
                        $data->imgArr  = '<p class="p-img layui-col-xs4"><img src="/_data/bbs/'. $data->type .'/'. $data->id .'/'. $imgArr[0] .'" /></p>';
                        $data->imgArr .= '<p class="p-img layui-col-xs4"><img src="/_data/bbs/'. $data->type .'/'. $data->id .'/'. $imgArr[1] .'" /></p>';
                        $data->imgArr .= '<p class="p-img layui-col-xs4"><img src="/_data/bbs/'. $data->type .'/'. $data->id .'/'. $imgArr[2] .'" /></p>';
                    }
                } else {
                    $data->content = preg_replace('#<[^>]+>#ims', '', $data->content);
                    if (mb_strlen($data->content) > 25) {
                        $data->content = mb_substr($data->content, 0, 22) .'...';
                    }
                }
                if (!empty($data->is_book)) {
                    $data->title .= ' <span class="layui-badge layui-bg-green"> &nbsp;册'. $data->is_book .'&nbsp; </span>';
                }
                if (!empty($data->is_essence)) {
                    $data->title .= ' <span class="layui-badge"> &nbsp;推荐&nbsp; </span>';
                }
                if (!empty($data->is_top)) {
                    $data->title .= ' <span class="layui-badge layui-bg-orange"> &nbsp;置顶&nbsp; </span>';
                }
            });

        // 显示会员名称还是登陆注册链接
        if (User::type() == 'member') {
            $nickname = User::nickname();
        } else {
            $nickname = '<a href="/user/login">登陆/注册</a>';
        }

        return view()->assign([
            'title' => $BbsData->name,
            'description' => '',
            'list'  => $BbsArticleData,
            'type' => input('type'),
            'face' => User::face(),
            'nickname' => $nickname,
            'header' => $BbsData->header,
            'footer' => $BbsData->footer,
            'links' => $links_arr,
        ]);
    }
}