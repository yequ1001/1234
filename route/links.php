<?php

Route::group('links', function () {
    Route::rule('$', 'links.index/index', 'GET');
    Route::rule('/type/:type', 'links.index/getLinks', 'GET');

    /**
     * 添加友链
     */
    Route::rule('save$', 'links.save/index', 'GET');
    Route::rule('save$', 'links.save/save', 'POST');

    /**
     * 用户自定义
     */
    Route::rule('my$', 'links.index/mySave', 'POST');
    Route::rule('my/:id$', 'links.index/myClick', 'GET');
    Route::rule('my/:id$', 'links.index/myDelete', 'DELETE');
    Route::rule('my/:id$', 'links.index/myUpdate', 'POST');

    /**
     * 跳转
     */
    Route::rule('go/:id$', 'links.index/go', 'GET');

    /**
     * 一键收藏
     */
    Route::rule('collect$', 'links.index/collect', 'POST');

});
