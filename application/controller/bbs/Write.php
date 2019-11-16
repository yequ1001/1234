<?php

namespace app\controller\bbs;

use think\DB;
use think\facade\Env;
use think\facade\Validate;
use app\common\controller\BaseController;
use app\common\facade\Form;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\facade\File;
use app\common\model\Bbs as BbsModel;
use app\common\model\BbsArticle as BbsArticleModel;
use app\common\model\BbsBlacklist as BbsBlacklistModel;

class Write extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {
        User::login();

        $updateId = input('update');
        $inputTitle = '';
        $inputContent = '';

        if (!empty($updateId)) {
            $BbsData = BbsArticleModel::get($updateId);
            $inputTitle = $BbsData->title;
            $inputContent = $BbsData->content;
        }

		if(empty(input('keywords'))) {
			$BbsData = BbsModel::where('id', input('type'))->find();
			$title = $BbsData->name;
		} else {
			$title = input('keywords');
		}

        return view()->assign([
            'title'         => $title,
            'description' => '',
            'bind' => input('bind'),
            'write' => input('write'),
            'type' => input('type'),
            'inputTitle' => $inputTitle,
            'inputContent' => $inputContent,
            'faceList' => File::face(),
            'face' => User::face(),
            'nickname' => User::nickname(),
        ]);
    }

    /**
     * 保存1级帖
     */
    public function save()
    {
        /**
         * 如果用户没有登陆则跳出当前页面
         */
        User::login();


        $title = input('title');
        $content = input('content');
        $bind = input('bind');
        $type = input('type');
        $write = input('write');
        $keywords = input('keywords');
        $result = $type;

        // 查询当前用户是否被拉入黑名单
        $BbsBlacklistData = BbsBlacklistModel::where('user', User::id())
            ->where('type', $type)
            ->find();

        if (!empty($BbsBlacklistData)) {
            $timespan = strtotime($BbsBlacklistData->expire) - strtotime(date('Y-m-d H:i:s'));
            if ($timespan > 0) {
                $s = $timespan;
                $m = floor($s/60);
                $h = floor($m/60);
                $d = floor($h/24);
                if ($d >= 1) {
                    $timespan = $d .'天';
                } elseif ($h >= 1) {
                    $timespan = $h .'小时';
                } elseif ($m >= 1) {
                    $timespan = $m .'分钟';
                } else {
                    $timespan = $s .'秒钟';
                }
                return '您被列入黑名单，距离解禁还有'. $timespan;
            }
        }

        // 是否已被手动关闭发帖
        $BbsData = BbsModel::where('id', input('type'))->find();
        $authority_write = $BbsData->authority_write;
        $admin1 = $BbsData->admin1;
        if (($authority_write == 1 || $authority_write == 2) && User::id() != $admin1) {
            return '很抱歉，您没有权限';
        }

        // 以下if没什么意义，只是为了验证通过，不为空
        if (!empty($keywords)) {
            $type = $keywords;
        }

        $content = preg_replace('#<iframe[^>]+>#ims', '', $content);
        $content = preg_replace('#</iframe>#ims', '', $content);
        $content = preg_replace('#<a[^>]+>#ims', '', $content);
        $content = preg_replace('#</a>#ims', '', $content);
        $content = preg_replace('#<script[^>]+>#ims', '', $content);
        $content = preg_replace('#</script>#ims', '', $content);
        $content = preg_replace('#<style[^>]+>#ims', '', $content);
        $content = preg_replace('#</style>#ims', '', $content);
        $content = preg_replace('#<img[^>]+onerror=[^>]+/>#ims', '', $content);
		$content = preg_replace('#^(<div><br></div>)+|(<div><br></div>)+$#ims', '', $content);

        // 数据验证
        $validate = [
            'title'         =>$title,
            'content'       => $content,
            'type' => $type,
        ];
        Validate::rule([
            'title|标题'          => 'require|max:32',
            'content|内容'        => 'require|length:2,60000',
            'type|类型'      => 'require|length:1,10'
        ]);
        if(!Validate::check($validate)){
            return Validate::getError();
        }

        // 屏蔽敏感词
        $title   = Str::filter($title);
        $content = Str::filter($content);
        if (empty($bind)) {
            $bind = null;
        }

        // 将正文帖中的图形提取出来单独保存字段
        preg_match_all('#(?<="/_data/bbs/_temp/)\d{15}.[a-z]{3,4}(?=")#ims', $content, $imgArr);
        $imgStr = implode('|', $imgArr[0]);

        // 在所有的验证之后，创建新的论坛
        if (!empty($keywords)) {
            $BbsData = BbsModel::where('name', $keywords)->find();
//            $type = $keywords;
            if (empty($BbsData)) {
                $BbsData = BbsModel::create([
                    'name' => $keywords,
                    'time' => date('Y-m-d H:i:s'),
                    'founder' => User::id(),
                    'admin1' => User::id(),
                    'article' => 1,
                    'links_name' => '|||||||||||',
                    'links_url' => '|||||||||||',
                ]);
                $result = $BbsData->id;
                $type = $BbsData->id;
            }
        }

        // 写入数据库
        if (empty($keywords)) {
            $type_name = BbsModel::where('id', $type)->find();
            $type_name = $type_name->name;
        } else {
            $type_name = $keywords;
        }
        $bbsData = BbsArticleModel::create([
            'title' => $title,
            'content' => $content,
            'user' => User::id(),
            'nickname' => User::nickname(),
            'face' => User::face(),
            'time' => date('Y-m-d H:i:s'),
            'type' => $type,
            'type_name' => $type_name,
            'bind' => $bind,
            'imgArr' => $imgStr,
            'reply_time' => date('Y-m-d H:i:s')
        ]);
        $id = $bbsData->id;

        // 连载成册
        if (!empty($bind)) {
            BbsArticleModel::where('id', $bind)
                ->update([
                    'is_book' => ['inc', 1]
                ]);
        }

        // 更新统计
        BbsModel::where('id', $type)
            ->update([
                'article' => ['inc', 1]
            ]);

        // 将帖子中的图片临时地址转换为正式地址
        $bbsData->content = preg_replace('#"/_data/bbs/_temp/(\d{15}.[a-z]{3,4})"#ims', "\"/_data/bbs/{$type}/{$id}/$1\"", $content);
        $bbsData->save();

        // 将图形从临时文件夹中移动到正式文件夹里面
        foreach ($imgArr[0] as $img) {
            $imgName = $img;
            $path = Env::get('root_path') ."_data/bbs/{$type}/{$id}/";
            if (!is_dir($path)) {
                File::mkdir($path);
            }
            $path .= $imgName;
            // 已经上传临时文件的路径
            $temp = Env::get('root_path') .'_data/bbs/_temp/'. $imgName;
            if (is_file($temp)) {
                if(!rename($temp, $path)) {
                    unlink($temp);
                }
            }
        }

        // 处理成功，则返回论坛类型id
        return $result;
    }

    /***********************************************************************
     * 保存2级贴
     */
    public function saveBlog2()
    {
        /** 接收客户端的数据 */
        $blog1      = input('id');
        $content    = trim(input('content'));
        /** 数据验证 */
        // 1.验证内容字数是否符合要求
        $contentCount = mb_strlen($content);
        if ($contentCount > 10000 || $contentCount < 1) {
            return "限1~10000字之间，当前内容{$contentCount}字";
        }
        // 2.验证绑定的1级帖是否存在
        $blog1Has = Blog1Model::get($blog1);
        if (empty($blog1Has)) {
            return '不存在的帖子或已被删除';
        }
        // 3.验证内容中是否携带code UBB代码，如果携带则不分段
        $_bool      = preg_match('#(?<=\[code\]).+(?=\[/code\])#ims', $content);
        /** 接收的数据再加工 */
        $content    = htmlspecialchars($content);
        $content    = Form::insertFace($content);
        $content    = Str::filter($content);
        /** 验证通过，将2级帖写入数据库 */
        $blog2Model = new Blog2Model;
        if ($contentCount > 50 && !$_bool) {
            $contentArr = Str::paragraph($content);
        } else {
            $contentArr = [$content];
        }
        $contentArrCount = count($contentArr);
        $list = array();
        foreach ($contentArr as $value) {
            // 将回帖中的图片代码提取出来写入数据库
            preg_match_all('#(?<=\[)\d{15}\.[a-z]{3,4}(?=\])#ims', $value, $imgArr);
            $imgStr = implode('|', $imgArr[0]);
            $arr = [
                'user'      => cookie('user_id'),
                'time'      => date('Y-m-d H:i:s'),
                'blog1'     => $blog1,
                'content'   => $value,
                'img'       => $imgStr,
            ];
            array_push($list, $arr);

            // 将图形从临时文件夹中移动到正式文件夹里面
            foreach ($imgArr[0] as $img) {
                $imgName = str_replace('/', '', $img); //图片的文件名
                $dir = substr($imgName, 0, 6);
                $file = substr($imgName, 6);
                $path = Env::get('root_path') ."public/data/blog/{$dir}";
                if (!is_dir($path)) {
                    mkdir($path, 0700);
                }
                $path .= "/{$file}";
                // 已经上传临时文件的路径
                $temp = Env::get('root_path') .'runtime/blog/'. $imgName;
                if (is_file($temp)) {
                    // 此处是复制，不能用移动
                    if(!copy(Env::get('root_path') .'runtime/blog/'. $imgName, $path)) {
                        unlink(Env::get('root_path') .'runtime/blog/'. $imgName);
                    }
                }
            }
        }
        $blog2Model->saveAll($list, false);

        /** 将1级帖的回复量加1，且更新最新回复时间、最新回复内容 */
        $content = preg_replace('#<[^>]+>#i', '[表情]', $content);
        if (mb_strlen($content) > 21) {
            $Blog2Content = mb_substr($content, 0, 21) .'..';
        } else {
            $Blog2Content = $content;
        }
        $Blog2Content = preg_replace('#\[\d{15}.[a-z]{3,4}\]#ims', '[图片]', $Blog2Content);
        Db::execute('UPDATE op_blog1 SET blog2Num = blog2Num+? , Blog2Content=?, blog2Time=? where id=?',
            [$contentArrCount, $Blog2Content, date('Y-m-d H:i:s'), $blog1]
        );
        /** 向客户端返回OK标识 */
        return 'OK';
    }

    /***********************************************************************
     * 保存3级帖
     */
    public function saveBlog3(){
        /** 接收客户端的数据 */
        $blog1      = input('blog1');
        $blog2      = input('blog2');
        $toUser     = input('toUser');
        $content    = trim(input('content'));
        if ($toUser == cookie('user_id')) {
            $toUser = null;
        }
        /** 数据验证 */
        // 1.验证内容字数是否符合要求
        $contentCount = mb_strlen($content);
        if ($contentCount > 120 || $contentCount < 1) {
            return "限1~120字之间，当前内容{$contentCount}字";
        }
        // 2.验证绑定的2级帖是否存在
        $blog2Has = Blog2Model::get($blog2);
        if (empty($blog2Has)) {
            return '不存在的跟帖或已被删除';
        }
        /** 接收的数据再加工 */
        $content    = htmlspecialchars($content);
        $content    = Form::insertFace($content);
        $content    = Str::filter($content);
        /** 验证通过，将2级帖写入数据库 */
        Blog3Model::Create([
            'user'          => cookie('user_id'),
            'toUser'        => $toUser,
            'time'          => date('Y-m-d H:i:s'),
            'blog1'         => $blog1,
            'blog2'         => $blog2,
            'content'       => $content,
        ]);
        /** 将1级帖的回复量加1，且更新最新回复时间 */
        Db::execute('UPDATE op_blog1 SET blog3Num = blog3Num+1 where id=?',
            [$blog1]
        );
        /** 向客户端返回OK标识 */
        return 'OK';
    }

    /***********************************************************************
     * 保存图片
     */
    public function saveImg()
    {
        $path = Env::get('root_path') .'runtime/blog';
        // 每次保存图片，都要清理一次其他用户上传的过期图片文件
        $filesnames = scandir($path);
        array_splice($filesnames, 0, 2);
        foreach ($filesnames as $img) {
            $img = Env::get('root_path') .'runtime/blog/'. $img;
            $ctime = filectime($img); // 获取文件的创建日期
            $mspan = floor((strtotime(date('Y-m-d H:i:s'))-strtotime(date('Y-m-d H:i:s',$ctime))) / 60);
            if ($mspan >= 60) {
                // 删除超过60分钟的图片文件
                unlink($img);
            }
        }
        $imgName = $_FILES['file']['name'];		//图片的文件名
        $tmp = $_FILES['file']['tmp_name'];
        if (!is_dir($path)) {
            mkdir($path, 0700);
        }
        $path .= '/'. $imgName;
        if( move_uploaded_file($tmp, $path) ){
            return 'OK';
        } else {
            return '图片上传失败';
        }
    }
}