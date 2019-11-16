<?php
/**
 * Created by PhpStorm.
 * User: YEQU1001
 * Date: 2019/4/21
 * Time: 18:16
 */

namespace app\index\controller\article;

use think\Db;
use think\facade\Cache;
use app\common\facade\User;
use app\common\controller\BaseController;
use app\common\model\Article as ArticleModel;
use app\common\model\ArticleReply as ReplyModel;

class Read extends BaseController
{
    public function index()
    {
        /* 从URL获取文章ID */
        $id = trim(input('id'));

        /* 读取数据库的信息 */
        $articleData = ArticleModel::where('id', $id)->find();
        $title      = $articleData->title;
        $content    = $articleData->content;
        $type       = $articleData->type;
        $source     = $articleData->source;
        $user       = $articleData->user;
        $clickNum   = $articleData->clickNum;
        $replyNum   = $articleData->replyNum;

        switch ($type) {
            case 'aquatic':
                $type_cn = '水生';
                break;
            case 'flower':
                $type_cn = '花卉';
                break;
            case 'succulent':
                $type_cn = '多肉';
                break;
            case 'leaf':
                $type_cn = '绿植';
                break;
        }

        /* 渲染视图 */
        $this->assign('article', $articleData);
        return view()->assign([
            'title'         => $title,
            'description' => '',
            'source'        => ($source == 'original')?'原创':'分享',
            'randRead'      => $this->rand_read(),
            'type_en'       => $type,
            'type_cn'       => $type_cn,
            'reply'         => $this->reply_read(),
        ]);
    }

    /**
     * 随机文章
     */
    private function rand_read()
    {
        // 不存在则将随机数据写入缓存后返回
        Cache::remember('articleDataRand',function(){
            $articleData = Db::query('select id,title from op_article ORDER BY RAND() LIMIT 5');
            $list = '<ul id="article_rand">';
            foreach ($articleData as $article) {
                $id = $article['id'];
                $title = $article['title'];
                $list .= "<li><a href='/article/{$id}'>{$title}</a></li>";
            }
            $list .= '</ul>';
            return $list;
        }, 3600);

        /* 浏览量加1 */
        ArticleModel::where('id', input('id'))->setInc('clickNum', 1);
        return Cache::get('articleDataRand');
    }

    /**
     * 保存新评论
     */
    public function reply_save()
    {
        $id         = input('id');
        $content    = input('content');
        $captcha    = input('captcha');

        $result = $this->validate(
            [
                'id' => $id,
                'content' => $content,
                'captcha' => $captcha,
            ],
            'app\index\validate\ArticleReply');
        if ($result !== true) {
            return $result;
        }

        ReplyModel::create([
            'user'      => User::id(),
            'article'   => $id,
            'time'      => date('Y-m-d H:i:s'),
            'content'   => $content,
        ]);

        /* 回复+1 */
        ArticleModel::where('id', input('id'))->setInc('replyNum', 1);
        return 'OK';
    }

    /**
     * 读取新评论
     */
    public function reply_read()
    {
        $replyData = ArticleModel::get(input('id'))->reply()->order('id', 'desc')->limit(3)->select();
        $replyData = $replyData->each(function($item, $key){
            $item['user'] = User::nickname($item['user']);
            return $item;
        });
        return $replyData;
    }
}