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
use app\common\model\LinksUser as LinksUserModel;
use app\common\facade\User;
use app\common\facade\Str;
use think\facade\Cache;
use think\Validate;

class Index extends BaseController
{
    public function index()
    {
        /**
         * 视图渲染
         */
        return view()->assign([
            'title'         => '勤话网导航',
            'description' => '',
            'getReplyTime'  => $this->getReplyTime(),
            'getBlog'       => $this->getLinks('blog'),
            'getBBS'        => $this->getLinks('论坛'),
            'getArticle'    => $this->getLinks('文章'),
            'getNavigation' => $this->getLinks('导航'),
            'getOther'      => $this->getLinks('其他'),

            'my'            => $this->myRead(),
            'empty'         => '<p class="empty">空</p>',
        ]);
    }

    /**
     * 最新链入
     */
    public function getReplyTime()
    {
        $list = LinksModel::where('state', 1)
            -> where('reply', '>=', 0)
            -> order('reply_time', 'desc')
            ->paginate(8)
            ->each(function($item, $key){
                if ($item->description == '') {
                    $item->description = $item->keywords;
                }
                if ($item->description == '') {
                    $str = [
                        '一个不错的网站',
                    ];
                    $item->description = $str[mt_rand(0, count($str) -1)];
                }
            });
        return $list;
    }

    /**
     * 获取博客类外链
     */
    public function getLinks($type = null)
    {
        if (!empty(input('type'))) {
            $type = input('type');
        }
        switch ($type) {
            case 'blog':
                $type = '博客';
                break;
            case 'article':
                $type = '文章';
                break;
            case 'navigation':
                $type = '导航';
                break;
            case 'bbs':
                $type = '论坛';
                break;
            case 'other':
                $type = '其他';
                break;
        }

        // 读取博客类型的外链
        $BlogData = LinksModel::where('type', $type)->order('reply_time desc')->paginate(16)->each(function($item, $key){
            if ($item->description == '') {
                $item->description = $item->keywords;
            }
            if ($item->description == '') {
                $str = [
                    '一个不错的网站',
                ];
                $item->description = $str[mt_rand(0, count($str) -1)];
            }
        });
        return $BlogData;
    }

    /**
     * 跳转
     */
    public function go()
    {
        // 链出统计
        $LinksModel = LinksModel::get(input('id'));
        $LinksModel->click = ['inc', 1];
        $LinksModel->click_time = date('Y-m-d H:i:s');
        $LinksModel->save();

        return redirect('http://'. input('url'));
    }

    /**
     * 链入统计
     */
    public function reply()
    {
        $ip = User::ip();
        $LinksModel = LinksModel::get(input('id'));
        if ($LinksModel && $LinksModel->reply_ip != $ip) {
            $LinksModel->reply      = ['inc', 1];
            $LinksModel->reply_time = date('Y-m-d H:i:s');
            $LinksModel->reply_ip   = $ip;
            $LinksModel->save();
        }
        return redirect('/');
    }

    /**
     * 用户新增网址书签
     */
    public function mySave()
    {
        /**
         * 验证用户是否登录
         */
        if (User::type() != "member") {
            return '当前操作需要登录支持';
        }

        $title  = input('title');
        $url    = input('url');
        $time   = date('Y-m-d H:i:s');
        $user   = User::id();

        /**
         * 用户输入验证
         */
        $validate = Validate::make([
            'title|标题'  => 'length:0,50',
            'url|网址'    => 'require|url'
        ]);
        $data = [
            'title' => $title,
            'url'   => $url
        ];
        if (!$validate->check($data)) {
            return $validate->getError();
        }

        /**
         * 输入再加工
         */
        $title = htmlspecialchars($title);
        $title = Str::filter($title);
        $url   = preg_replace('#^https?://#i', '', $url);
        $url   = preg_replace('#/$#i', '', $url);

        /**
         * 查询当前用户已保存的网址数量，
         * 控制在50个数量以内
         */
        $LinksUserData = LinksUserModel::where('user', $user)->select();
        if (count($LinksUserData) >= 50) {
            return '最多只能保存50条记录';
        }

        /**
         * 保存数据库
         */
        LinksUserModel::create([
            'title'     => $title,
            'url'       => $url,
            'user'      => $user,
            'time'      => $time,
        ]);
        return true;
    }

    /**
     * 返回用户自定义书签
     */
    public function myRead()
    {
        $LinksUserData = LinksUserModel::where('user', User::id())
            ->order('id', 'desc')
            ->select()
            ->each(function($item, $key) {
                if ($item->title == '') {
                    $item->title = $item->url;
                }
            });
        return $LinksUserData;
    }

    /**
     * 更新链接统计
     */
    public function myClick()
    {
        $LinksUserData = LinksUserModel::get(input('id'));
        $LinksUserData->click = ['inc', 1];
        $LinksUserData->clickTime = date('Y-m-d H:i:s');
        $LinksUserData->save();
        return true;
    }

    /**
     * 更新链接内容
     */
    public function myUpdate()
    {
        $url = input('url');

        $url = preg_replace('#^https?://#i', '', $url);
        $url = preg_replace('#/$#i', '', $url);

        $LinksUserData = LinksUserModel::get(input('id'));
        $user = $LinksUserData->user;
        if ($user != User::id()) {
            return '您没有权限修改这个链接';
        }
        $LinksUserData->title = input('title');
        $LinksUserData->url = $url;
        $LinksUserData->save();
        return true;
    }

    /**
     * 删除链接
     */
    public function myDelete()
    {
        $id = input('id');
        $LinksUserData = LinksUserModel::get($id);
        $user = $LinksUserData->user;
        if ($user != User::id()) {
            return '您没有权限删除这个链接';
        }
        LinksUserModel::destroy($id);
        return true;
    }

    /**
     * 一键收藏
     */
    public function collect()
    {
        // 验证是否已经登录
        if (User::type() != 'member') {
            return 'login'; // 发送登录跳转指令
        }

        $title = input('title');
        $type = input('type');
        $url = input('url');
        $url = preg_replace('#^[^=]+=#ims', '', $url);
        $logo = input('logo');

        $type = htmlspecialchars($type);
        $title = htmlspecialchars($title);
        $url = htmlspecialchars($url);

        // 添加收藏
        if ($type == 'save') {
            LinksUserModel::create([
                'user' => User::id(),
                'title' => $title,
                'time' => date('Y-m-d H:i:s'),
                'url' => $url,
                'readOnly' => 1,
                'logo' => $logo,
            ]);
        }
        // 取消收藏
        else {
            LinksUserModel::where('url', $url)->delete();
        }

        // 查询该链接是否存在
        return true;
    }
}