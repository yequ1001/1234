<?php
/**
 * Created by PhpStorm.
 * User: YEQU1001
 * Date: 2019/4/21
 * Time: 18:16
 */

namespace app\controller\diary;

use think\Db;
use app\common\controller\BaseController;
use app\common\model\Diary as DiaryModel;

class Help extends BaseController
{
    public function index()
    {
        /* 渲染视图 */
        return view()->assign([
            'title'         => '拓展用法',
            'description' => '',
        ]);
    }


}