<?php

namespace app\controller\user;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Env;
use think\facade\Validate;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\model\User as UserModel;
use app\common\model\BbsArticle as BbsArticleModel;
use app\common\model\BbsComment as BbsCommentModel;
use app\common\model\BbsBlacklist as BbsBlacklistModel;

class Face extends BaseController
{
    public function index()
    {
        /**
         * 如果用户没有登陆则跳出当前页面
         */
        User::login();

        $userData   = User::data();
        $face       = $userData->face;

        /**
         * 渲染视图
         */
        return view()->assign([
            'title' => '头像设置',
            'description' => '',
            'face' => $face,
        ]);
    }

    /**
     * 保存用户头像
     */
    public function save()
    {
        // 服务器默认的文件上传路径
        $tmp = $_FILES['file']['tmp_name'];
        /**
         * 获取文件信息
         */
        $dir = substr(User::id(), 0, 3);
        $file = User::id() .'.jpg';
        $path = Env::get('root_path') ."_data/user/face/{$dir}";
        if (!is_dir($path)) {
            mkdir($path, 0700);
        }
        $path .= "/{$file}";
        if (move_uploaded_file($tmp, $path)) {
            $path = "/_data/user/face/{$dir}/{$file}?t=". time();
            $s = UserModel::where('id', User::id()) ->update([
                    'face' => $path,
                ]);

            /**
             * 更新其它表
             */
            BbsArticleModel::where('user', User::id()) ->update([
                'face' => $path,
            ]);
            BbsCommentModel::where('user', User::id()) ->update([
                'face' => $path,
            ]);
			BbsBlacklistModel::where('user', User::id()) ->update([
                'face' => $path,
            ]);

            if ($s) {
                return 'OK'; //此处不能使用bool类型的true，前端也不要改
            } else {
                return '上传失败了';
            }
        } else {
            return '上传失败';
        }
    }
}