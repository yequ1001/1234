<?php

namespace app\common\facade;

use think\Facade;

class Str extends Facade
{
    protected static function getFacadeClass()
    {
        return '\Str';
    }
}