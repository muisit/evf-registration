<?php

Route::group(["namespace" => "App\Http\Controllers"], function () {
    require('device.php');
});
Route::get('/', function () {
    return env('APP_VERSION');
});
