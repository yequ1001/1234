<?php
namespace app\common\facade;

use think\Facade;

class File extends Facade
{
    protected static function getFacadeClass()
    {
        return '\File';
    }
}