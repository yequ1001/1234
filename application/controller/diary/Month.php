<?php
/**
 * Created by PhpStorm.
 * User: YEQU1001
 * Date: 2019/4/21
 * Time: 18:16
 */

namespace app\controller\diary;

use think\Db;
use think\facade\Env;
use app\common\controller\BaseController;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\facade\File;
use app\common\model\Diary as DiaryModel;

class Month extends BaseController
{
    public function index()
    {
        if (User::type() != 'member') {
            $this->redirect('/diary/novice');
        }

        // 计算当前URL月份包含的天数
        $year = input("year", date('Y'));
        $month = input("month", date('m'));
        $month = sprintf('%02s', $month);
        $days = date("t",strtotime("{$year}-{$month}"));

        // 从数据库获取当前用户 当前月份的月记信息
        $week_arr = array("日", "一", "二", "三", "四", "五", "六");
        $DiaryData = DiaryModel::where('date', "{$year}-{$month}-01")->where('user', User::id())->find();

        // 安全码验证
        $UserData = User::data();
        if (!empty($UserData->password2)) {
            if (session('?password2_time')) {
                $oldTime = strtotime(session('password2_time'));
                $nowTime = strtotime(date('Y-m-d H:i:s'));
                $span = ceil(($nowTime - $oldTime) / 60);
                if ($span >= 20) {
                    return $this->redirect('/diary/secure');
                }
            } else {
                return $this->redirect('/diary/secure');
            }
        }

        // 当前月份有日记时
        $diary_arr  = array();
        $diary_arr2 = array();
        if ($DiaryData) {
            $diary_arr  = explode('|', $DiaryData['content']);
            $diary_arr2 = explode('|', $DiaryData['content2']);
        } else {
            for ($day = 1; $day <= $days; $day ++) {
                array_push($diary_arr, '');
                array_push($diary_arr2, '');
            }
        }
        $list_arr = array();
        for ($day = 1; $day <= $days; $day ++) {
            $day = sprintf('%02s', $day);
            $week = $week_arr[date("w",strtotime("{$year}-{$month}-{$day}"))];
            // “今日”高亮
            $light = '';
            if (date('Y-m-d') == "{$year}-{$month}-{$day}") {
                $light = '<span class="light">今天</span>';
            }
            // 周末高亮
            $weekendMark = '';
            if ($week == '六' || $week == '日') {
                $weekendMark = 'weekend-mark';
            }
            // 生成数据数组，用于模板中视图渲染
            $list['year']           = $year;
            $list['month']          = $month;
            $list['day']            = $day;
            $list['date']           = "{$year}{$month}{$day}";
            $list['week']           = $week;
            $list['weekLight']      = $weekendMark;
            $list['todayLight']     = $light;
            $list['content1']       = $diary_arr[$day-1];
            $list['content2']       = $diary_arr2[$day-1];
            array_push($list_arr, $list);
        }

        // 月份翻页处理
        if ($month + 1 > 12) {
            $month2 = 1;
            $year2 = $year +1;
        } else {
            $month2 = $month +1;
            $year2 = $year;
        }

        if ($month - 1 < 1) {
            $month1 = 12;
            $year1 = $year -1;
        } else {
            $month1 = $month -1;
            $year1 = $year;
        }
        // 往年今月
        $a1 = date('Y')-1 .'/'. date('m');
        // 往年下月
        $a2 = (date('Y')-1) .'/'. (date('m')+1);
        // 查看本月
        $a3 = date('Y') .'/'. date('m');
        // 上一个月
        $a4 = $year1 .'/'. $month1;
        // 下一个月
        $a5 = $year2 .'/'. $month2;
        /* 渲染视图 */
        $month = sprintf('%u', $month);
        return view()->assign([
            'title'         => "我的{$month}月-{$year}",
            'description' => '',
            'list'          => $list_arr,
            'year'          => $year,
            'month'         => $month,
            'selectYear'    => $this->selectYear(),
            'selectMonth'   => $this->selectMonth(),
            'a1'            => $a1,
            'a2'            => $a2,
            'a3'            => $a3,
            'a4'            => $a4, // 前端已经删除了这个按钮，本文件也可以不要$a4，但建议保留，以便以后再次使用
            'a5'            => $a5, // 前端已经删除了这个按钮，本文件也可以不要$a5，但建议保留，以便以后再次使用
        ]);
    }

    /**
     * 保存日记
     */
    public function save()
    {
        if (User::type() != 'member') {
            return '当前操作需要登录支持';
        }

        // 接收从客户端发过来的数据
        $input_date         = trim(input('date'));
        $input_content      = trim(input('content'));
        $input_content2     = trim(input('content2'));

        // 当前保存月份的天数
        $date_arr = explode('-', $input_date);
        $days_count = date("t",strtotime($date_arr[0] .'-'. $date_arr[1]));
        // 验证日期
        $date = substr($input_date, 0, 4);
        if ($date > date('Y') +1 || $date < 2016) {
            return '暂不支持的日期';
        }
        $date = substr($input_date, -2);
        if ($date != '01') {
            return '非法日期';
        }
        // 验证日记数量是否与当月天数一致
        if (substr_count($input_content, '|') != $days_count) {
            return '日记正文数量与当前月份的天数不匹配';
        }
        if (substr_count($input_content2, '|') != $days_count) {
            return '日记附属信息数量与当前月份的天数不匹配';
        }
        // 验证日记字数是否符合
        $str_arr = explode('|', $input_content);
        foreach ($str_arr as $str) {
			$s = preg_replace('#<[^>]*>#ims', '', $str);
            if (mb_strlen($s) > 300) {
                return '有日记正文字数超长，限300字';
            }
        }
        $str_arr = explode('|', $input_content2);
        foreach ($str_arr as $str) {
            if (mb_strlen($str) > 20) {
                return '有附属信息字数超长，限20字';
            }
        }
        // 剔除不合法的HTML标签
        $input_content = str_replace('<div', '&#60;div', $input_content);
        $input_content = str_replace('</div>', '&#60;/div&#62;', $input_content);
        $input_content = str_replace('<span', '&#60;span', $input_content);
        $input_content = str_replace('</span>', '&#60;/span&#62;', $input_content);
        $input_content = str_replace('<hr', '&#60;hr', $input_content);
        $input_content = str_replace('<br', '&#60;br', $input_content);

        $input_content = preg_replace('#<[^>]*>#ims', '', $input_content);

        $input_content = str_replace('&#60;div', '<div', $input_content);
        $input_content = str_replace('&#60;/div&#62;', '</div>', $input_content);
        $input_content = str_replace('&#60;span', '<span', $input_content);
        $input_content = str_replace('&#60;/span&#62;', '</span>', $input_content);
        $input_content = str_replace('&#60;hr', '<hr', $input_content);
        $input_content = str_replace('&#60;br', '<br', $input_content);

        $input_content = preg_replace('#<div[^>]+>#ims','<div>', $input_content);

        // 非法敏感词屏蔽
        $input_content = Str::filter($input_content);
		
		$input_content = preg_replace('#\|<span style="text-decoration:line-through; color: \#BCC1D6">×[^\|(\x{4e00}-\x{9fa5})a-z0-9]*</span>\|#imus', '||', $input_content);
		$input_content = preg_replace('#^<span style="text-decoration:line-through; color: \#BCC1D6">×[^\|(\x{4e00}-\x{9fa5})a-z0-9]*</span>\|#imus', '|', $input_content);

		$input_content = preg_replace('#\|<hr class="hr">\|#ims', '||', $input_content);
		$input_content = preg_replace('#^<hr class="hr">\|#ims', '|', $input_content);

		$input_content = preg_replace('#\|<span style="color: \#f0370e">! [^\|(\x{4e00}-\x{9fa5})a-z0-9]*</span>\|#imus', '||', $input_content);
		$input_content = preg_replace('#^<span style="color: \#f0370e">! [^\|(\x{4e00}-\x{9fa5})a-z0-9]*</span>\|#imus', '|', $input_content);
		
		$input_content = preg_replace('#\|[^\|(\x{4e00}-\x{9fa5})a-z0-9]+\|#imus', '||', $input_content);
		$input_content = preg_replace('#^[^\|(\x{4e00}-\x{9fa5})a-z0-9]+\|#imus', '|', $input_content);
		
        // 如果月记为空则转为null
        $str = preg_replace('#<br ?/?>#ims','', $input_content);
        if (!preg_match('#^\|+$#ims', $str)) {
            $input_content = substr($input_content, 0, -1);
        }
        // 当月记为空则删除记录
        if (preg_match('#^\|+$#ims', $input_content) && preg_match('#^\|+$#ims', $input_content2)) {
            DiaryModel::where('date', $input_date)->where('user', User::id())->delete();
            return true;
        }
        // 更新或创建月记
        $DiaryData = DiaryModel::where('date', $input_date)->where('user', User::id())->find();
        if ($DiaryData) {
            DiaryModel::where('date', input('date'))
                ->where('user', User::id())
                ->update(['content' => $input_content, 'content2' => $input_content2]);
        } else {
            DiaryModel::create([
                'user'      => User::id(),
                'date'      => $input_date,
                'content'   => $input_content,
                'content2'  => $input_content2
            ]);
        }
		
		// 创建备份
		$date = date('Y-m-d H:i:s');
		$dir1 = substr(User::id(), 0, 4);
		$dir2 = User::id();
		$filename = date('Y-m') .'.txt';
		$path = Env::get('root_path') ."_data/diary/backup/{$dir1}/{$dir2}/";
		if (!file_exists($path)) {
			File::mkdir($path);
		}
		$path = $path . $filename;
		$input_content = preg_replace('#<[^>]+>#ims', '', $input_content);
	    $input_content = preg_replace('#\|· +#ims', '|', $input_content);
	    $input_content = preg_replace('#\|! +#ims', '|!', $input_content);
	    $input_content = preg_replace('#^· +#ims', '', $input_content);
	    $input_content = preg_replace('#^! +#ims', '!', $input_content);
		$input_content_arr = explode('|', $input_content);
		$input_content = '';
		for ($i = 1; $i <= count($input_content_arr); $i ++) {
			$input_content .= $i .'日:'. $input_content_arr[$i-1] .'|';
		}
		$input_content2_arr = explode('|', $input_content2);
		$input_content2 = '';
		for ($i = 1; $i < count($input_content2_arr); $i ++) {
			$input_content2 .= $i .'日:'. $input_content2_arr[$i-1] .'|';
		}
		$input_content = '微日记'. str_replace('-01', '', $input_date) ."月 保存时间：{$date}\n". $input_content ."\n". $input_content2 ."\n\n";
		File::fopen($path, $input_content, 'a');
        return true;
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

    /**
     * 月份选择按钮
     */
    private function selectMonth()
    {
        $month = "";
        $esc = false;
        for ($i = 1; $i <= 12; $i ++) {
            if ($i == input('month', date('m'))) {
                $month .= "<option value='{$i}' selected>{$i}月</option>";
                $esc = true;
            } else if ($i == date('Y') && !$esc) {
                $month .= "<option value='{$i}' selected>{$i}月</option>";
            } else {
                $month .= "<option value='{$i}'>{$i}月</option>";
            }
        }
        return $month;
    }

    /**
     * 安全退出微日记
     */
    public function close()
    {
		if (User::type() != 'member') {
			return $this->redirect('/');
		}
        session('password2', null);
        return $this->redirect('/');
    }
}