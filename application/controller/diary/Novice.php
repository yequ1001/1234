<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/7/25
 * Time: 22:42
 */

namespace app\controller\diary;

use app\common\controller\BaseController;
use app\common\facade\User;
use app\common\model\Diary as DiaryModel;

class Novice extends BaseController
{
    public function index()
    {
        if (User::type() == 'member') {
            return $this->redirect('/diary/month');
        }

        /* 渲染视图 */
        return view()->assign([
            'title'         => '新手教程',
            'description' => '',
        ]);
    }
}