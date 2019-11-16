<?php

namespace app\controller\blog;

use think\DB;
use app\common\facade\User;
use app\common\controller\BaseController;

class Praise extends BaseController
{
    public function save(){
        $user           = cookie('user_id');
        $blog1          = input('blog1');
        $model          = input('model');
        $modelTable     = 'op_'. $model;
        $modelId        = input('modelId');
        $modelPraise    = $modelTable .'_praise';
        /** 禁止游客操作 */
        if (User::type($user) == 'visitor') {
            return '您尚未登录';
        }
        /** 查询是否有点赞记录 */
        $result = Db::query("select * from {$modelPraise} where user = {$user} AND {$model} = ?", [$modelId]);
        /** 将点赞附表新增一行点赞数据 */
        if (empty($result)) {
            $data = [
                $model      => $modelId,
                'user'      => cookie('user_id'),
                'time'      => date('Y-m-d H:i:s')
            ];
            if ($model == 'blog2') {
                $data['blog1'] = $blog1;
            }
            Db::name($model .'_praise')->insert($data);
        } else {
            return '已经赞过了';
        }
        $result = Db::execute("update {$modelTable} set praise = praise +1 where id=?", [$modelId]);
        if ($modelTable == 'op_blog2') {
            Db::execute("update op_blog1 set praiseBlog2 = praiseBlog2 +1 where id=?", [$blog1]);
        }
        if ($result == 1) {
            return 'OK';
        }
    }
}