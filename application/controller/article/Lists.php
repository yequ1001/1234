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
use app\common\controller\BaseController;
use app\common\model\Article as ArticleModel;

class Lists extends BaseController
{
    public function index()
    {

        $type = input('type');
        Cache::remember($type, function(){
            return $this->read(input('type'));
        }, 86400);
        $this->assign('list', Cache::get($type));

        switch ($type) {
            case 'aquatic':
                $title = '水生';
                break;
            case 'flower':
                $title = '花卉';
                break;
            case 'succulent':
                $title = '多肉';
                break;
            case 'leaf':
                $title = '绿植';
                break;
        }
        /* 渲染视图 */
        return view()->assign([
            'title'         => '养植手册 - '. $title .'类',
            'description' => '',
        ]);
    }

    private function read($type)
    {
        $list = ArticleModel::where('type', $type)->order('id', 'desc')->paginate(10)->each(function($item, $key){
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
                if (count($imgArr) <= 2 || $i >= 3) {
                    break;
                }
                $i ++;
            }
            return $item;
        });
        return $list;
    }
}