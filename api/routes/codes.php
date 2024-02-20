<?php

// Unauthenticated route
Route::group(
    [
        'prefix' => '/codes'
    ],
    function () {
        Route::post(
            '/',
            'Codes\Validate@index'
        )->name('codes.validate');
    }
);


// Authenticated routes
Route::group(
    [
        'prefix' => '/codes',
        'middleware' => 'auth'
    ],
    function () {
        Route::get(
            '/users',
            'Codes\Users@index'
        )->name('codeusers.list');

        Route::post(
            '/users',
            'Codes\SaveUser@index'
        )->name('codeusers.save');
    }
);
