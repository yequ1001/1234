<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 获取全局默认的静态文件URL
 */
function defaultStatic()
{
    /* 当前页面私有静态文件的路径 */
    $path = think\facade\Env::get('root_path') .'public';
    /* 获取文件 */
    $list = scandir($path);
    array_splice($list, 0, 2);
    /* 遍历文件并生成html字符串 */
    $html = '';
    foreach ($list as $file) {
        $filePath = $path .'/'. $file;
        $fileTime = filemtime($filePath);
        if (substr($file, -3) == '.js') {
            $html .= "<script src='/public/{$file}?v={$fileTime}'></script>\n    ";
        } else if (substr($file, -4) == '.css') {
            $html .= "<link rel='stylesheet' href='/public/{$file}?v={$fileTime}' />\n    ";
        }
    }
    return $html;
}

/**
 * 获取当前页面的私有静态文件URL
 */
function privateStatic()
{
    /* 获取模块、控制器、方法 */
    $module     = think\facade\Request::module();
    $controller = think\facade\Request::controller();
    $controller = str_replace('.', '/', $controller);
    $controller = preg_replace('#(?<!^|/)([A-Z]){1}#', '_$1', $controller);
    $controller = strtolower($controller);
    $action     = think\facade\Request::action();

    /* 当前页面私有静态文件的路径 */
    $path = think\facade\Env::get('root_path') ."public/{$controller}";
    $path = strtolower($path);

    /* 获取文件 */
    $list = scandir($path);
    array_splice($list, 0, 2);
    /* 遍历文件并生成html字符串 */
    $html = '';
    foreach ($list as $file) {
        $filePath = $path .'/'. $file;
        $fileTime = filemtime($filePath);
        if (substr($file, -3) == '.js') {
            $html .= "<script src='/public{$module}/{$controller}/{$file}?v={$fileTime}'></script>\n    ";
        } else if (substr($file, -4) == '.css') {
            $html .= "<link rel='stylesheet' href='/public{$module}/{$controller}/{$file}?v={$fileTime}' />\n    ";
        }
    }
    return $html;
}