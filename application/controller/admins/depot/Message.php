<?php
/**
 * Created by PhpStorm.
 * User: YeQu1001
 * Date: 2019/7/13
 * Time: 18:38
 */

namespace app\controller\admins\depot;

use app\common\controller\BaseController;
use app\common\model\DepotMessage as DepotMessageModel;
use app\common\facade\User;
use app\common\facade\Str;
use app\common\facade\Record;

class Message extends BaseController
{
    public function index()
    {
        User::login();

        $DepotMessageData = DepotMessageModel::where('id', '>', 0)->order('id', 'desc')->paginate(20)->each(function($item, $key){
            $item->user = User::nickname($item->user);
            $item->time = Str::simpleTime($item->time);
        });
        $page_items = $DepotMessageData->total();
        $pages = ceil($page_items / 20);

        /* 渲染视图 */
        return view()->assign([
            'title' => '用户留言管理',
            'description' => '',
            'pages'         => $pages,
            'empty'         => '<br/><br/><p>&nbsp; 没有新的留言！</p><br/><br/>',
            'DepotMessageList' => $DepotMessageData,
        ]);
    }

    public function save()
    {
        $id = input('id');
        $content = input('content');
        DepotMessageModel::where('id', $id)->update([
            'reply' => $content,
            'reply_time' => date('Y-m-d'),
            'reply_user' => User::id()
        ]);

        /**
         * 清除缓存
         */
        cache('DepotMessageData', NULL);

        /**
         * 站务纪录
         */
        Record::depot("回复了留言{$id}：{$content}");

        return true;
    }

    public function delete()
    {
        $id = input('id');
        $start = input('start');
        $end = input('end');
        if (empty($id) && empty($start) && empty($end)) {
            return '输入不能为空';
        }
        if ($id != null) {
            DepotMessageModel::destroy($id);
            Record::depot("删除了留言{$id}");
        } else {
            $count = DepotMessageModel::where('id', '>=', $start)->where('id', '<=', $end)->delete();
            Record::depot("批量删除了留言{$start}~{$end}，共{$count}条数据");
        }

        /**
         * 清除缓存
         */
        cache('DepotMessageData', NULL);

        return true;
    }
}