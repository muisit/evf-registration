<?php

Route::group(['middleware' => 'throttle:2,1'], function () {
    Route::post('/device/register', 'Device\Register@index')->name('device.register');
    Route::post('/device/error', 'Device\Error@index')->name('device.error');
});

Route::group([
    'prefix' => 'device',
    'middleware' => 'auth:device'
], function () {
    Route::get('/status', 'Device\Status@index')->name('device.status');
    Route::post('/register/status', 'Device\RegisterStatus@index')->name('device.registerstatus');
    Route::post('/token', 'Device\RegisterToken@index')->name('device.token');
    Route::post('/follow', 'Device\Follow@index')->name('device.follow');
    Route::post('/block', 'Device\Block@index')->name('device.block');
    Route::get('/rankdetails/{weapon}/{uuid}', 'Device\RankDetails@index')->name('device.rankdetails');
    Route::get('/ranking/{weapon}/{category}', 'Device\Ranking@index')->name('device.ranking');
    Route::get('/events', 'Device\Events@index')->name('device.events');
    Route::get('/results/{competitionId}', 'Device\Results@index')->name('device.results');
    Route::get('/calendar', 'Device\Calendar@index')->name('device.calendar');
    Route::get('/feed', 'Device\Feed@index')->name('device.feed');

    Route::get('/account', 'Device\Account\Get@index')->name('device.account');
    Route::post('/account', 'Device\Account\Save@index')->name('device.saveaccount');
    Route::post('/account/verify', 'Device\Account\Verify@index')->name('device.verify');
    Route::post('/account/check', 'Device\Account\Check@index')->name('device.check');
    Route::post('/account/link', 'Device\Account\Link@index')->name('device.link');
    Route::post('/account/preferences', 'Device\Account\Preferences@index')->name('device.preferences');
});
