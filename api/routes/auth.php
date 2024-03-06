<?php

Route::group(['middleware' => 'throttle:2,1'], function () {
    Route::post('/auth/login', 'Auth\Login@index')->name('auth.login');
});
Route::get('/auth/me', 'Auth\Me@index')->name('auth.me');
Route::get('/auth/logout', 'Auth\Logout@index')->name('auth.logout');
