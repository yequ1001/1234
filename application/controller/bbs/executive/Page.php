<?php
namespace app\controller\bbs\executive;

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

class Page extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        $type = input('type');
        $BbsData = BbsModel::where('id', $type)->find();
        $typeName = $BbsData->name;

        return view()->assign([
            'title'         => '页面设计',
            'description' => '',
            'type' => $type,
            'typeName' => $typeName,
            'headerContent' => $BbsData->header,
            'footerContent' => $BbsData->footer,
        ]);
    }

    public function update()
    {
        $header = trim(input('header'));
        $footer = trim(input('footer'));
        $type = input('type');

        $header = $this->invalid($header);
        $footer = $this->invalid($footer);

        if (input('?post.header')) {
            BbsModel::where('id', $type)
                ->update([
                    'header' => $header
                ]);
        } else if (input('?post.footer')) {
            BbsModel::where('id', $type)
                ->update([
                    'footer' => $footer
                ]);
        }

        return true;
    }

    /*
     * 使部分html标签失效
     */
    private function invalid($str)
    {
        $str = str_ireplace('&lt;', '<', $str);
        $str = str_ireplace('&gt;', '>', $str);
        $str = preg_replace('#<img[^>]+>#ims', '', $str);
        $str = preg_replace('#<iframe[^>]+>#ims', '', $str);
        $str = str_ireplace('</iframe>', '', $str);
        $str = preg_replace('#<script[^>]+>#ims', '', $str);
        $str = str_ireplace('</script>', '', $str);
        $str = preg_replace('#<video[^>]+>#ims', '', $str);
        $str = str_ireplace('</video>', '', $str);
        $str = preg_replace('#<audio[^>]+>#ims', '', $str);
        $str = str_ireplace('</audio>', '', $str);
        $str = preg_replace('#<input[^>]+>#ims', '', $str);
        $str = str_ireplace('</input>', '', $str);
        $str = preg_replace('#<textarea[^>]+>#ims', '', $str);
        $str = str_ireplace('</textarea>', '', $str);
        $str = preg_replace('#<object[^>]+>#ims', '', $str);
        $str = str_ireplace('</object>', '', $str);
        $str = preg_replace('#<embed[^>]+>#ims', '', $str);
        $str = str_ireplace('</embed>', '', $str);
		
		$str = str_ireplace('font-size', '*', $str);
        return $str;
    }

}