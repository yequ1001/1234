<?php

namespace app\common\facade;

use think\Facade;

class Form extends Facade
{
    protected static function getFacadeClass()
    {
        return '\Form';
    }
}