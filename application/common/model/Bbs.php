<?php

namespace app\common\model;

use think\Model;

class Bbs extends Model
{
    public function profile()
    {
        return $this->hasOne('BbsComment', 'id');
    }
}