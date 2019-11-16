<?php

namespace app\controller\bbs;

use app\common\controller\BaseController;
use app\common\model\BbsArticle as BbsArticleModel;
use app\common\model\BbsComment as BbsCommentModel;
use app\common\facade\File;
use app\common\facade\Str;
use app\common\facade\User;

class Read extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        $id = input('id');
        $page = input('page');
        $BbsArticleData = BbsArticleModel::get($id);
        if (!$BbsArticleData) {
            $this->to404();
        }
        $title = $BbsArticleData->title;
        // 给标题附带标徽
        if (!empty($BbsArticleData->is_top)) {
            $biao = ' <span class="layui-badge layui-bg-orange" style="float:right"> &nbsp;置顶&nbsp; </span>';
        } else if (!empty($BbsArticleData->is_essence)) {
            $biao = ' <span class="layui-badge" style="float:right"> &nbsp;推荐&nbsp; </span>';
        } else {
			$biao = '';
		}
        if (empty($page) || $page == 1) {
            $content = $BbsArticleData->content;
        } else {
            $content = '';
        }
        $nickname = $BbsArticleData->nickname;
		$face = $BbsArticleData->face;
        $time = Str::simpleTime($BbsArticleData->time);
        $bind = $BbsArticleData->bind;
        $type = $BbsArticleData->type;
        $click = $BbsArticleData->click;
        $reply = $BbsArticleData->reply;
        $typeName = $BbsArticleData->type_name;
        if (empty($bind)) {
            $bind = $BbsArticleData->id;
        }
		
		$userData = User::data($BbsArticleData->user);
		$autograph = $userData->autograph;

        // 更新浏览量
        $BbsArticleData->click = ['inc', 1];
        $BbsArticleData->save();

        // 获取连载 和 目录
        $serializeData = BbsArticleModel::where('id', $id)->whereOr('bind', $bind)->whereOr('id', $bind)->order('id', 'asc')->select();
        $serializeId = '';
        $serializeTitle = '';
        $bool = true;
        foreach ($serializeData as $serialize) {
            // 获取连载
            if ($serialize->id > $BbsArticleData->id && $bool) {
                $serializeId = $serialize->id;
                $serializeTitle = $serialize->title;
                $bool = false;
            }
        }

        // 连载数量
        $serializeCount = count($serializeData);

        // 获取回顾
        $reviewId = '';
        $reviewTitle = '';
        foreach ($serializeData as $review) {
            if ($review->id >= $BbsArticleData->id) {
                break;
            }
            $reviewId = $review->id;
            $reviewTitle = $review->title;
        }

        if (empty($reviewId) && input('id') != $bind) {
            $r = BbsArticleModel::get($bind);
            $reviewId = $r->id;
            $reviewTitle = $r->title;
        }

        // 返回评论
        $BbsCommentData = BbsCommentModel::where('bind', $id)->paginate(16)->each(function($data, $key){
            $data->time = Str::simpleTime($data->time);
            if ($data->delete) {
                $data->content = '<small style=\'color: #8E8E8E\'>[ 评论已删除 ]</small>';
            }
        });

        return view()->assign([
            'description' => '',
            'id' => $BbsArticleData->id,
            'bind' => $bind,
            'user' => $BbsArticleData->user,
            'author' => $BbsArticleData->user,
            'nickname' => $nickname,
			'autograph' => $autograph,
			'face' => $face,
            'title' => $title,
            'title_short' => mb_substr($title, 0, 6) .'..',
            'content'  => $content,
            'time' => $time,
            'type' => $type,
            'typeName' => $typeName,
            'faceList' => File::face(),
            'click' => $click,
            'reply' => $reply,
			'biao' => $biao,
            // 回看
            'reviewId' => $reviewId,
            'reviewTitle' => $reviewTitle,
            // 连载
            'serializeCount' => $serializeCount,
            'serializeId' => $serializeId,
            'serializeTitle' => $serializeTitle,
            'serializeData' => $serializeData,
            // 评论
            'BbsComment' => $BbsCommentData,
            'empty' => '<br/>　暂无评论，快来抢楼吧！<br/><br/>',
            // 当前用户
            'currentUser' => User::id()
        ]);
    }
}