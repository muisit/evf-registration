<?php

// Unauthenticated routes
Route::group(
    [
        'prefix' => '/ranking'
    ],
    function () {
        Route::get(
            '/{weapon}/{category}',
            'Ranking\Get@index'
        )
        ->name('ranking.get');
    }
);

// Authenticated routes
Route::group(
    [
        'prefix' => '/ranking',
        'middleware' => 'auth:wp'
    ],
    function () {
        Route::get(
            '/create',
            'Ranking\Create@index'
        )->name('ranking.create');
    }
);
