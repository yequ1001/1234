<?php
/**
 * Created by PhpStorm.
 * User: YEQU1001
 * Date: 2019/5/4
 * Time: 20:01
 */

namespace app\admins\controller\article;

use app\common\controller\BaseController;
use think\facade\Cache;
use app\common\model\Article as ArticleModel;

class Update extends BaseController
{
    public function index()
    {
        /* 渲染视图 */
        return view()->assign([
            'title' => '文章修改',
            'description' => '',
        ]);
    }

    public function read()
    {
        $articleData = ArticleModel::get(input('id'));
        return json($articleData);
    }

    public function update()
    {
        /* 提取文章中图片 */
        preg_match_all('#(?<= src="/public/data/article/file/)\d{8}/\d{10}.[a-z]{3,5}#ims', input('content'), $fileArr);
        $files = implode('|', $fileArr[0]);

        ArticleModel::where('id', input('id'))
            ->update([
                'title'     => input('title'),
                'type'      => input('type'),
                'content'   => input('content'),
                'source'    => input('source'),
                'files'     => $files
            ]);
        return 'OK';
    }
}