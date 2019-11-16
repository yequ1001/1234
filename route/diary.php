<?php

Route::group('diary', function () {
    /**
     * 月记表
     */
    Route::rule('month/:year/:month$', 'diary.month/index', 'GET')
        ->pattern(['year' => '2\d{3}', 'month' => '\d{1,2}']);

    Route::rule('month$', 'diary.month/save', 'POST');

    Route::rule('month$', 'diary.month/index', 'GET');

    /**
     * 年度报告
     */
    Route::rule(':year$', 'diary.year/index', 'GET')
        ->pattern(['year' => '2\d{3}']);

    Route::rule(':year/download$', 'diary.year/download', 'GET')
        ->pattern(['year' => '2\d{3}']);

    /**
     * 使用帮助
     */
    Route::rule('help$', 'diary.help/index', 'GET');

    /**
     * 新手教程
     */
    Route::rule('novice$', 'diary.novice/index', 'GET');

    /**
     * 历史今天
     */
    Route::rule('history$', 'diary.history/index', 'GET');

    /**
     * 日记搜索
     */
    Route::rule('search$', 'diary.search/index', 'GET');

    Route::rule('search$', 'diary.search/search', 'POST');

    /**
     * 安全验证
     */
    Route::rule('secure$', 'diary.secure/index', 'GET');
    Route::rule('secure$', 'diary.secure/read', 'POST');

    /**
     * 安全退出
     */
    Route::rule('close$', 'diary.month/close', 'GET');
	
	/**
     * 备份
     */
	Route::rule('backup$', 'diary.backup/index', 'GET');
	Route::rule('backup/download/:year-:month$', 'diary.backup/download', 'GET');

    /**
     * PC视图模式
     */
    Route::rule('year_pc/[:year]$', 'diary.yearPc/index', 'GET')
        ->pattern(['year' => '2\d{3}']);

});
