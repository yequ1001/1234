<?php

namespace app\common\facade;

use think\Facade;

class CookieExtend extends Facade
{
    protected static function getFacadeClass()
    {
        return '\CookieExtend';
    }
}