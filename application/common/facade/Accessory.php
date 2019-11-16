<?php

namespace app\common\facade;

use think\Facade;

class Accessory extends Facade
{
    protected static function getFacadeClass()
    {
        return '\Accessory';
    }
}