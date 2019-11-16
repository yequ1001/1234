<?php
/**
 * Created by PhpStorm.
 * User: Yequ1001
 * Date: 2019/7/3
 * Time: 21:35
 */

namespace app\controller\depot;

use app\common\controller\BaseController;
use app\common\model\DepotMessage as DepotMessageModel;
use app\common\facade\User;
use app\common\facade\Str;
use think\facade\Cache;
use think\facade\Session;
use think\Validate;

class Message extends BaseController
{
    public function index()
    {
        /**
         * 验证码过期
         */
        Session::set('captcha', null);

        $page_item = 18;
        if (input('page') == null || input('page') == 1) {
            Cache::remember('DepotMessageData',function(){
                $DepotMessageData = DepotMessageModel::where('id', '>', 0)->order('id', 'desc')->paginate(18)->each(function($item, $key){
                    $item->user = User::nickname($item->user);
                    $item->reply = Str::getLink($item->reply);
                });
                return $DepotMessageData;
            });

            $DepotMessageData = cache('DepotMessageData');
            $DepotMessageData->each(function($item, $key){
                $item->time = Str::simpleTime($item->time);
            });
        } else {
            $DepotMessageData = DepotMessageModel::where('id', '>', 0)->order('id', 'desc')->paginate(18)->each(function($item, $key){
                $item->user = User::nickname($item->user);
                $item->time = Str::simpleTime($item->time);
                $item->reply = Str::getLink($item->reply);
            });
        }

        $page_items = $DepotMessageData->total();
        $pages = ceil($page_items / $page_item);

        return view()->assign([
            'title'         => '留言箱',
            'description' => '',
            'pages'         => $pages,
            'empty'         => '<br/><br/><p>&nbsp; 没有新的留言！</p><br/><br/>',
            'DepotMessageList' => $DepotMessageData,
        ]);
    }

    /**
     * 保存新留言
     */
    public function save()
    {
        $content = input('content');

        /**
         * 输入验证
         */
        $validate = Validate::make([
            'content|留言'    => 'require|length:2,300',
        ]);
        $data = [
            'content' => $content,
        ];
        if (!$validate->check($data)) {
            return $validate->getError();
        }

        /**
         * 敏感词屏蔽
         */
        $content = Str::filter($content);

        /**
         * 三方api验证
         */
        if (Session::get('captcha') != 'OK') {
            Session::set('captcha', null);
            return '您还没有通过验证';
        }

        /**
         * 验证码过期
         */
        Session::set('captcha', null);

        /**
         * 保存数据库
         * 此时保存的数据是未激活的，等待用户进一步操作来激活
         */
        $DepotMessageData = DepotMessageModel::create([
            'content'   => htmlspecialchars($content),
            'time'      => date('Y-m-d H:i:s'),
            'user'      => User::id(),
            'ua'        => User::system(),
        ]);

        /**
         * 清除缓存
         */
        cache('DepotMessageData', NULL);

        if ($DepotMessageData) {
            return true;
        } else {
            return $DepotMessageData;
        }
    }
}