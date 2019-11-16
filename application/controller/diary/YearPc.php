<?php
namespace app\controller\diary;

use think\Db;
use think\facade\Env;
use think\model\Collection;
use app\common\controller\BaseController;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\facade\File;
use app\common\model\Diary as DiaryModel;

class YearPc extends BaseController
{
    function object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)$this->object_to_array($v);
            }
        }

    return $obj;
}

    public function index()
    {
        if (User::type() != 'member') {
            $this->redirect('/diary/novice');
        }

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

        // 计算当前URL月份包含的天数
        $year = input("year", date('Y'));

        // 从数据库获取当前用户 当前月份的月记信息
        $yearArr = [];
        $week_arr = array("日", "一", "二", "三", "四", "五", "六");
        $DiaryData = DiaryModel::where('date', 'like', "{$year}%")->where('user', User::id())->order('date', 'asc')->select();
        $DiaryData = $DiaryData->toArray();
        for ($i = 0; $i < 12; $i ++) {
            if (!empty($DiaryData[0])) {
                if (substr($DiaryData[0]['date'], 5, 2) == sprintf("%02d", $i+1)) {
                    $yearArr[] = $DiaryData[0];
                    $DiaryData = array_splice($DiaryData, 1);
                } else {
                    $yearArr[] = [];
                }
            } else {
                $yearArr[] = [];
            }
        }

        for ($i = 0; $i < 12; $i ++) {
            $month = $yearArr[$i];
            $content = $month;
            $attach = [];
            $d = [];
            if (!empty($month)) {
                $content = explode('|', $month['content']);
                $attach = explode('|', $month['content2']);
                for ($j = 0; $j <count($content); $j ++) {
                    $d[] = array('c1'=>$content[$j], 'c2'=>$attach[$j]);
                }
            }
            switch ($i) {
                case 0:
                    $month1 = $d;
                    break;
                case 1:
                    $month2 = $d;
                    break;
                case 2:
                    $month3 = $d;
                    break;
                case 3:
                    $month4 = $d;
                    break;
                case 4:
                    $month5 = $d;
                    break;
                case 5:
                    $month6 = $d;
                    break;
                case 6:
                    $month7 = $d;
                    break;
                case 7:
                    $month8 = $d;
                    break;
                case 8:
                    $month9 = $d;
                    break;
                case 9:
                    $month10 = $d;
                    break;
                case 10:
                    $month11 = $d;
                    break;
                case 11:
                    $month12 = $d;
                    break;
            }
        }

        /* 渲染视图 */
        return view()->assign([
            'title'         => "年度纪录 - {$year} - 电脑模式",
            'description' => '',
            'year' => $year,
            'empty' => '<p style="text-align: center; font-size: 13px; color: #828282; margin-top: 20px">无记事</p>',
            'month1' => $month1,
            'month2' => $month2,
            'month3' => $month3,
            'month4' => $month4,
            'month5' => $month5,
            'month6' => $month6,
            'month7' => $month7,
            'month8' => $month8,
            'month9' => $month9,
            'month10' => $month10,
            'month11' => $month11,
            'month12' => $month12,
        ]);
    }
}