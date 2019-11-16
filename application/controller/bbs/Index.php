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

class Index extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        $BbsData = BbsModel::where('id', '>', '0')->limit(30)->orderRand()->select();
        $BbsMainData = BbsModel::where('id', '>', '0')->limit(6)->order('article', 'desc')->select();
        return view()->assign([
            'title'         => '勤话交流社区',
            'description' => '',
            'BbsData' => $BbsData,
            'BbsMainData' => $BbsMainData
        ]);
    }

}