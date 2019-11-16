<?php
namespace app\common\exception;

use Exception;
use think\exception\Handle;

class Http extends Handle
{
    public function render(Exception $e)
    {
        // 如果是Ajax请求时发生异常
        if ( request()->isAjax() ) {
            $errorStr = $e->getMessage() .'<br/>'.
                        $e->getFile() .' 行:'. $e->getLine();
            return response($errorStr);
        }

        // 其他错误交给系统处理
        return parent::render($e);
    }

}