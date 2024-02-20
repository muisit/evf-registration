<?php

Route::group(
    [
        'prefix' => '/registrations',
        'middleware' => 'auth'
    ],
    function () {
        Route::get(
            '/',
            'Registrations\Index@index'
        )->name('registrations.list');

        Route::post(
            '/',
            'Registrations\Save@index'
        )->name('registrations.save');

        Route::post(
            '/delete',
            'Registrations\Delete@index'
        )->name('registrations.delete');

        Route::post(
            '/pay',
            'Registrations\Pay@index'
        )->name('registrations.pay');

        Route::post(
            '/state',
            'Registrations\State@index'
        )->name('registrations.state');
    }
);
