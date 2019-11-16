<?php

Route::rule('i$', 'i.index/index', 'GET');
Route::rule('i$', 'i.index/info', 'POST');

Route::domain('i', function () {
    Route::rule('', 'i.index/index', 'GET');
    Route::rule('', 'i.index/info', 'POST');
})->bind('i');