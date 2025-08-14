<?php

// Special unauthenticated routes
// These will probably disappear at some point
Route::group(
    [
        'prefix' => '/fe',
    ],
    function () {
        Route::get(
            '/{weapon}/{category}',
            'Ranking\Get@index'
        )
        ->name('ranking.get');
    }
);

Route::group(
    [
        'prefix' => '/fe',
        'middleware' => 'auth:wp'
    ],
    function () {
        Route::post('/ranking/create', 'FE\CreateRanking@index')->name('fe.ranking.create');

        Route::post('/fencers/view', 'FE\Fencers\View@index')->name('fe.fencers.view');
        Route::post('/fencers', 'FE\Fencers\Index@index')->name('fe.fencers.index');
    }
);
