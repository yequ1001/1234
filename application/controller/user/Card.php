<?php
namespace app\controller\user;

use app\common\controller\BaseController;
use think\facade\View;
use app\common\model\User as UserModel;
use app\common\facade\Str;
use app\common\facade\User;

class Card extends BaseController
{
    public function index()
    {
        $id = input('id');
        // 判断ID类型，游客还是会员
        if (User::type($id) == 'member') {
            $userData = UserModel::get($id);
            if (!$userData) {
                return '不存在的用户';
            }
            $nickname       = $userData->nickname;
            $face           = $userData->face;
            $finalTime      = Str::simpleTime($userData->final_time);
            $finalSystem    = $userData->final_system;
            $regTime        = Str::simpleTime($userData->reg_time);
            $autograph      = $userData->autograph;
            $birthday       = $userData->birthday;
            $sex            = $userData->sex;
            $display        = '';
            $lv             = User::lv($userData->days);
        } else {
            $nickname       = User::nickname($id);
            $face           = User::face($id);
            $finalTime      = '';
            $finalSystem    = '';
            $regTime        = '';
            $autograph      = '';
            $birthday       = '';
            $sex            = '';
            $lv             = 0;
            $display        = 'style="display:none"';
        }
        // 如果浏览当前名片的用户是同一人，则隐藏相关操作
        if ($id == User::id()) {
            $display        = 'style="display:none"';
        }
        //渲染视图
        View::assign([
            'user'          => $id,
            'title'         => $nickname,
            'description' => '',
            'nickname'      => $nickname,
            'face'          => $face,
            'finalTime'     => $finalTime,
            'finalSystem'   => $finalSystem,
            'regTime'       => $regTime,
            'autograph'     => $autograph,
            'birthday'      => $birthday,
            'sex'           => $sex,
            'display'       => $display,
            'lv'            => $lv,
        ]);
        return View::fetch();
    }
}