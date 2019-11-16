<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/16
 * Time: 21:39
 */

namespace app\controller\diary;

use app\common\controller\BaseController;
use app\common\model\Diary as DiaryModel;
use app\common\facade\User;

class Search extends BaseController
{
    public function index()
    {
		if (User::type() != 'member') {
			$this->redirect('/diary/novice');
		}
		
        /* 渲染视图 */
        return view()->assign([
            'title'         => '日记搜索',
            'description' => '',
            'select'        => $this->selectYear(),
            ]);
    }

    public function search()
    {
		if (User::type() != 'member') {
			return '您尚未登录';
		}
		
        $key = trim(input('key'));
        $year = trim(input('year'));
		if ($key == '') {
			return '请输入有效的关键词';
		}
        $key = preg_replace('# #i', '%', $key);
        if ($year != '') {
            $DiaryData = DiaryModel::where('content', 'like', "%{$key}%")
			    ->where('user', User::id())
                ->where('date', '>=', $year)
                ->where('date', '<', $year +1)
                ->order('date', 'desc')
                ->select();
        } else {
            $DiaryData = DiaryModel::where('content', 'like', "%{$key}%")
			    ->where('user', User::id())
                ->order('date', 'desc')
                ->select();
        }
        $result = [];
        // 遍历月日记，选出含有关键词的月记
        foreach ($DiaryData as $diary) {
            // 遍历月记，选出含有关键词的日记
            $str_arr = explode('|', $diary['content']);
            for ($day = 1; $day <= count($str_arr); $day ++) {
                // 遍历用户输入的关键词，当前循环日记必须包含所有的用户关键词
                $key_arr = explode('%', $key);
                $bool = false;
                foreach ($key_arr as $keys) {
                    $keys = strtolower($keys);
                    // 屏蔽日记中的html标签，避免搜索到标签中的字符
                    $str = $str_arr[$day-1];
                    $str = preg_replace('#<[^>]+>#ims', '', $str);
                    $str = strtolower($str);
                    // 只要有一个关键词不匹配，就设$bool为false，并退出循环
                    if (strstr($str, $keys)) {
                        $bool = true;
                    } else {
                        $bool = false;
                        break;
                    }
                }
                // 用户关键词成功被匹配，则输出结果数组
                if ($bool) {
                    $date = substr($diary['date'], 0, 8) . sprintf('%02s', $day);
                    array_push($result, ['date'=>$date, 'content'=>$str_arr[$day-1]]);
                }
            }
        }
        return json($result);
    }

    /**
     * 年份选择按钮
     */
    private function selectYear()
    {
        $year = "";
        $esc = false;
        for ($i = 2016; $i <= date('Y')+1; $i ++) {
            if ($i == input('year', date('Y'))) {
                $year .= "<option value='{$i}' selected>{$i}年</option>";
                $esc = true;
            } else if ($i == date('Y') && !$esc) {
                $year .= "<option value='{$i}' selected>{$i}年</option>";
            } else {
                $year .= "<option value='{$i}'>{$i}年</option>";
            }
        }
        return $year;
    }
}