<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/6/16
 * Time: 20:19
 */

namespace app\controller\diary;

use app\common\controller\BaseController;
use app\common\model\Diary as DiaryModel;
use think\facade\Env;
use app\common\facade\User;

class Year extends BaseController
{
    public function index()
    {
		if (User::type() != 'member') {
			$this->redirect('/diary/novice');
		}
        /* 渲染视图 */
        return view()->assign([
            'title'         => '年度记录 - '. input('year'),
            'description'   => '',
            'year'          => input('year'),
            'table'         => $this->annualReport(input('year'))
        ]);
    }

    /**
     * 年度日记下载
     */
    public function download()
    {
		if (User::type() != 'member') {
			return '您尚未登录';
		}
		
        $year = input('year');
        $template = file_get_contents(Env::get('ROOT_PATH') .'public/diary/year/download.html');
        $list = str_replace('[[html]]', $this->annualReport($year), $template);
        // 启动下载
        // 告诉浏览器这是一个文件流格式的文件
        Header ('Content-type: application/octet-stream');
        // 请求范围的度量单位
        Header ('Accept-Ranges: bytes');
        // Content-Length是指定包含于请求或响应中数据的字节长度
        Header ('Content-Length: '. strlen($list));
        // 用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
        Header ("Content-Disposition: attachment; filename=勤话微日记{$year}.html");
        return $list;
    }

    /**
     * 生成年度报告
     */
    private function annualReport($year)
    {
		$username = User::data();
	    $username = $username->username;	
        $lastYear = $year +1;
        $week_arr = array("日", "一", "二", "三", "四", "五", "六");
        $diaryData = DiaryModel::where('date', '>=', "{$year}-01-01")->where('date', '<', "{$lastYear}-01-01")->where('user', User::id())->order('date', 'asc')->select();
        $list = "<h1>勤话微日记 {$year}年<span class='username'>用户：{$username}</span></h1><table>";
        foreach ($diaryData as $diary) {
            $date = $diary['date'];
            $daysArr1 = $diary['content'];
            $daysArr2 = $diary['content2'];
            $daysArr1 = explode('|', $daysArr1);
            $daysArr2 = explode('|', $daysArr2);
            for ($i = 1; $i <= count($daysArr1); $i ++) {
                $date = substr($date, 0, 8) . sprintf('%02s', $i);
                $week = $week_arr[date('w',strtotime($date))];
                $str1 = $daysArr1[$i-1];
                $str2 = $daysArr2[$i-1];
                if ($str1 != '') {
                    $str1 = preg_replace('#(>! )|(>× )#ims', '>· ', $str1);
                    $date2 = substr($date, 5);
					$week_mark = '';
					if ($week == '六' || $week == '日') {
						$week_mark = 'weekend';
					}
                    $list .= "<tr><td class='date {$week_mark}'>{$date2}<br/>周{$week}</td><td class='content1'>{$str1}</td><td class='content2'>{$str2}</td></tr>\n";
                }
            }
        }
        $list .= '</table>';
        return $list;
    }
}