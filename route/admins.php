<?php
/**
 * 网站后台
 */
Route::group('admins', function () {
    /**
     * 管理中心主页
     */
    Route::rule('/$', 'admins.index/index');

    /**
     * 文章管理
     */
    Route::rule('/article/save$', 'admins/index/save');
    Route::rule('/article/update/:id$', 'admins/article.update/index', 'GET');
    Route::rule('/article/update/:id$', 'admins/article.update/update', 'POST');

    Route::rule('/:id$', 'admins/article.update/read');

    /**
     * 验证问答管理
     */
    Route::rule('/question$', 'admins.question/index', 'GET');
    Route::rule('/question$', 'admins.question/save', 'POST');

    /**
     * 用户留言管理
     */
    Route::rule('/depot/message$', 'admins.depot.message/index', 'GET');
    Route::rule('/depot/message$', 'admins.depot.message/save', 'POST');
    Route::rule('/depot/message$', 'admins.depot.message/delete', 'DELETE');

    /**
     * 世界历史纪录修改器
     */
    Route::rule('/diary/history$', 'admins.diary.history/index', 'GET');
    Route::rule('/diary/history$', 'admins.diary.history/save', 'POST');
    Route::rule('/diary/history$', 'admins.diary.history/del', 'DELETE');

    /**
     * 外链管理
     */
    Route::rule('/links$', 'admins.links.index/index', 'GET');
    Route::rule('/links$', 'admins.links.index/update', 'POST');
    Route::rule('/links$', 'admins.links.index/del', 'DELETE');
    /**
     * 推荐标徽
     */
    Route::rule('/links/recommend$', 'admins.links.index/recommend', 'POST');

    /**
     * 今日访客统计
     */
    Route::rule('online/today$', 'admins.online.today/index', 'GET');

    /**
     * 系统通知管理
     */
    Route::rule('/announcement$', 'admins.announcement.index/index', 'GET');
    Route::rule('/announcement$', 'admins.announcement.index/save', 'POST');
});
