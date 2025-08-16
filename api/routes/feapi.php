<?php

// Special unauthenticated routes
// These will probably disappear at some point
Route::group(
    [
        'prefix' => '/fe',
    ],
    function () {
        Route::get('/{weapon}/{category}', 'Ranking\Get@index')->name('ranking.get');
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
        Route::post('/fencers/save', 'FE\Fencers\Save@index')->name('fe.fencers.save');
        Route::post('/fencers/presavecheck', 'FE\Fencers\Presave@index')->name('fe.fencers.presave');
        Route::post('/fencers/delete', 'FE\Fencers\Delete@index')->name('fe.fencers.delete');
        Route::post('/fencers/merge', 'FE\Fencers\Merge@index')->name('fe.fencers.merge');
        Route::post('/fencers/upload', 'FE\Fencers\Upload@index')->name('fe.fencers.upload');
        Route::get('/fencers/{fencerId}/photo', 'Fencers\Photo@index')->name('fe.fencers.photo');
        Route::post('/fencers', 'FE\Fencers\Index@index')->name('fe.fencers.index');
    }
);
