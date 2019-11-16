<?php

/**
 * 网站首页路由
 * 如果设置了全局MISS路由，或开启了强制路由，则该路由规则必须存在
 */
Route::rule('/' , 'index/index');
Route::rule('/:id$' , 'links.index/reply')
    ->pattern(['id' => '\d{1,11}']);

/**
 * 全局MISS路由
 * 没有匹配到所有的路由规则后执行一条设定的路由：跳转到404页面
 * 只有关闭了调试模式才有效，否则会显示详细的错误信息帮助你发现问题
 */
if(!Env::get('APP_DEBUG')) {
    Route::miss(function (){
        return file_get_contents(Env::get('root_path') . '/public/static/404.html');
    });
}
