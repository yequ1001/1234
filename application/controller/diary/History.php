<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/16
 * Time: 16:57
 */

namespace app\controller\diary;

use app\common\controller\BaseController;
use app\common\model\Diary as DiaryModel;
use app\common\model\HistoryToday as HistoryTodayModel;
use think\facade\Cache;
use app\common\facade\User;

class History extends BaseController
{
    public function index()
    {
		if (User::type() != 'member') {
			$this->redirect('/diary/novice');
		}
        // 当前年份、月份、第几天
        $this_year = date('Y');
        $this_month = date('m');
        $this_day = date('d');

        for ($year = $this_year; $year >= 2016; $year --) {
            $year_arr[] = "{$year}-{$this_month}-01";
        }
        $DiaryData = DiaryModel::where('date', 'in', $year_arr)->where('user', User::id())->order('date', 'desc')->select();
        $my_history = [];
        foreach ($DiaryData as $diary_arr) {
            $content1 = explode('|', $diary_arr['content']);
            $content2 = explode('|', $diary_arr['content2']);
            for ($day = 1; $day <= count($content1); $day ++) {
                if ($day == $this_day) {
                    $history_year = substr($diary_arr['date'], 0, 4);
                    // 当日记不为空，或附属信息不为空： || mb_strlen($content2[$day-1]) != 0
                    if (mb_strlen($content1[$day-1]) != 0) {
						$content = preg_replace('#^· #ims', '', $content1[$day-1]);
						$content = preg_replace('#>· #ims', '>', $content);
						$content = preg_replace('#>! #ims', '>', $content);
						$content = preg_replace('#>×#ims', '>', $content);
                        if ($content != '' || ($content2[$day-1] != '<br>' && $content2[$day-1] != '')) {
                            $my = [
                                'year'      => $history_year,
                                'content1'  => $content,
                                'content2'  => $content2[$day-1]
                            ];
                            array_push($my_history, $my);
                        }
                    }
                }
            }
        }

        /* 渲染视图 */
        return view()->assign([
            'my_history'    => $my_history,
            'description' => '',
            'title'         => '历史今日',
            'date'          => "{$this_month}月{$this_day}日",
            'world_history' => $this->worldHistory(),
            'empty'         => '<small>没有这一天的记录</small>',
        ]);
    }

    /**
     * 返回世界历史上的今天
     */
    private function worldHistory()
    {
        Cache::remember('HistoryToday'. date('m.d'),function(){
            $HistoryTodayData = HistoryTodayModel::where('date', 'LIKE', '%'.date('m-d'))->order('date', 'desc')->select()->each(function($item, $key){
                $item['date'] = substr($item['date'], 0, 4);
                $item['content'] = str_replace('<br/>', '<br/><br/>', $item['content']);
				$item['content'] = str_replace("\n", '<br/>', $item['content']);
                return $item;
            });
            return $HistoryTodayData;
        }, 86400);
        return cache('HistoryToday'. date('m.d'));
    }
}