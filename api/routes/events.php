<?php

// Unauthenticated routes
Route::group(
    [
        'prefix' => '/events'
    ],
    function () {
        Route::get(
            '/{eventId}',
            'Events\Get@index'
        )
        ->name('events.get')
        ->where('eventId', '[1-9][0-9]*');
    }
);

// Authenticated routes
Route::group(
    [
        'prefix' => '/events',
        'middleware' => 'auth'
    ],
    function () {
        Route::get(
            '/',
            'Events\Index@index'
        )->name('events.list');

        Route::post(
            '/',
            'Events\Save@index'
        )->name('events.save');

        Route::post(
            '/config',
            'Events\SaveConfig@index'
        )->name('events.saveconfig');

        Route::post(
            '/sides',
            'Events\SaveSides@index'
        )->name('events.savesides');

        Route::get(
            '/roles',
            'Events\Roles@index'
        )->name('events.roles');

        Route::post(
            '/roles',
            'Events\SaveRoles@index'
        )->name('events.saveroles');

        Route::get(
            '/overview',
            'Events\Overview@index'
        )->name('events.overview');

        Route::get(
            '/statistics',
            'Events\Statistics@index'
        )->name('events.statistics');

        Route::get(
            '/generate',
            'Events\Generate@index'
        )->name('events.generate');

        Route::get(
            '/xml/{sideEventId}',
            'Events\Download@asXML'
        )->name('events.downloadxml');

        Route::get(
            '/csv/{sideEventId}',
            'Events\Download@asCSV'
        )->name('events.downloadcsv');
    }
);
