<?php

Route::group(['middleware' => 'throttle:2,1'], function () {
    Route::post('/device/register', 'Device\Register@index')->name('device.register');
});

Route::group([
    'prefix' => 'device',
    'middleware' => 'auth:device'
], function () {
    Route::get('/status', 'Device\Status@index')->name('device.status');
    Route::post('/follow', 'Device\Follow@index')->name('device.follow');
    Route::get('/rankdetails/{weapon}/{uuid}', 'Device\RankDetails@index')->name('device.rankdetails');
    Route::get('/ranking/{weapon}/{category}', 'Device\Ranking@index')->name('device.ranking');
});
