<?php
/**
 * Created by PhpStorm.
 * User: YeQu1001
 * Date: 2019/7/10
 * Time: 18:13
 */

namespace app\controller\admins;

use app\common\controller\BaseController;
use app\common\model\Question as QuestionModel;

class Question extends BaseController
{
    public function index()
    {
        /* 渲染视图 */
        return view()->assign([
            'title' => '验证问答管理',
            'description' => '',
        ]);
    }

    /**
     * 添加新问答
     */
    public function save()
    {
        $question = input('question');
        $answer0 = input('answer0');
        $answer1 = input('answer1');
        $answer2 = input('answer2');
        $answer3 = input('answer3');
        $true = input('true');
        $true = input('answer'. $true);

        $QuestionData = QuestionModel::create([
            'question' =>  $question,
            'answer' => "{$answer0}|{$answer1}|{$answer2}|{$answer3}",
            'true'=> $true,
        ]);
        return true;
    }
}
