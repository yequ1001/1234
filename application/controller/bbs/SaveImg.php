<?php
namespace app\controller\bbs;

use think\facade\Env;
use app\common\controller\BaseController;
use app\common\facade\File;

class SaveImg extends BaseController
{
    public function save()
    {
        $imgname = $_FILES['file']['name'];		//图片的文件名
        $tmp = $_FILES['file']['tmp_name'];		//

        $path = Env::get('root_path') .'/_data/bbs/_temp/';
        if (!is_dir($path)) {
            File::mkdir($path);
        }

        $move = move_uploaded_file($tmp, $path . $imgname);
        if($move) {
            unset($move);
            return 'OK';
        } else {
            unset($move);
            return "上传失败";
        }
    }
}