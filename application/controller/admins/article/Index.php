<?php
namespace app\controller\admins;

use app\common\controller\BaseController;
use think\facade\Cache;
use app\common\model\Article as ArticleModel;

class Index extends BaseController
{
    public function index()
    {
        /* 渲染视图 */
        return view()->assign([
            'title' => '网站管理中心',
            'description' => '',
        ]);
    }

    public function save()
    {
        /* 接收用户表单数据 */
        $title      = input("title");
        $content    = input("content");
        $type       = input("type");
        $source     = input("source");

        /* 用户表单数据再加工 */
        $title      = trim($title);
        $content    = trim($content);
        $type       = trim($type);
        $source     = trim($source);

        /* 验证器 */
        $result = $this->validate(
            [
                'title'     => $title,
                'content'   => $content,
                'type'      => $type,
                'source'    => $source,
            ],
            'app\admins\validate\Article');
        if ($result !== true) {
            return $result;
        }

        /* 提取文章中图片 */
        preg_match_all('#(?<= src="/public/data/article/file/)\d{8}/\d{10}.[a-z]{3,5}#ims', $content, $fileArr);
        $files = implode('|', $fileArr[0]);

        /* 删除缓存，使浏览器及时更新界面 */
        Cache::rm($type);
        Cache::rm('articleList');
        Cache::rm('articleList1');

        /* 保存数据 */
        ArticleModel::create([
            'title'     => $title,
            'content'   => $content,
            'type'      => $type,
            'time'      => date('Y-m-d H:i:s'),
            'source'    => $source,
            'files'     => $files,
        ]);

        return 'OK';
    }
}
