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

class Index extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        $type = input('type');
        $BbsData = BbsModel::where('id', $type)->find();
        $typeName = $BbsData->name;
        $founderData = $BbsData->founder;
        $admin1Data = $BbsData->admin1;
        $admin2Data = $BbsData->admin2;

        $UserData = UserModel::where('id', 'in', "{$founderData},{$admin1Data},{$admin2Data}")->field('id,nickname,face')->select();
//        dump("{$founder},{$admin1},{$admin2}");
        for ($i = 0; $i < count($UserData); $i ++) {
            if ($i == 0) {
                $founder = $UserData[$i];
            } else if ($i == 1) {
                $admin1 = $UserData[$i];
            } else {
                $admin2[] = $UserData[$i];
            }
        }

        if ($admin1Data == $founderData) {
            $admin1 = $founder;
        }

        if (empty($admin2)) {
            $admin2 = [];
        }

        return view()->assign([
            'title' => '管理员',
            'description' => '',
            'type' => $type,
            'typeName' => $typeName,
            'founder_id' => $founder->id,
            'founder_nickname' => $founder->nickname,
            'admin1_id' => $admin1->id,
            'admin1_nickname' => $admin1->nickname,
            'admin2' => $admin2,
        ]);
    }

}