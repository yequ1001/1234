<?php

/**
 * 论坛路由
 */
Route::group('bbs', function () {
    Route::rule('$', 'bbs.index/index', 'GET');
    Route::rule(':type$', 'bbs.type/index', 'GET')-> pattern(['type' => '\d+']);
    Route::rule(':type/executive$', 'bbs.executive.index/index', 'GET');
    Route::rule(':type/executive/page$', 'bbs.executive.page/index', 'GET');
    Route::rule(':type/executive/page$', 'bbs.executive.page/update', 'POST');
    Route::rule(':type/executive/links$', 'bbs.executive.links/index', 'GET');
    Route::rule(':type/executive/links$', 'bbs.executive.links/save', 'POST');
    Route::rule(':type/executive/write$', 'bbs.executive.write/index', 'GET');
    Route::rule(':type/executive/write$', 'bbs.executive.write/update', 'POST');

    Route::rule(':type/write/[:bind]$', 'bbs.write/index', 'GET');
    Route::rule('write$', 'bbs.write/index', 'GET');
    Route::rule('write$', 'bbs.write/save', 'POST');

    Route::rule('ferry$', 'bbs.index/index', 'GET');
    Route::rule('ferry$', 'bbs.ferry/read', 'POST');
    Route::rule('read/:type/:id$', 'bbs.read/index', 'GET');
    Route::rule('read/:type/:id/operate$', 'bbs.operate/index', 'GET');
    Route::rule('read/:type/:id/operate$', 'bbs.operate/update', 'POST');
    Route::rule('update/:id$', 'bbs.update/index', 'GET');
    Route::rule('update$', 'bbs.update/save', 'POST');
    Route::rule('delete$', 'bbs.update/del', 'POST');
    Route::rule('comment$', 'bbs.comment/save', 'POST');
    Route::rule('saveImg$', 'bbs.SaveImg/save', 'POST');
    Route::rule('comment/delete$', 'bbs.comment/delete', 'DELETE');

    // 以下可删除
    Route::rule(':id$',
        'index/blog.read/index', 'GET')
        -> pattern(['id' => '\d+']);

    Route::rule('saveBlog2$',
        'index/blog.save/saveBlog2', 'POST')
        -> pattern(['id' => '\d+']);

    Route::rule('saveBlog3$',
        'index/blog.save/saveBlog3', 'POST')
        -> pattern(['id' => '\d+']);

    Route::rule('saveBlog1$',
        'index/blog.save/saveBlog1', 'POST');

    Route::rule('saveImg$',
        'index/blog.save/saveImg', 'POST');

    Route::rule('praise/:model/:modelId/:blog1$',
        'index/blog.praise/save', 'GET')
        -> pattern(['modelId'=>'\d+', 'blog1'=>'\d+']);

    Route::rule('delete/blog1/:id$',
        'index/blog.delete/delBlog1', 'GET')
        -> pattern(['id'=>'\d+']);

    Route::rule('delete/blog2/:id$',
        'index/blog.delete/delBlog2', 'GET')
        -> pattern(['id'=>'\d+']);

});
