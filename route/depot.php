<?php
/**
 * 公共站务
 */
Route::group('depot', function () {
    /**
     * 网站历程
     */
    Route::rule('experience$', 'depot.experience/index', 'GET');

    /**
     * 合作
     */
    Route::rule('cooperation$', 'depot.cooperation/index', 'GET');

    /**
     * 客服中心
     */
    Route::rule('service$', 'depot.service/index', 'GET');

    /**
     * 留言箱
     */
    Route::rule('message$', 'depot.message/index', 'GET');
    Route::rule('message$', 'depot.message/save', 'POST');

    /**
     * 开发者笔记
     */
    Route::rule('developer$', 'depot.developer.index/index', 'GET');
    Route::rule('developer/write$', 'depot.developer.write/index', 'GET');
    Route::rule('developer/write$', 'depot.developer.write/save', 'POST');
    Route::rule('developer/write$', 'depot.developer.write/del', 'DELETE');
    Route::rule('developer/:type/:id$', 'depot.developer.read/index', 'GET')
        -> pattern(['type' => '\w{1,10}', 'id' => '\d+']);

});