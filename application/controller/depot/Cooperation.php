<?php
/**
 * Created by PhpStorm.
 * User: Yequ1001
 * Date: 2019/7/3
 * Time: 21:34
 */

namespace app\controller\depot;

use app\common\controller\BaseController;

class Cooperation extends BaseController
{
    public function index()
    {
        return view()->assign([
            'title'         => '合作',
            'description' => '',
        ]);
    }

}