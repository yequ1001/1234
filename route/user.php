<?php

Route::group('user', function () {
    Route::rule('$', 'user.index/index', 'GET');
    Route::rule('message/list$', 'user.index/read', 'GET');

    /**
     * 用户名片
     */
    Route::rule('card/:id$', 'user.card/index', 'GET')
        ->pattern(['id' => '\d{4,}']);

    /**
     * 注销登录
     */
    Route::rule('logout$', 'user.index/logout', 'GET');

    /**
     * 用户登录
     */
    Route::rule('login$', 'user.login/index', 'GET');
    Route::rule('login$', 'user.login/read', 'POST');

    /**
     * 用户密码
     */
    Route::rule('password$', 'user.password/index', 'GET');
    Route::rule('password$', 'user.password/save', 'POST');

    /**
     * 安全码
     */
    Route::rule('password2$', 'user.password2/index', 'GET');
    Route::rule('password2$', 'user.password2/save', 'POST');

    /**
     * 新用户注册
     */
    Route::rule('register$', 'user.register/index', 'GET');
    Route::rule('register$', 'user.register/create', 'POST');
    Route::rule('register/sms$', 'user.register/sms', 'POST');

    /**
     * 个人信息
     */
    Route::rule('info$', 'user.info/index', 'GET');

    /**
     * 用户头像
     */
    Route::rule('face$', 'user.face/index', 'GET');
    Route::rule('face$', 'user.face/save', 'POST');

    /**
     * 用户更名
     */
    Route::rule('rename$', 'user.rename/index', 'GET');
    Route::rule('rename$', 'user.rename/save', 'POST');

    /**
     * 搜索用户
     */
    Route::rule('search$', 'user.search/index', 'GET');
    Route::rule('search$', 'user.search/select', 'POST');

    /**
     * 用户签名
     */
    Route::rule('autograph$', 'user.autograph/index', 'GET');
    Route::rule('autograph$', 'user.autograph/save', 'POST');

    /** 站内通讯 */
    Route::rule('message/:from$', 'user.message/index', 'GET')
        -> pattern(['from' => '\d+']);

    Route::rule('message/read$', 'user.message/read', 'POST');

    Route::rule('message/inform$', 'user.message/inform', 'GET');

    Route::rule('message/save$', 'user.message/save', 'POST');

    Route::rule('message/neglect$', 'user.message/neglect', 'GET');

});
