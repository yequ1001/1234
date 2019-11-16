<?php
/**
 * Created by PhpStorm.
 * User: Yequ1001
 * Date: 2019/7/3
 * Time: 21:34
 */

namespace app\controller\depot;

use app\common\controller\BaseController;

class Experience extends BaseController
{
    public function index()
    {
        return view()->assign([
            'title'         => '网站历程',
            'description' => '',
        ]);
    }

}