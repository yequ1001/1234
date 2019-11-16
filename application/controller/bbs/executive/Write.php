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

class Write extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        $type = input('type');
        $BbsData = BbsModel::where('id', $type)->find();
        $typeName = $BbsData->name;
        $authority_write = $BbsData->authority_write;
        $authority_reply = $BbsData->authority_reply;

        return view()->assign([
            'title'         => '对话设置',
            'description' => '',
            'type' => $type,
            'typeName' => $typeName,
            'authority_write' => $authority_write,
            'authority_reply' => $authority_reply,
        ]);
    }

    public function update()
    {
        $type = input('type');
        $authority_write = input('authority_write');
        $authority_reply = input('authority_reply');
        BbsModel::where('id', $type)
            ->update([
                'authority_write' => $authority_write,
                'authority_reply' => $authority_reply,
            ]);

        return true;
    }
}