<?php
namespace app\controller\user;

use app\common\controller\BaseController;
use think\facade\Validate;
use app\common\model\User as UserModel;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\model\BbsArticle as BbsArticleModel;
use app\common\model\BbsComment as BbsCommentModel;
use app\common\model\BbsBlacklist as BbsBlacklistModel;

class Rename extends BaseController
{
    public function index()
    {
        /**
         * 如果用户没有登陆则强制退出当前页面
         */
        User::login();

        $userData   = User::data();
        $nickname   = $userData->nickname;
        $face       = $userData->face;

        /**
         * 渲染视图
         */
        return view()->assign([
            'description' => '',
            'nickname'  => $nickname,
            'title'     => '重新取名',
            'face'      => $face,
        ]);
    }

    /**
     * 用户改名字
     */
    public function save()
    {
        $newNickname = input("newNickname");
        $newNickname = trim($newNickname);

        /**
         * 验证名字是否符合规则：
         * 必填，长度最大为8，只能为汉字字母和数字
         */
        Validate::rule([
            'newNickname|名字' => 'require|max:8|chsAlphaNum',
        ]);

        $form = [
            'newNickname' => $newNickname,
        ];

        if (!Validate::check($form)) {
            return Validate::getError();
        }

        /**
         * 连接数据库获取用户信息
         */
        $userData   = User::data();

        /**
         * 验证距离上次改名时间的天数是否满足60天
         */
        $nicknameTime = $userData->nickname_time;
        if (!empty($nicknameTime)) {
            $daySpan = Str::daySpan(date('Y-m-d H:i:s'), $nicknameTime);
            if ($daySpan < 60) {
                $SurplusDays = 60 - $daySpan;
                return "距离上次改名时间太短，还需等待{$SurplusDays}天";
            }
        }

        // 查询用户名称是否存在，名称必须唯一
        if (UserModel::getByNickname($newNickname)) {
            return '该昵称已被占用，再换一个';
        }

        /**
         * 数据库用户名字更新并返回信息
         */
        $update = UserModel::where('id', User::id()) ->update([
            'nickname' => $newNickname,
            'nickname_time' => date('Y-m-d H:i:s')
        ]);

        /**
         * 更新其它表
         */
        BbsArticleModel::where('user', User::id()) ->update([
            'nickname' => $newNickname,
        ]);
        BbsCommentModel::where('user', User::id()) ->update([
            'nickname' => $newNickname,
        ]);
		BbsBlacklistModel::where('user', User::id()) ->update([
            'nickname' => $newNickname,
        ]);

        if ($update) {
            return true;
        } else {
            return '出错了';
        }
    }
}