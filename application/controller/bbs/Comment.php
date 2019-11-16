<?php

namespace app\controller\bbs;

use think\DB;
use think\facade\Env;
use think\facade\Validate;
use app\common\controller\BaseController;
use app\common\facade\File;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\model\Bbs as BbsModel;
use app\common\model\BbsArticle as BbsArticleModel;
use app\common\model\BbsComment as BbsCommentModel;
use app\common\model\BbsBlacklist as BbsBlacklistModel;
use app\common\model\User as UserModel;

class Comment extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {

        return view()->assign([
            'title'         => '评论',
            'description' => '',
        ]);
    }

    public function save()
    {
        /**
         * 如果用户没有登陆则跳出当前页面
         */
        User::login();

        $object = input('object');
        $bind = input('id');
        $content = input('content');
        $type = input('type');
        $author = input('author');
        $at = input('at');
        $pageUrl = input('pageUrl');
        $pageTitle = input('pageTitle');

        // 查询当前用户是否被拉入黑名单
        $BbsBlacklistData = BbsBlacklistModel::where('user', User::id())
            ->where('type', $type)
            ->find();

        if (!empty($BbsBlacklistData)) {
            $timespan = strtotime($BbsBlacklistData->expire) - strtotime(date('Y-m-d H:i:s'));
            if ($timespan > 0) {
                $s = $timespan;
                $m = floor($s/60);
                $h = floor($m/60);
                $d = floor($h/24);
                if ($d >= 1) {
                    $timespan = $d .'天';
                } elseif ($h >= 1) {
                    $timespan = $h .'小时';
                } elseif ($m >= 1) {
                    $timespan = $m .'分钟';
                } else {
                    $timespan = $s .'秒钟';
                }
                return '您被列入黑名单，距离解禁还有'. $timespan;
            }
        }

        // 是否已被手动关闭回复
        $BbsData = BbsModel::where('id', input('type'))->find();
        $authority_reply = $BbsData->authority_reply;
        $admin1 = $BbsData->admin1;
        if (($authority_reply == 1 || $authority_reply == 2) && User::id() != $admin1) {
            return '很抱歉，您没有权限';
        }

        if ($author == User::id()) {
            $author = 1;
        } else {
            $author = 0;
        }

        // 数据加工
		$content = preg_replace('#^(<div><br></div>)+|(<div><br></div>)+$#ims', '', $content);
        $content = preg_replace('#<span[^>]+>#ims', '', $content);
        $content = preg_replace('#</span>#ims', '', $content);
        $content = preg_replace('#<pre[^>]+>#ims', '', $content);
        $content = preg_replace('#</pre>#ims', '', $content);
        $content = preg_replace('#<p[^>]+>#ims', '', $content);
        $content = preg_replace('#</p>#ims', '', $content);
        $content = preg_replace('#<div[^>]+>#ims', '', $content);
        $content = preg_replace('#</div>#ims', '', $content);
        $content = preg_replace('#<table[^>]+>#ims', '', $content);
        $content = preg_replace('#</table>#ims', '', $content);


        // 数据验证
        $validate = [
            'content'       => $content,
        ];
        Validate::rule([
            'content|内容'        => 'require|length:1,30000',
        ]);
        if(!Validate::check($validate)){
            return Validate::getError();
        }

        // 使艾特生效
        preg_match_all('#<input class=("|\')input-auto-[^>]+>#ims', $content, $input_at);
        foreach ($input_at as $input) {
            $input_ = str_replace('onclick_temp=', 'onclick=', $input);
            $content = str_replace($input, $input_, $content);
        }

        // 屏蔽敏感词
        $content = Str::filter($content);

        // 将评论中的图形提取出来单独保存字段
        preg_match_all('#(?<="/_data/bbs/_temp/)\d{15}.[a-z]{3,4}(?=")#ims', $content, $imgArr);
        $imgStr = implode('|', $imgArr[0]);

        // 保存数据库
        $bbsComment = BbsCommentModel::create([
            'content' => $content,
            'user' => User::id(),
            'nickname' => User::nickname(),
            'face' => User::face(),
            'author' => $author,
            'time' => date('Y-m-d H:i:s'),
            'bind' => $bind,
            'object' => $object,
            'type' => $type,
            'imgArr' => $imgStr,
        ]);
        $type = $bbsComment->type;

        // 更新回复量（同时更新当前章的回复量和总回复量）
        // 超过40天，则帖子不再置顶
        $BbsArticleData = BbsArticleModel::where('id', $object)->whereOr('id', $bind)->find();
        if (Str::daySpan(date('Y-m-d'), substr($BbsArticleData->reply_time, 0, 10)) > 40) {
            BbsArticleModel::where('id', $object)->whereOr('id', $bind)
                ->update([
                    'reply' => ['inc', 1],
                ]);
        } else {
            BbsArticleModel::where('id', $object)->whereOr('id', $bind)
                ->update([
                    'reply' => ['inc', 1],
                    'reply_time' => date('Y-m-d H:i:s')
                ]);
        }

        // 艾特消息通知
        $c = preg_replace('#<input [^@]+#ims', '', $content);
        $c = preg_replace('#" readonly="readonly">#ims', '', $c);
        $c = preg_replace('#<img [^>]+>#ims', '<i>[img]</i>', $c);
        $at_arr = explode('|', $at);
        $at_arr = array_unique($at_arr); // 消除数组重复的元素
        $at_arr = array_filter($at_arr); // 消除数组为空的元素
        $mess = [];
        foreach ($at_arr as $at) {
            $mess[] = [
                'form' => '1000',
                'to' => $at,
                'content' => User::nickname() .'在帖子《<a onclick="window.parent.location.href=\''. $pageUrl .'\'">'. $pageTitle .'</a>》中谈到你！',
                'time' => date('Y-m-d H:i:s'),
                'state' => 0,
            ];
        }

        $Message = new \app\common\model\UserMessage;
        $Message->saveAll($mess);

        $str = implode(',', $at_arr);
        UserModel::where('id', 'IN', $str)
        ->update(['message'=> ['inc', 1]]);

        // 将帖子中的图片临时地址转换为正式地址
        $bbsComment->content = preg_replace('#"/_data/bbs/_temp/(\d{15}.[a-z]{3,4})"#ims', "\"/_data/bbs/{$type}/{$bind}/$1\"", $content);
        $bbsComment->save();

        // 将图形从临时文件夹中移动到正式文件夹里面
        foreach ($imgArr[0] as $img) {
            $imgName = $img;
            $path = Env::get('root_path') ."_data/bbs/{$type}/{$bind}/";
            if (!is_dir($path)) {
                File::mkdir($path);
            }
            $path .= $imgName;
            // 已经上传临时文件的路径
            $temp = Env::get('root_path') .'_data/bbs/_temp/'. $imgName;
            if (is_file($temp)) {
                if(!rename($temp, $path)) {
                    unlink($temp);
                }
            }
        }

        return true;
    }

    public function delete()
    {
        $BbsCommentData = BbsCommentModel::where('id', input('id'))->find();
        if ($BbsCommentData->user != User::id()) {
            return '您没有权限进行这个操作';
        }
        BbsCommentModel::where('id', input('id'))
            ->update([
                'delete' => 1,
                'delete_time' => date('Y-m-d H:i:s')
            ]);
        return true;
    }
}