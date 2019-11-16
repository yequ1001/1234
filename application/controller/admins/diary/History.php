<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/16
 * Time: 16:57
 */

namespace app\controller\admins\diary;

use app\common\controller\BaseController;
use app\common\model\HistoryToday as HistoryTodayModel;
use app\common\facade\User;

class History extends BaseController
{
    public function index()
    {

        User::login();

        /* 渲染视图 */
        return view()->assign([
            'title'    => '世界历史纪录修改器',
            'description' => '',
            'history'  => $this->worldHistory(),
        ]);
    }

    /**
     * 返回世界历史上的今天
     */
    private function worldHistory()
    {
        $HistoryTodayData = HistoryTodayModel::where('date', 'LIKE', '%' . date('m-d'))->order('date', 'desc')->select()->each(function ($item, $key) {
            $item['content'] = str_replace('<br/>', '<br/><br/>', $item['content']);
            return $item;
        });
        return $HistoryTodayData;
    }

    public function save()
    {
        $id         = input('id');
        $date       = input('date');
        $title      = input('title');
        $content    = input('content');

        $HistoryTodayData = HistoryTodayModel::get($id);
        $HistoryTodayData->date     = $date;
        $HistoryTodayData->title    = $title;
        $HistoryTodayData->content  = $content;
        $HistoryTodayData->save();

        return true;
    }

    public function del()
    {
        $id = input('id');
        HistoryTodayModel::destroy($id);
        return true;
    }
}