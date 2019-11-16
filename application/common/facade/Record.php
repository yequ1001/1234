<?php

namespace app\common\facade;

use think\Facade;

class Record extends Facade
{
    protected static function getFacadeClass()
    {
        return '\Record';
    }
}