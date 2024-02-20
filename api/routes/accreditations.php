<?php

Route::group(
    [
        'prefix' => '/accreditations',
        'middleware' => 'auth'
    ],
    function () {
        Route::get(
            '/{fencerId}/badge/{templateId}',
            'Accreditations\Badge@index'
        )->name('accreditations.badge');

        Route::get(
            '/overview',
            'Accreditations\Overview@index'
        )->name('acrreditations.overview');
   
        Route::get(
            '/regenerate',
            'Accreditations\Regenerate@index'
        )->name('acrreditations.regenerate');

        Route::get(
            '/summary/{summaryId}',
            'Accreditations\Download@index'
        )->name('acrreditations.download');

        Route::post(
            '/summary',
            'Accreditations\Summary@index'
        )->name('acrreditations.summary');

        Route::post(
            '/document',
            'Accreditations\SaveDocument@index'
        )->name('acrreditations.document');
    }
);
