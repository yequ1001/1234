<?php
namespace app\controller\bbs;

use think\DB;
use think\facade\Validate;
use think\facade\Env;
use app\common\controller\BaseController;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\model\BbsArticle as BbsArticleModel;
use app\common\model\BbsComment as BbsCommentModel;
use app\common\model\BbsBlacklist as BbsBlacklistModel;

class Update extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        User::login();

        $id = input('id');
        $BbsData = BbsArticleModel::get($id);
        $title = $BbsData->title;
        $content = $BbsData->content;

        return view()->assign([
            'title'         => '修改',
            'description' => '',
            'id' => $id,
            'inputTitle' => $title,
            'inputContent' => $content,
        ]);
    }

    /**
     * 更新数据
     */
    public function save()
    {
        /**
         * 如果用户没有登陆则跳出当前页面
         */
        User::login();

        $id = input('id');
        $title = input('title');
        $content = input('content');

        // 数据验证
        $validate = [
            'title'         =>$title,
            'content'       => $content,
        ];
        Validate::rule([
            'title|标题'          => 'require|max:32',
            'content|内容'        => 'require|length:2,30000',
        ]);
        if(!Validate::check($validate)){
            return Validate::getError();
        }

        // 屏蔽敏感词
        $title   = Str::filter($title);
        $content = Str::filter($content);
        if (empty($bind)) {
            $bind = null;
        }

        // 验证是否当前用户
        $user = BbsArticleModel::where('id', $id)->field('user')->find();
        if (User::id() != $user->user) {
            return '您没有权限修改';
        }

        BbsArticleModel::where('id', $id)
            ->update([
                'title' => $title,
                'content' => $content,
                'updateTime' => date('Y-m-d H:i:s'),
            ]);

        return true;
    }

    /**
     * 删除数据
     */
    public function del()
    {
        $id = input('id');
        $object = input('object');
        if (empty($object)) {
            $object = $id;
        }

        // 验证是否当前用户
        $user = BbsArticleModel::where('id', $id)->field('user')->find();
        if (User::id() != $user->user) {
            return '您没有权限删除';
        }

        // 删除帖子正文
        $bbsData = BbsArticleModel::where('id', $id)->whereOr('bind', $id)->select();
        // 不一定有帖子，如果有，则删除
        if ($bbsData) {
            foreach ($bbsData as $bbs) {
                $id = $bbs->id;
                $type = $bbs->type;
                $bind = $bbs->bind;
                if (!empty($bind)) {
                    BbsArticleModel::where('id', $bind)
                        ->update([
                            'is_book' => ['inc', -1]
                        ]);
                }
                $path = Env::get('root_path') . "_data/bbs/{$type}/{$id}/";
                if (!empty($bbs->imgArr)) {
                    $imgArr = explode('|', $bbs->imgArr);
                    foreach ($imgArr as $img) {
                        if (file_exists($path . $img)) {
                            unlink($path . $img);
                        }
                    }
                    @rmdir($path); // 如果当前文件夹空了，则删除
                }
                $bbs->delete();
            }
        }

        $bbsCommentData = BbsCommentModel::where('object', $object)->whereOr('bind', $id)->select();
        // 不一定有评论，如果没有，直接返回true
        if (!$bbsCommentData) {
            return true;
        }
        foreach ($bbsCommentData as $bbsComment) {
            $bind = $bbsComment->bind;
            $type = $bbsComment->type;
            $path = Env::get('root_path') ."_data/bbs/{$type}/{$bind}/";
            if (!empty($bbsComment->imgArr)) {
                $imgArr = explode('|', $bbsComment->imgArr);
                foreach ($imgArr as $img) {
                    if (file_exists($path . $img)) {
                        unlink($path . $img);
                    }
                }
                @rmdir($path); // 如果当前文件夹空了，则删除
            }
            $bbsComment->delete();
        }

        return true;
    }
}