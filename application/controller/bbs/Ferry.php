<?php
namespace app\controller\bbs;

use think\DB;
use think\facade\Env;
use think\facade\Validate;
use app\common\controller\BaseController;
use app\common\facade\File;
use app\common\facade\Str;
use app\common\facade\User;
use app\common\model\Bbs as BbsModel;
use app\common\model\BbsArticle as BbsArticleModel;

class Ferry extends BaseController
{
    /**
     * 页面首次加载时的默认行为
     */
    public function index()
    {

        return view()->assign([
            'title'         => '论坛摆渡',
            'description' => '',
        ]);
    }

    public function read()
    {
        $keywords = input('keywords');

        // 关键词验证规则
        Validate::rule([
            'keywords|关键词'       => 'require|chsAlphaNum|min:1|max:9',
        ]);
        $wait = [
            'keywords'          => $keywords,
        ];

        // 是否有精确匹配到的数据
        $bool = 0;

        // 表单验证，将验证结果（即bool值）写入变量$bool
        $error = 0;
        $BbsDataFind = '';
        if(!Validate::check($wait)){
            $error = Validate::getError();
            $BbsDataSelect2 = [];
        } else {
            $BbsDataFind = BbsModel::where('name', $keywords)->find();
            $BbsDataSelect = BbsModel::where('name', 'LIKE', "%{$keywords}%")->limit(20)->order('article', 'desc')->select();
            $BbsDataSelect = $BbsDataSelect->toArray();
            $BbsDataSelect2 = [];
            if (empty($BbsDataFind)) {
                $BbsDataSelect2 = $BbsDataSelect;
            } else {
                $bool = 1;
                foreach ($BbsDataSelect as $BbsData) {
                    if ($BbsData['name'] != $keywords) {
                        $BbsDataSelect2[] = $BbsData;
                    }
                }
                // 在首尾插入精准查询结果
                array_unshift($BbsDataSelect2, $BbsDataFind);
            }
        }

        $this->assign([
            'title' => '',
            'description' => '',
			'keywords' => $keywords,
            'Bool' => $bool,
            'BbsData' => $BbsDataSelect2,
            'error' => $error,
        ]);
        return $this->fetch('index');
    }

}