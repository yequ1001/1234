<?php
namespace app\controller\bbs\executive;

use think\DB;
use think\facade\Env;
use think\facade\Validate;
use app\common\controller\BaseController;
use app\common\facade\File;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\model\User as UserModel;
use app\common\model\Bbs as BbsModel;
use app\common\model\BbsArticle as BbsArticleModel;

class Links extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        $type = input('type');
        $BbsData = BbsModel::where('id', $type)->find();
        $typeName = $BbsData->name;

        $links_name = $BbsData->links_name;
        $links_url = $BbsData->links_url;

        $links_name_arr = explode('|', $links_name);
        $links_url_arr = explode('|', $links_url);

        for ($i = 0; $i < count($links_name_arr); $i ++) {
            $links_arr[] = [
                'name'=> $links_name_arr[$i],
                'url'=> $links_url_arr[$i],
            ];
        }

        return view()->assign([
            'title' => '拓展链接管理',
            'description' => '',
            'links_arr' => $links_arr,
            'type' => $type,
            'typeName' => $typeName,
        ]);
    }

    public function save()
    {
        $id = input('type');
        $links_name = input('name');
        $links_url = input('url');

        $links_name = substr($links_name, 0, -1);
        $links_url = substr($links_url, 0, -1);

        if (substr_count($links_name, '|') != 11 || substr_count($links_url, '|') != 11) {
            return '非法操作';
        }

        BbsModel::where('id', $id)
            ->update([
                'links_name' => $links_name,
                'links_url' => $links_url,
            ]);
        return true;
    }
}