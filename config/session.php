<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------
$sessionPath = Env::get('root_path') .'runtime/session';
if( !is_dir($sessionPath) ) {
    mkdir($sessionPath, 0777);
}

return [
    'id'             => '',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // SESSION 前缀
    'prefix'         => 'op',
    // SESSION过期时间，单位 秒，20分钟
    'expire'         => 1200,
    // 驱动方式 支持redis memcache memcached
    'type'           => '',
    // 是否自动开启 SESSION
    'auto_start'     => true,
    // 路径
//    'path'           => $sessionPath,
    //
	'cache_expire'   => 1200,
    // 使用httponly
    'httponly'       => true,
];
