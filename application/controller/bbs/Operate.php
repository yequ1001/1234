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
use app\common\model\BbsBlacklist as BbsBlacklistModel;

class Operate extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        $id = input('id');
        $BbsArticleData = BbsArticleModel::get($id);
        if (!$BbsArticleData) {
            $this->to404();
        }
        $articleTitle = mb_substr($BbsArticleData->title, 0, 6) .'..';
        $user = $BbsArticleData->user;
        $nickname = $BbsArticleData->nickname;
        $face = $BbsArticleData->face;
        $type = $BbsArticleData->type;
        $typeName = $BbsArticleData->type_name;
        $is_top = $BbsArticleData->is_top;
        $is_essence = $BbsArticleData->is_essence;
        if ($is_top) {
            $is_fine = 'top';
        } elseif ($is_essence) {
            $is_fine = 'essence';
        } else {
            $is_fine = '';
        }

        $BbsBlacklistData = BbsBlacklistModel::where('user', $user)
		    ->where('type', $type)
			->find();
        if (empty($BbsBlacklistData)) {
            $default_value = '';
        } else {
            $default_value = $BbsBlacklistData->expire;
        }

        return view()->assign([
            'title'         => '管理操作',
            'description' => '',
            'user' => $user,
            'nickname' => $nickname,
            'face' => $face,
            'articleId' => $id,
            'articleTitle' => $articleTitle,
            'type' => $type,
            'typeName' => $typeName,
            'is_fine' => $is_fine,
            'default_value' => $default_value,
        ]);
    }

    public function update()
    {
        $type = input('type');
        $id = input('id');
        $state = input('state');
        $expire = input('blacklist');
        $user = input('user');
        $nickname = input('nickname');
        $face = input('face');

        if (strtotime($expire) - strtotime(date('Y-m-d H:i:s')) < 0) {
            $expire = null;
        }

        switch ($state) {
            case 'top':
                $option = [
                    'is_essence' => 0,
                    'is_essence_time' => null,
                    'is_top' => 1,
                    'is_top_time' => date('Y-m-d H:i:s')
                ];
                break;
            case 'essence':
                $option = [
                    'is_essence' => 1,
                    'is_essence_time' => date('Y-m-d H:i:s'),
                    'is_top' => 0,
                    'is_top_time' => null
                ];
                break;
            default:
                $option = [
                    'is_essence' => 0,
                    'is_essence_time' => null,
                    'is_top' => 0,
                    'is_top_time' => null
                ];
                break;
        }

        BbsArticleModel::where('id', $id)
            ->update($option);

        $BbsBlacklistData = BbsBlacklistModel::where('user', $user)->find();
        if (empty($BbsBlacklistData) && $expire != null) {
            BbsBlacklistModel::create([
                'user' => $user,
                'nickname' => $nickname,
                'face' => $face,
                'time' => date('Y-m-d H:i:s'),
                'expire' => $expire,
                'type' => $type
            ]);
        } else {
            if (empty($expire)) {
                BbsBlacklistModel::where('user', $user)
                    ->where('type', $type)
                    ->delete();
            } else {
                BbsBlacklistModel::where('user', $user)
                    ->where('type', $type)
                    ->update(['expire' => $expire]);
            }
        }
        return true;
    }

}