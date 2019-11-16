<?php

namespace app\controller\user;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Validate;
use app\common\model\User as UserModel;
use app\common\facade\Str;
use app\common\facade\User;

class Search extends BaseController
{
    public function index()
    {
        //渲染视图
        View::assign([
            'title' => '搜索用户',
            'description' => '',
            'face' => User::face(),
        ]);
        return View::fetch();
    }

    public function select()
    {
        $keyWord = trim(input("keyWord"));
        $keyType = trim(input("keyType"));
        // ID1000是系统账号，不允许被搜索
        if ($keyType == 'id') {
            if(!empty($keyWord)) {
                $userData = UserModel::where('id', $keyWord)
                    ->where('id','<>', '1000')
                    ->field('id,nickname,face,autograph,days')
                    ->limit(35)
                    ->order('final_time DESC')
                    ->select();
            } else {
                $userData = UserModel::where('id', '>', 1000)
                    ->field('id,nickname,face,autograph,days')
                    ->limit(35)
                    ->order('final_time DESC')
                    ->select();
            }
        } else {
            $userData = UserModel::where('nickname', 'LIKE', "%{$keyWord}%")
                ->where('id','<>', '1000')
                ->field('id,nickname,face,autograph,days')
                ->limit(30)
                ->select();
        }

        $userData = $userData->each(function($item, $key){
            $item->days = User::lv($item->days);
        });

        return json($userData);
    }
}