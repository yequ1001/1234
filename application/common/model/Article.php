<?php

namespace app\common\model;

use think\Model;

class Article extends Model
{
    /**
     * 关联：文章评论
     */
    public function reply()
    {
        return $this->hasMany('ArticleReply', 'article');
    }
}