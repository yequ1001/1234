<?php
/**
 * Created by PhpStorm.
 * User: YEQU1001
 * Date: 2019/8/24
 * Time: 14:06
 */

namespace app\controller\diary;

use think\Db;
use think\facade\Env;
use app\common\controller\BaseController;
use app\common\model\Diary as DiaryModel;
use app\common\facade\File;
use app\common\facade\User;

class Backup extends BaseController
{
    public function index()
    {
		if (User::type() != 'member') {
			$this->redirect('/diary/novice');
		}
		
		$date = date('Y-m-d H:i:s');
		$dir1 = substr(User::id(), 0, 4);
		$dir2 = User::id();
//		$filename = str_replace('-01', '', $input_date);
//		$filename = preg_replace('#-([^0])#ims', "-0$1", $filename) .'.txt';
		$path = Env::get('root_path') ."_data/diary/backup/{$dir1}/{$dir2}/";
		if (is_dir($path)) {
    		$fileArr = scandir($path, 1);
			array_splice($fileArr, -2);
			$fileArr = str_replace('.txt', '', $fileArr);
		} else {
			$fileArr = null;
		}

        /* 渲染视图 */
        return view()->assign([
            'title'   => '找回日记',
			'description' => '',
			'fileArr' => $fileArr,
			'empty'   => '暂无备份',
        ]);
    }

    public function download()
	{
		if (User::type() != 'member') {
			return '您尚未登录';
		}
		$dir1 = substr(User::id(), 0, 4);
		$dir2 = User::id();
		$filename = input('year') .'-'. input('month') .'.txt';
		$file = Env::get('root_path') ."_data/diary/backup/{$dir1}/{$dir2}/{$filename}";
		if (!file_exists($file)) {
			return '不存在的文件';
		}
		$txt = file_get_contents($file); // 注意file_get_contents不要写在Header之后，会被影响可能引起乱码
		
        // 启动下载
        // 告诉浏览器这是一个文件流格式的文件
        Header ('Content-type: application/octet-stream');
        // 请求范围的度量单位
        Header ('Accept-Ranges: bytes');
        // Content-Length是指定包含于请求或响应中数据的字节长度
        Header ('Content-Length: '. strlen($txt));
        // 用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
        Header ("Content-Disposition: attachment; filename=微日记备份{$filename}");
        return $txt;
	}
}