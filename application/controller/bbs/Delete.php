<?php
namespace app\controller\blog;

use think\Db;
use think\facade\Env;
use app\common\facade\User;
use app\common\model\Blog1          as Blog1Model;
use app\common\model\Blog1Praise    as Blog1PraiseModel;
use app\common\model\Blog2          as Blog2Model;
use app\common\model\Blog2Praise    as Blog2PraiseModel;
use app\common\model\Blog3          as Blog3Model;
use app\common\controller\BaseController;

class Delete extends BaseController
{
    public function delBlog1(){
        $blog1 = input('id');
        // 查询1级帖发帖人是否当前操作人
        $blog1Data = Blog1Model::get($blog1);
        if (empty($blog1Data)) {
            return '当前帖子已经不存在';
        }
        if (User::id() != $blog1Data->user) {
            return '您无权进行操作';
        }

        // 删除1级帖图片
        $imgData = Blog1Model::where('id', $blog1)->field('img')->find();
        $imgArr = explode('|', $imgData['img']);
        if ($imgArr[0] == '') {
            $imgArr = [];
        }
        foreach ($imgArr as $img) {
            $imgDir = Env::get('root_path') .'public/data/blog/'. substr($img, 0, 6);
            $imgPath = $imgDir .'/'. substr($img, 6);
            if (is_file($imgPath)) {
                $this->delFile($imgPath);
            }
            if (is_dir($imgDir)) {
                if ($this->is_empty_dir($imgDir)) {
                    rmdir($imgDir);
                }
            }
        }

        // 删除2级帖图片
        $imgData = Blog2Model::where('blog1', $blog1)->field('img')->select();
        foreach ($imgData as $imgArr) {
            $imgArr = explode('|', $imgArr['img']);
            if ($imgArr[0] == '') {
                $imgArr = [];
            }
            foreach ($imgArr as $img) {
                $imgDir = Env::get('root_path') .'public/data/blog/'. substr($img, 0, 6);
                $imgPath = $imgDir .'/'. substr($img, 6);
                if (is_file($imgPath)) {
                    $this->delFile($imgPath);
                }
                if (is_dir($imgDir)) {
                    if ($this->is_empty_dir($imgDir)) {
                        rmdir($imgDir);
                    }
                }
            }
        }

        // 以下删除数据库信息
        Blog1Model::where('id', $blog1)->delete();
        Blog1PraiseModel::where('blog1', $blog1)->delete();
        Blog2Model::where('blog1', $blog1)->delete();
        Blog2PraiseModel::where('blog1', $blog1)->delete();
        Blog3Model::where('blog1', $blog1)->delete();

        return 'OK';
    }

    public function delBlog2(){

        $blog2 = input('id');

        // 查询2级帖发帖人是否当前操作人
        $blog2Data = Blog2Model::get($blog2);
        if (empty($blog2Data)) {
            return '当前回复已经不存在';
        }
        if (User::id() != $blog2Data->user) {
            return '您无权进行操作';
        }

        // 删除图片
        $imgData = Blog2Model::where('id', $blog2)->field('img')->find();
        $imgArr = explode('|', $imgData['img']);
        if ($imgArr[0] == '') {
            $imgArr = [];
        }
        foreach ($imgArr as $img) {
            $imgDir = Env::get('root_path') .'public/data/blog/'. substr($img, 0, 6);
            $imgPath = $imgDir .'/'. substr($img, 6);
            if (is_file($imgPath)) {
                $this->delFile($imgPath);
            }
            if (is_dir($imgDir)) {
                if ($this->is_empty_dir($imgDir)) {
                    rmdir($imgDir);
                }
            }
        }

        // 以下删除数据库信息
        Blog2Model::where('id', $blog2)->update([
            'content' => '<small style="font-size: 15px">[ 该回复已删除 ]</small>',
            'img'     => null,
            ]);
        Blog3Model::where('blog2', $blog2)->delete();

        return 'OK';
    }

    /**
     * 判断文件夹是否为空
     */
    private function is_empty_dir($dir)
    {
        $d = opendir($dir);
        $i = 0;
        while ($a=readdir($d)) {
            $i++;
        }
        closedir($d);
        if ($i>2) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 删除文件
     */
    private function delFile($imgPath)
    {
        if (!unlink($imgPath)) {
            $myfile = fopen(Env::get('root_path') .'runtime/garbage.txt', 'a');
            fwrite($myfile, str_replace('\\', '/', $imgPath) ."\n");
            fclose($myfile);
        }
    }
}