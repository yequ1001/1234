<?php

namespace app\common\model;

use think\Model;

class BbsArticle extends Model
{
    public function profile()
    {
        return $this->hasOne('BbsArticle', 'id');
    }
}