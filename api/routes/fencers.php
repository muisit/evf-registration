<?php

Route::group(
    [
        'prefix' => '/fencers',
        'middleware' => 'auth'
    ],
    function () {
        Route::get(
            '/',
            'Fencers\Index@index'
        )->name('fencers.index');

        Route::get(
            '/{fencerId}/photo',
            'Fencers\Photo@index'
        )->name('fencers.photo');

        Route::get(
            '/{fencerId}/accreditations',
            'Fencers\Accreditations@index'
        )->name('fencers.accreditations');

        Route::post(
            '/{fencerId}/photo',
            'Fencers\PhotoSave@index'
        )->name('fencers.photosave');

        Route::post(
            '/{fencerId}/photostate',
            'Fencers\PhotoState@index'
        )->name('fencers.photostate');

        Route::get(
            '/autocomplete',
            'Fencers\Autocomplete@index'
        )->name('fencers.ac');

        Route::post(
            '/duplicate',
            'Fencers\Duplicate@index'
        )->name('fencers.duplicate');

        Route::post(
            '/',
            'Fencers\Save@index'
        )->name('fencers.save');
    }
);
