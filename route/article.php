<?php

Route::group('article', function () {
    Route::rule(':id$', 'article.read/index', 'GET')
        ->pattern(['id' => '\d+']);

    Route::rule(':type$', 'article.lists/index', 'GET')
        ->pattern(['type' => '\w+']);

    Route::rule('reply$', 'article.read/reply_save', 'POST')
        ->pattern(['id' => '\d+']);
});
