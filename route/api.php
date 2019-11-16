<?php
/**
 * Created by PhpStorm.
 * User: yequ1001
 * Date: 2019/7/11
 * Time: 20:20
 */

Route::group('api', function () {
    Route::rule('tencentCaptcha$', 'api.index/tencentCaptcha', 'POST');
});